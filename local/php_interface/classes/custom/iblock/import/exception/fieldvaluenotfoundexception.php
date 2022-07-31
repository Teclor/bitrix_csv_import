<?php

namespace Custom\Iblock\Import\Exception;


class FieldValueNotFoundException extends ImportException
{
    protected string $fieldName;

    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
        $message = "Value for field $this->fieldName not found";
        parent::__construct($message);
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }
}