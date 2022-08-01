<?php

namespace Custom\Tools\Converter;


class Converter
{
    private array $fieldTypeMap;

    public function __construct(array $fieldTypeMap)
    {
        $this->fieldTypeMap = $fieldTypeMap;
    }

    public function getConvertedBatch(array $batch): array
    {
        $convertedBatch = [];
        foreach ($batch as $values) {
            $convertedBatch[] = $this->getConvertedValues($values);
        }

        return $convertedBatch;
    }
    
    public function getConvertedValues(array $values): array
    {
        $convertedValues = [];
        foreach ($values as $key => $value) {
            if (isset($this->fieldTypeMap[$key])) {
                $convertedValues[$key] = self::getConvertedValue($value, $this->fieldTypeMap[$key]);
            }
            else {
                $convertedValues[$key] = $value;
            }
        }

        return $convertedValues;
    }


    public static function getConvertedValue($value, $type = FieldType::TYPE_STRING)
    {
        $convertedValue = $value;
        switch ($type) {
            case FieldType::TYPE_INT:
                $convertedValue = (int)$value;
                break;
            case FieldType::TYPE_STRING:
                $convertedValue = (string)$value;
                break;
            default:
                break;
        }

        return $convertedValue;
    }
}