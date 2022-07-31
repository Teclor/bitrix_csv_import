<?php

namespace Custom\Tools\Import;

use Custom\Iblock\Import\Exception\FieldValueNotFoundException;
use Custom\Iblock\Import\Importable\Element;
use Custom\Iblock\Import\Importer\ImporterRegistry;
use Custom\Iblock\Import\Package;
use Custom\Iblock\Products;
use Custom\Iblock\Property\Singular;
use Custom\Parser\DSV;
use Custom\Tools\Converter\Converter;
use Custom\Tools\Converter\FieldType;


class ProductsFromCsv
{
    public const FIELD_MAP = [
        'XML_ID' => FieldType::TYPE_STRING,
        'NAME' => FieldType::TYPE_STRING,
        'VENDOR' => FieldType::TYPE_STRING,
        'MATERIAL' => FieldType::TYPE_STRING,
        'QUANTITY' => FieldType::TYPE_INT,
        'PRICE' => FieldType::TYPE_INT,
    ];

    private \SplFileObject $csvFile;

    public function __construct(\SplFileObject $csvFile)
    {
        $this->csvFile = $csvFile;
    }

    public function run(): string
    {
        $result = '';
        $errors = [];
        try {
            $dsv = new DSV($this->csvFile, true, ';');
            $dsv->setColumns(array_keys(self::FIELD_MAP));
            $parsed = $dsv->getParsed();

            $converter = new Converter(self::FIELD_MAP);
            $converted = $converter->getConvertedBatch($parsed);

            $importPackage = new Package();
            $propertyHelper = new Singular(Products::getCachedInstance()->getId());
            foreach ($converted as $fields) {
                try {
                    $element = new Element($propertyHelper, $fields);
                    $importPackage->append($element);
                }
                catch (FieldValueNotFoundException $exception) {
                    $errors[] = "Error during import - value not found for field {$exception->getFieldName()}";
                }
            }

            $importer = ImporterRegistry::getInstance()->getImporter(\Custom\Iblock\Import\Importer\Element\Products::getIblockId());
            $importer->import($importPackage);
            $result = 'Import finished.' . PHP_EOL .
                'Added elements: ' . count($importer->getAddedIds()) . PHP_EOL .
                'Updated elements: ' . count($importer->getUpdatedIds());
        }
        catch (\Throwable $exception) {
            $errors[] = "Import finished with an error: {$exception->getMessage()} {$exception->getFile()}:{$exception->getLine()}";
        }

        if (count($errors) > 0) {
            $result .= implode(PHP_EOL, $errors);
        }

        return $result;
    }
}