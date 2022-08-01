<?php
namespace Custom\Patterns;

use Bitrix\Main\SystemException;
use Custom\Cache\ICacheable;

class Singleton
{
    private static $instances = [];

    protected function __construct() { }

    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    /** @return static */
    public static function getInstance()
    {
        $subclass = static::class;
        if (!isset(self::$instances[$subclass])) {
            self::$instances[$subclass] = new static();
        }

        return self::$instances[$subclass];
    }
    
    public static function getCachedInstance()
    {
        $subclass = static::class;
        if (!isset(self::$instances[$subclass])) {
            self::$instances[$subclass] = new static();
        }
        if (self::$instances[$subclass] instanceof ICacheable) {
            self::$instances[$subclass]->enableCachedMode();
        }
        else {
            throw new SystemException("$subclass is not instance of " . ICacheable::class);
        }

        return self::$instances[$subclass];
    }
}