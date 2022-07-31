<?php

namespace Custom\Iblock\Import\Exception;


class ImporterNotFoundException extends ImportException
{
    public function __construct(int $iblockId)
    {
        $message = "Import class for iblock with ID $iblockId not found";
        parent::__construct($message);
    }
}