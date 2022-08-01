<?php

namespace Custom\Agents;


class Import
{
    public static function runProductsFromCsvImport(): string
    {
        $filePath = \Bitrix\Main\Application::getDocumentRoot() . '/upload/import/data.csv';
        if (file_exists($filePath)) {
            $file = new \SplFileObject($filePath);
            $importResult = (new \Custom\Tools\Import\ProductsFromCsv($file))->run();
        }
        return __METHOD__ . '();';
    }
}