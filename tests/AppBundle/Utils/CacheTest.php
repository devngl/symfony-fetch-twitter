<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Utils\Cache;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CacheTest extends WebTestCase
{
    /**
     * @test we can store and fetch cache values by a given key
     * @throws \Psr\Cache\InvalidArgumentException
     */
    function remember_stores_values_in_cache()
    {
        $container = $this->createClient()->getContainer();
        $cache = $container->get(Cache::class);

        $testedKey = 'test_key_';
        $container->get('cache.app')->deleteItem($testedKey);
        $cache->remember($testedKey, 1, function () {
            return 'value';
        });

        $storedValue = $cache->remember($testedKey, 1, function () {
            $this->fail('Callback should not be reached with existing key on cache');
        });

        $this->assertEquals($storedValue, 'value');
    }
}
