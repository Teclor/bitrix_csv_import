<?php

namespace Custom\Iblock\Property;


use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Loader;

class Singular
{
    private int $iblockId;
    private array $properties;
    
    public function __construct(int $iblockId)
    {
        if ($iblockId <= 0) {
            throw new ArgumentOutOfRangeException('iblockId', 1);
        }
        $this->iblockId = $iblockId;
        self::loadModules();
    }
    
    protected static function loadModules()
    {
        Loader::includeModule('iblock');
    }

    public function getIblockId(): int
    {
        return $this->iblockId;
    }
    
    public function getIblockProperties(): array
    {
        if (!isset($this->properties)) {
            $properties = [];
            $propertyRows = PropertyTable::getList([
                'filter' => ['=IBLOCK_ID' => $this->iblockId, '=MULTIPLE' => 'N', '=ACTIVE' => 'Y'],
                'select' => ['IS_REQUIRED', 'NAME', 'CODE', 'ID', 'PROPERTY_TYPE']
            ]);
            
            while ($property = $propertyRows->fetch()) {
                $properties[$property['CODE']] = $property;
            }
            $this->properties = $properties;
        }
        
        return $this->properties;
    }
    
    public function getByCode(string $code): array
    {
        return self::getIblockProperties()[$code] ?? [];
    }
}