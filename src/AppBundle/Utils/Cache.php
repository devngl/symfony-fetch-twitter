<?php

namespace AppBundle\Utils;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class Cache
{
    /** @var CacheItemPoolInterface  */
    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param $key
     * @param $minutes
     * @param $callback
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function remember($key, $minutes, $callback)
    {
        /** @var CacheItemInterface $cacheItem */
        $cacheItem = $this->cache->getItem($key);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $newCacheValue = $callback();
        $cacheItem->set($newCacheValue);
        $cacheItem->expiresAfter($minutes * 60);
        $this->cache->save($cacheItem);
        return $newCacheValue;
    }
}