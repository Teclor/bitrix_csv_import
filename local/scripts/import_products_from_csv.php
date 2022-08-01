<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

\Bitrix\Main\Loader::includeModule('iblock');

$filePath = \Bitrix\Main\Application::getDocumentRoot() . '/upload/import/data.csv';
if (file_exists($filePath)) {
    $file = new SplFileObject($filePath);
    $importResult = (new \Custom\Tools\Import\ProductsFromCsv($file))->run();
    echo '<pre>';
    print_r($importResult); //TODO: DELETE IV_DUMP
    echo '</pre>';
}
else {
    echo "File with path $filePath not found";
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
