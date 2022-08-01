<?php

namespace Custom\Iblock\Import\Exception;


use Bitrix\Main\SystemException;

class ImportException extends SystemException
{
    public function __construct($message = "")
    {
        parent::__construct($message);
    }
}