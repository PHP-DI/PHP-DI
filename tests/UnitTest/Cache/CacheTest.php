<?php

namespace DI\Test\UnitTest\Cache;

use ArrayObject;
use Psr\SimpleCache\CacheInterface;

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
        $this->assertTrue($cache->set('key', 'value'));
        $this->assertTrue($cache->has('key'));
        $this->assertEquals('value', $cache->get('key'));

        // Test updating the value of a cache entry
        $this->assertTrue($cache->set('key', 'value-changed'));
        $this->assertTrue($cache->has('key'));
        $this->assertEquals('value-changed', $cache->get('key'));

        // Test deleting a value
        $this->assertTrue($cache->delete('key'));
        $this->assertFalse($cache->has('key'));
    }

    public function provideCrudValues()
    {
        return [
            'array'   => [['one', 2, 3.0]],
            'string'  => ['value'],
            'integer' => [1],
            'float'   => [1.5],
            'object'  => [new ArrayObject()],
            'null'    => [null],
        ];
    }

    public function testDeleteAll()
    {
        $cache = $this->getCacheDriver();

        $this->assertTrue($cache->set('key1', 1));
        $this->assertTrue($cache->set('key2', 2));
        $this->assertTrue($cache->clear());
        $this->assertFalse($cache->has('key1'));
        $this->assertFalse($cache->has('key2'));
    }

    public function testFetchMissShouldReturnFalse()
    {
        $cache = $this->getCacheDriver();

        $this->assertNull($cache->get('nonexistent_key'));
        $this->assertFalse($cache->get('nonexistent_key', false));
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

        $this->assertTrue($cache->set('key', $value));
        $this->assertTrue($cache->has('key'));
        $this->assertEquals($value, $cache->get('key'));
    }

    /**
     * The following values get converted to FALSE if you cast them to a boolean.
     * @see http://php.net/manual/en/types.comparisons.php
     */
    public function falseCastedValuesProvider()
    {
        return [
            [false],
            [null],
            [[]],
            ['0'],
            [0],
            [0.0],
            [''],
        ];
    }

    /**
     * Check to see that objects are correctly serialized and unserialized by the cache
     * provider.
     */
    public function testCachedObject()
    {
        $cache = $this->getCacheDriver();
        $cache->clear();
        $obj = new \stdClass();
        $obj->foo = 'bar';
        $obj2 = new \stdClass();
        $obj2->bar = 'foo';
        $obj2->obj = $obj;
        $obj->obj2 = $obj2;
        $cache->set('obj', $obj);

        $fetched = $cache->get('obj');

        $this->assertInstanceOf('stdClass', $obj);
        $this->assertInstanceOf('stdClass', $obj->obj2);
        $this->assertInstanceOf('stdClass', $obj->obj2->obj);
        $this->assertEquals('bar', $fetched->foo);
        $this->assertEquals('foo', $fetched->obj2->bar);
    }

    abstract protected function getCacheDriver() : CacheInterface;
}
