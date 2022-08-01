<?php

namespace Custom\Iblock\Import\Importer;


use Custom\Iblock\Import\Importer\Element\IImporter;
use Custom\Iblock\Import\Importer\Element\Products;
use Custom\Patterns\Singleton;
use Custom\Iblock\Import\Exception\ImporterNotFoundException;

class ImporterRegistry extends Singleton
{
    private array $importerClassByIblockId;
    private array $importerInstanceByIblockId = [];

    protected function __construct()
    {
        parent::__construct();
        $this->importerClassByIblockId = [
            Products::getIblockId() => Products::class,
        ];
    }

    /*** @throws ImporterNotFoundException */
    public function getImporter(int $iblockId): IImporter
    {
        if (!isset($this->importerInstanceByIblockId[$iblockId])) {
            if (isset($this->importerClassByIblockId[$iblockId])) {
                $this->importerInstanceByIblockId[$iblockId] = new $this->importerClassByIblockId[$iblockId]();
            }
            else {
                throw new ImporterNotFoundException($iblockId);
            }
        }
        return $this->importerInstanceByIblockId[$iblockId];
    }
}