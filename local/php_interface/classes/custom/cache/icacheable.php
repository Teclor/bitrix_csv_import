<?

namespace Custom\Cache;


interface ICacheable
{
    public function enableCachedMode();

    public function disableCachedMode();

    public function isCachedMode(): bool;

    public function getCacheTime(): int;
}