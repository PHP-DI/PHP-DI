<?php

namespace DI\Test\UnitTest\Cache;

use DI\Cache\ArrayCache;

/**
 * Copy of Doctrine's test.
 */
class ArrayCacheTest extends CacheTest
{
    protected function getCacheDriver()
    {
        return new ArrayCache();
    }
}
