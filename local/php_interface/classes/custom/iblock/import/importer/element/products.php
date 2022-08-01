<?php
namespace Custom\Iblock\Import\Importer\Element;

use Bitrix\Catalog\GroupTable;
use Bitrix\Catalog\Model\Price;
use Bitrix\Catalog\Model\Product;
use Bitrix\Iblock\ORM\CommonElementTable;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Custom\Iblock\Import\Importable\IImportable;
use Custom\Iblock\Import\Package;

class Products extends BaseImporter
{
    public const CATALOG_FIELDS_MAP = ['QUANTITY' => 'QUANTITY', 'PRICE' => 'PRICE'];
    public const DEFAULT_CURRENCY = 'RUB';
    
    public function __construct()
    {
        parent::__construct();
        Loader::includeModule('catalog');
    }

    public static function getIblockId(): int
    {
        return \Custom\Iblock\Products::getCachedInstance()->getId();
    }
    
    public function getSearchElementField(): array
    {
        return ['TYPE' => 'FIELD', 'NAME' => 'XML_ID'];
    }

    /** @param CommonElementTable|string $iblockElementClass */
    protected function addElements(Package $package, $iblockElementClass)
    {
        $iblockElement = $iblockElementClass::getObjectClass();
        $catalogFieldsByProduct = [];
        
        /** @var EntityObject $iblockElement */
        while ($element = $package->retrieve()) {
            $fields = array_merge($element->getFields(), $element->getProperties());
            $fields['CODE'] = \CUtil::translit($fields['NAME'], LANGUAGE_ID);
            if (empty($fields['XML_ID'])) {
                $fields['XML_ID'] = $fields['CODE'];
            }

            try {
                $iblockElement = new $iblockElement();
                $productId = $this->saveProduct($iblockElement, $fields, $element);
                if ($productId > 0) {
                    $catalogFields = $this->collectCatalogFields($fields);
                    if (count($catalogFields) > 0) {
                        $catalogFieldsByProduct[$productId] = $catalogFields;
                    }
                }
            }
            catch (\Throwable $exception) {
                $this->errorCollection->setError(new Error(
                    'Unable to create the element', 0,
                    [
                        'error' => $exception->getMessage(),
                        'fields' => $element->getFields(),
                        'properties' => $element->getProperties(),
                    ]
                ));
            }
        }

        $this->updateProductCatalogFields($catalogFieldsByProduct);
    }

    /** @param CommonElementTable|string $iblockElementClass */
    protected function updateElements(Package $package, $iblockElementClass)
    {
        $catalogFieldsByProduct = [];
        while ($element = $package->retrieve()) {
            $fields = array_merge($element->getFields(), $element->getProperties());

            $elementId = (int)$element->getFields()['ID'];
            unset($fields['ID']);

            try {
                $iblockElement = $iblockElementClass::getById($elementId)->fetchObject();
                $productId = $this->saveProduct($iblockElement, $fields, $element);
                if ($productId > 0) {
                    $catalogFields = $this->collectCatalogFields($fields);
                    if (count($catalogFields) > 0) {
                        $catalogFieldsByProduct[$productId] = $catalogFields;
                    }
                }
            }
            catch (\Throwable $exception) {
                $this->errorCollection->setError(new Error(
                    'An error occurred during the import process', 0,
                    [
                        'error' => $exception->getMessage(),
                        'fields' => $element->getFields(),
                        'properties' => $element->getProperties(),
                    ]
                ));
            }
        }
        
        $this->updateProductCatalogFields($catalogFieldsByProduct);
    }
    
    protected function saveProduct($iblockElement, array $fields, IImportable $element): int
    {
        foreach ($fields as $name => $value) {
            $iblockElement->set($name, $value);
        }
        $saveResult = $iblockElement->save();
        $this->handleSaveResult($saveResult, $element);
        $productId = 0;
        if ($saveResult->isSuccess()) {
            $productId = $saveResult->getId() ?? $element->getFields()['ID'];
        }
        
        return $productId;
    }
    
    protected function collectCatalogFields($fields): array
    {
        $catalogFields = [];
        foreach ($fields as $name => $value) {
            if (isset(self::CATALOG_FIELDS_MAP[$name])) {
                $catalogFields[self::CATALOG_FIELDS_MAP[$name]] = $value;
            }
        }
        
        return $catalogFields;
    }
    
    protected function updateProductCatalogFields(array $catalogFieldsByProduct)
    {
        $productIds = array_keys($catalogFieldsByProduct);
        $existingPrices = $this->getExistingPrices($productIds);
        $existingProducts = $this->getExistingProducts($productIds);
        foreach ($catalogFieldsByProduct as $productId => $catalogFields) {
            if (!empty($catalogFields)) {
                if (isset($existingProducts[$productId])) {
                    $productSaveResult = Product::update($productId, $catalogFields);
                }
                else {
                    $catalogFields['ID'] = $productId;
                    $productSaveResult = Product::add($catalogFields);
                }
                if (!$productSaveResult->isSuccess()) {
                    $this->errorCollection->setError(new Error(
                        "Failed to update product data for product with ID $productId", 0,
                        ['error' => implode(',', $productSaveResult->getErrorMessages())]
                    ));
                }
            }
            
            $basePriceId = $this->getBasePriceId();
            
            if (isset($catalogFields['PRICE'])) {
                $priceFields = [
                    'PRODUCT_ID' => $productId,
                    'PRICE' => $catalogFields['PRICE'],
                    'CATALOG_GROUP_ID' => $basePriceId,
                    'CURRENCY' => self::DEFAULT_CURRENCY
                ];
                if (isset($existingPrices[$productId])) {
                    $priceSaveResult = Price::update($existingPrices[$productId], $priceFields);
                }
                else {
                    $priceSaveResult = Price::add($priceFields);
                }
                if (!$priceSaveResult->isSuccess()) {
                    $this->errorCollection->setError(new Error(
                        "Failed to update product data for product with ID $productId", 0,
                        ['error' => implode(',', $priceSaveResult->getErrorMessages())]
                    ));
                }
            }
        }
    }
    
    protected function getExistingPrices($productIds): array
    {
        $prices = Price::getList([
            'filter' => ['=PRODUCT_ID' => $productIds, 'CATALOG_GROUP_ID' => $this->getBasePriceId()],
            'select' => ['PRODUCT_ID', 'ID']
        ]);
        $existingPrices = [];
        while ($price = $prices->fetch()) {
            $existingPrices[$price['PRODUCT_ID']] = $price['ID'];
        }
        
        return $existingPrices;
    }

    protected function getExistingProducts($productIds): array
    {
        $products = Product::getList([
            'filter' => ['=ID' => $productIds],
            'select' => ['ID']
        ]);
        $existingProducts = [];
        while ($product = $products->fetch()) {
            $existingProducts[$product['ID']] = $product['ID'];
        }

        return $existingProducts;
    }
    
    public function getBasePriceId(): ?int
    {
        static $priceId;
        if (!isset($priceId)) {
            $priceId = (int)GroupTable::getList(['filter' => ['=BASE' => 'Y'], 'select' => ['ID']])->fetch()['ID'];
        }

        return $priceId;
    }
}
