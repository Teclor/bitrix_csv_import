<?php
namespace Custom\Iblock\Import\Importable;


interface IImportable
{
    public function getFields(): array;
    
    public function getProperties(): array;
    
    public function addProperty(string $code, $value);
    
    public function addField(string $name, $value);
}