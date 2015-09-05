<?php

namespace DI\Test\UnitTest\Cache;

use ArrayObject;

/**
 * Copy of Doctrine's test.
 */
abstract class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideCrudValues
     */
    public function testBasicCrudOperations($value)
    {
        $cache = $this->getCacheDriver();

        // Test saving a value, checking if it exists, and fetching it back
        $this->assertTrue($cache->save('key', 'value'));
        $this->assertTrue($cache->contains('key'));
        $this->assertEquals('value', $cache->fetch('key'));

        // Test updating the value of a cache entry
        $this->assertTrue($cache->save('key', 'value-changed'));
        $this->assertTrue($cache->contains('key'));
        $this->assertEquals('value-changed', $cache->fetch('key'));

        // Test deleting a value
        $this->assertTrue($cache->delete('key'));
        $this->assertFalse($cache->contains('key'));
    }


    public function provideCrudValues()
    {
        return array(
            'array' => array(array('one', 2, 3.0)),
            'string' => array('value'),
            'integer' => array(1),
            'float' => array(1.5),
            'object' => array(new ArrayObject()),
            'null' => array(null),
        );
    }

    public function testDeleteAll()
    {
        $cache = $this->getCacheDriver();

        $this->assertTrue($cache->save('key1', 1));
        $this->assertTrue($cache->save('key2', 2));
        $this->assertTrue($cache->deleteAll());
        $this->assertFalse($cache->contains('key1'));
        $this->assertFalse($cache->contains('key2'));
    }

    public function testFlushAll()
    {
        $cache = $this->getCacheDriver();

        $this->assertTrue($cache->save('key1', 1));
        $this->assertTrue($cache->save('key2', 2));
        $this->assertTrue($cache->flushAll());
        $this->assertFalse($cache->contains('key1'));
        $this->assertFalse($cache->contains('key2'));
    }

    public function testFetchMissShouldReturnFalse()
    {
        $cache = $this->getCacheDriver();

        /* Ensure that caches return boolean false instead of null on a fetch
         * miss to be compatible with ORM integration.
         */
        $result = $cache->fetch('nonexistent_key');

        $this->assertFalse($result);
        $this->assertNotNull($result);
    }

    /**
     * Check to see that, even if the user saves a value that can be interpreted as false,
     * the cache adapter will still recognize its existence there.
     *
     * @dataProvider falseCastedValuesProvider
     */
    public function testFalseCastedValues($value)
    {
        $cache = $this->getCacheDriver();

        $this->assertTrue($cache->save('key', $value));
        $this->assertTrue($cache->contains('key'));
        $this->assertEquals($value, $cache->fetch('key'));
    }

    /**
     * The following values get converted to FALSE if you cast them to a boolean.
     * @see http://php.net/manual/en/types.comparisons.php
     */
    public function falseCastedValuesProvider()
    {
        return array(
            array(false),
            array(null),
            array(array()),
            array('0'),
            array(0),
            array(0.0),
            array('')
        );
    }

    /**
     * Check to see that objects are correctly serialized and unserialized by the cache
     * provider.
     */
    public function testCachedObject()
    {
        $cache = $this->getCacheDriver();
        $cache->deleteAll();
        $obj = new \stdClass();
        $obj->foo = "bar";
        $obj2 = new \stdClass();
        $obj2->bar = "foo";
        $obj2->obj = $obj;
        $obj->obj2 = $obj2;
        $cache->save("obj", $obj);

        $fetched = $cache->fetch("obj");

        $this->assertInstanceOf("stdClass", $obj);
        $this->assertInstanceOf("stdClass", $obj->obj2);
        $this->assertInstanceOf("stdClass", $obj->obj2->obj);
        $this->assertEquals("bar", $fetched->foo);
        $this->assertEquals("foo", $fetched->obj2->bar);
    }

    /**
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    abstract protected function getCacheDriver();
}
