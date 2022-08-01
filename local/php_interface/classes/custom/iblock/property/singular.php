<?php

namespace Custom\Iblock\Property;


use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

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
            $plainProperties = [];
            $propertyRows = PropertyTable::getList([
                'filter' => ['=IBLOCK_ID' => $this->iblockId, '=MULTIPLE' => 'N', '=ACTIVE' => 'Y'],
                'select' => ['IS_REQUIRED', 'NAME', 'CODE', 'ID', 'PROPERTY_TYPE']
            ]);
            
            $listProperties = [];
            while ($property = $propertyRows->fetch()) {
                if (self::isEnumProperty($property)) {
                    $listProperties[$property['ID']] = $property;
                }
                else {
                    $plainProperties[$property['CODE']] = $property;
                }
            }
            if (count($listProperties) > 0) {
                $listPropertyValues = PropertyEnumerationTable::getList([
                    'filter' => ['=PROPERTY_ID' => array_column($listProperties, 'ID')],
                    'select' => ['ID', 'VALUE', 'PROPERTY_ID']
                ]);
                while ($enumProperty = $listPropertyValues->fetch()) {
                    $listProperties[$enumProperty['PROPERTY_ID']]['VALUE_ID_LIST'][$enumProperty['VALUE']] = $enumProperty['ID'];
                }
            }
            
            $this->properties = array_merge($plainProperties, $listProperties);
        }
        
        return $this->properties;
    }
    
    public function getByCode(string $code): array
    {
        return self::getIblockProperties()[$code] ?? [];
    }
    
    public static function isEnumProperty($property): bool
    {
        return $property['PROPERTY_TYPE'] === PropertyTable::TYPE_LIST;
    }

    /*** @throws \Exception|SystemException */
    public function addEnumPropertyValue($propertyId, $value)
    {
        $enumValueId = \CIblockPropertyEnum::Add(['PROPERTY_ID' => $propertyId, 'VALUE' => $value]);
        if ($enumValueId === false) {
            throw new SystemException("Unable to add enum property $propertyId value $value");
        }
        
        return $enumValueId;
    }
}