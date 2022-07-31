<?php

namespace Custom\Iblock\Import\Importable;


use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentNullException;
use Custom\Iblock\Import\Exception\FieldValueNotFoundException;
use Custom\Iblock\Property\Singular;


class Element implements IImportable
{
    protected Singular $iblockProperty;
    protected array $mappedFields;
    protected array $fields;
    protected array $properties;
    protected array $linkProperties;

    /**
     * @throws ArgumentNullException | \Bitrix\Main\SystemException | FieldValueNotFoundException
     */
    public function __construct(Singular $iblockProperty, array $mappedFields)
    {
        $this->iblockProperty = $iblockProperty;
        $this->mappedFields = $mappedFields;
        $this->validateFields();
        $this->collectFields();
        $this->collectProperties();
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getLinkProperties(): array
    {
        return $this->linkProperties;
    }

    public function addProperty(string $code, $value)
    {
        $this->properties[$code] = $value;
    }

    public function addField(string $name, $value)
    {
        $this->fields[$name] = $value;
    }


    /*** @throws ArgumentNullException */
    protected function validateFields()
    {
        if (empty($this->mappedFields)) {
            throw new ArgumentNullException('mappedFields');
        }
    }

    protected function collectFields()
    {
        $this->fields = [];
        $iblockElementFieldNames = array_keys(ElementTable::getMap());
        foreach ($iblockElementFieldNames as $fieldName) {
            if (isset($this->mappedFields[$fieldName])) {
                $this->fields[$fieldName] = $this->mappedFields[$fieldName];
            }
        }
    }

    /*** @throws \Bitrix\Main\SystemException | FieldValueNotFoundException */
    protected function collectProperties()
    {
        $this->properties = [];
        $this->linkProperties = [];
        $iblockProperties = $this->iblockProperty->getIblockProperties();
        foreach ($iblockProperties as $property) {
            if (!empty($this->mappedFields[$property['CODE']])) {
                $this->properties[$property['CODE']] = $this->mappedFields[$property['CODE']];
            }
            elseif ($property['IS_REQUIRED'] === 'Y') {
                throw new FieldValueNotFoundException($property['CODE']);
            }
        }
    }

}