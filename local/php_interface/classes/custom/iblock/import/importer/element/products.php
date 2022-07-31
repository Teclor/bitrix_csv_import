<?php
namespace Custom\Iblock\Import\Importer\Element;

class Products extends BaseImporter
{
    public static function getIblockId(): int
    {
        return \Custom\Iblock\Products::getCachedInstance()->getId();
    }
    
    public function getSearchElementField(): array
    {
        return ['TYPE' => 'FIELD', 'NAME' => 'XML_ID'];
    }
}
