<?php

namespace Custom\Iblock\Import;

use Bitrix\Main\Errorable;
use Custom\Iblock\Import\Importable\IImportable;

interface IPackage extends \IteratorAggregate, Errorable
{
    public function append(IImportable $element);
    
    public function retrieve(): ?IImportable;
}