<?php

namespace Custom\Iblock\Import\Importer\Element;


use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\ORM\CommonElementTable;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Custom\Error\Logger;
use Custom\Iblock\Import\Importable\IImportable;
use Custom\Iblock\Import\Package;

abstract class BaseImporter implements IImporter
{
    use Logger;

    protected ErrorCollection $errorCollection;
    protected array $addedIds = [];
    protected array $updatedIds = [];

    public function __construct()
    {
        $this->errorCollection = new ErrorCollection();
    }

    abstract public static function getIblockId(): int;

    abstract public function getSearchElementField(): array;

    public function import(Package $package)
    {
        $this->errorCollection->add($package->getErrors());
        [$packageToAdd, $packageToUpdate] = $this->getSplitPackages($package);

        $iblockElementClass = Iblock::wakeUp(static::getIblockId())->getEntityDataClass();
        $this->addElements($packageToAdd, $iblockElementClass);
        $this->updateElements($packageToUpdate, $iblockElementClass);

        $this->logErrors();
    }

    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code): ?\Bitrix\Main\Error
    {
        return $this->errorCollection->getErrorByCode($code);
    }

    public function getAddedIds(): array
    {
        return $this->addedIds;
    }

    public function getUpdatedIds(): array
    {
        return $this->updatedIds;
    }

    /** @param CommonElementTable|string $iblockElementClass */
    protected function addElements(Package $package, $iblockElementClass)
    {
        $iblockElement = $iblockElementClass::getObjectClass();
        /** @var EntityObject $iblockElement */
        while ($element = $package->retrieve()) {
            $fields = array_merge($element->getFields(), $element->getProperties());
            $fields['CODE'] = \CUtil::translit($fields['NAME'], LANGUAGE_ID);
            if (empty($fields['XML_ID'])) {
                $fields['XML_ID'] = $fields['CODE'];
            }

            try {
                $iblockElement = new $iblockElement();
                foreach ($fields as $name => $value) {
                    $iblockElement->set($name, $value);
                }
                $this->handleSaveResult($iblockElement->save(), $element);
            }
            catch (\Throwable $exception) {
                $this->errorCollection->setError(new Error(
                    'Unable to create the element',
                    [
                        'error' => $exception->getMessage(),
                        'fields' => $element->getFields(),
                        'properties' => $element->getProperties(),
                    ]
                ));
            }
        }
    }

    /***
     * @param CommonElementTable|string $iblockElementClass
     */
    protected function updateElements(Package $package, $iblockElementClass)
    {
        while ($element = $package->retrieve()) {
            $fields = array_merge($element->getFields(), $element->getProperties());

            $elementId = (int)$element->getFields()['ID'];
            unset($fields['ID']);

            try {
                $iblockElement = $iblockElementClass::getById($elementId)->fetchObject();
                foreach ($fields as $name => $value) {
                    $iblockElement->set($name, $value);
                }
                $this->handleSaveResult($iblockElement->save(), $element);
            }
            catch (\Throwable $exception) {
                $this->errorCollection->setError(new Error(
                    'An error occurred during the import process',
                    [
                        'error' => $exception->getMessage(),
                        'fields' => $element->getFields(),
                        'properties' => $element->getProperties(),
                    ]
                ));
            }
        }
    }

    protected function handleSaveResult(Result $saveResult, IImportable $element)
    {
        if ($saveResult->isSuccess()) {
            if (is_a($saveResult, AddResult::class)) {
                $this->addedIds[] = $saveResult->getId();
            }
            elseif (is_a($saveResult, UpdateResult::class)) {
                $this->updatedIds[] = $saveResult->getId();
            }
        }
        else {
            foreach ($saveResult->getErrorMessages() as $message) {
                $this->errorCollection->setError(new Error(
                    'Unable to save element during import process', 0,
                    [
                        'error' => $message,
                        'fields' => $element->getFields(),
                        'properties' => $element->getProperties(),
                    ]
                ));
            }
        }
    }

    protected function getSplitPackages(Package $package): array
    {
        $packageToAdd = $package;;
        $packageToUpdate = new Package();

        $searchField = $this->getSearchElementField();
        $searchFieldValues = [];
        $getSearchFieldValueMethod = $searchField['TYPE'] === 'PROPERTY' ? 'getProperties' : 'getFields';
        /** @var IImportable $element */
        foreach ($package as $element) {
            $fields = $element->{$getSearchFieldValueMethod}();
            if (!empty($fields[$searchField['NAME']])) {
                $searchFieldValues[] = $fields[$searchField['NAME']];
            }
        }

        if (count($searchFieldValues) > 0) {
            $entityClass = Iblock::wakeUp(static::getIblockId())->getEntityDataClass();
            $elementRows = $entityClass::getList([
                'filter' => [
                    "={$searchField['NAME']}" => $searchFieldValues,
                ],
                'select' => ['ID', $searchField['NAME']],
            ]);

            $elementIdBySearchField = [];
            while ($entityElement = $elementRows->fetch()) {
                $elementIdBySearchField[$entityElement[$searchField['NAME']]] = $entityElement['ID'];
            }

            if (count($elementIdBySearchField) > 0) {
                $packageToAdd = new Package();
                while ($element = $package->retrieve()) {
                    $fields = $element->{$getSearchFieldValueMethod}();
                    if (isset($elementIdBySearchField[$fields[$searchField['NAME']]])) {
                        $element->addField('ID', $elementIdBySearchField[$fields[$searchField['NAME']]]);
                        $packageToUpdate->append($element);
                    }
                    else {
                        $packageToAdd->append($element);
                    }
                }
            }
        }

        return [$packageToAdd, $packageToUpdate];
    }

    protected function logErrors()
    {
        /** @var Error $error */
        foreach ($this->errorCollection->getValues() as $error) {
            $fileName = '/log/import/' . date("Ymd") . '_import_errors.log';
            static::logToFile($fileName, ['message' => $error->getMessage(), 'data' => $error->getCustomData()]);
        }
    }
}