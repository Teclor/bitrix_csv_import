<?php

namespace Custom\Iblock;


use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\Model\Section;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Custom\Cache\ICacheable;
use Custom\Patterns\Singleton;

abstract class Base extends Singleton implements ICacheable
{
    protected const DEFAULT_CACHE_TIME = 86400;

    protected int $iblockId;

    protected bool $isCachedMode = false;

    protected $elementClass;
    protected $sectionClass;

    abstract public static function getCode(): string;

    /**
     * @throws LoaderException|\Bitrix\Main\ArgumentException|\Bitrix\Main\ObjectPropertyException|\Bitrix\Main\SystemException
     */
    public function getId(): int
    {
        if (!isset(static::$iblockId)) {
            static::loadModules();
            $queryParams = $this->getDefaultQueryParams();
            $queryParams['filter'] = ['=CODE' => static::getCode()];
            $queryParams['select'] = ['ID'];
            $this->iblockId = (int)IblockTable::getList($queryParams)->fetch()['ID'];
        }

        return $this->iblockId;
    }


    public function getElementClass(): string
    {
        return $this->elementClass;
    }

    public function getSectionClass(): string
    {
        return $this->sectionClass;
    }

    public function enableCachedMode()
    {
        $this->isCachedMode = true;
    }

    public function disableCachedMode()
    {
        $this->isCachedMode = false;
    }

    public function isCachedMode(): bool
    {
        return $this->isCachedMode;
    }

    public function getCacheTime(): int
    {
        return self::DEFAULT_CACHE_TIME;
    }

    /** @throws LoaderException */
    protected function __construct()
    {
        parent::__construct();
        static::loadModules();

        $this->elementClass = Iblock::wakeUp($this->getId())->getEntityDataClass();
        $this->sectionClass = Section::compileEntityByIblock($this->getId());
    }

    protected function getDefaultQueryParams()
    {
        return $this->isCachedMode() ? ['cache' => ['ttl' => $this->getCacheTime(), 'cache_joins' => true]] : [];
    }

    /** @throws LoaderException */
    protected static function loadModules()
    {
        Loader::includeModule('iblock');
    }
}