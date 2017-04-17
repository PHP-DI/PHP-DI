<?php

namespace DI\Test\UnitTest\Cache;

use DI\Cache\ArrayCache;
use Psr\SimpleCache\CacheInterface;

/**
 * Copy of Doctrine's test.
 */
class ArrayCacheTest extends CacheTest
{
    protected function getCacheDriver() : CacheInterface
    {
        return new ArrayCache;
    }
}
