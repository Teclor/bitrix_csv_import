<?php
namespace Custom\Iblock\Import\Importer\Element;


use Bitrix\Main\Errorable;
use Custom\Iblock\Import\Package;

interface IImporter extends Errorable
{
    public static function getIblockId(): int;
    
    public function import(Package $package);
    
    public function getSearchElementField(): array;

    public function getAddedIds(): array;

    public function getUpdatedIds(): array;
}