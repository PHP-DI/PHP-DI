<?php

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\ObjectDefinition;
use DI\Definition\Source\CachedDefinitionSource;
use DI\Definition\Source\DefinitionArray;
use EasyMock\EasyMock;
use Psr\SimpleCache\CacheInterface;

/**
 * @covers \DI\Definition\Source\CachedDefinitionSource
 */
class CachedDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    /**
     * @test
     */
    public function should_get_from_cache()
    {
        /** @var CacheInterface $cache */
        $cache = $this->easySpy(CacheInterface::class, [
            'get' => 'foo',
        ]);

        $source = new CachedDefinitionSource(new DefinitionArray(), $cache);

        $this->assertEquals('foo', $source->getDefinition('foo'));
    }

    public function testCacheKey()
    {
        $cache = $this->easySpy(CacheInterface::class);

        $source = new CachedDefinitionSource(new DefinitionArray(), $cache);

        $cache->expects($this->once())
            ->method('get')
            ->with(CachedDefinitionSource::CACHE_PREFIX . 'foo.bar.baz')
            ->will($this->returnValue(false));

        $cache->expects($this->once())
            ->method('set')
            ->with(CachedDefinitionSource::CACHE_PREFIX . 'foo.bar.baz');

        $source->getDefinition('foo\\bar\\baz');
    }

    /**
     * @test
     */
    public function should_save_to_cache_and_return()
    {
        $cache = $this->easySpy(CacheInterface::class, [
            'get' => false,
        ]);

        $cachedSource = new DefinitionArray([
            'foo' => \DI\create(),
        ]);

        $source = new CachedDefinitionSource($cachedSource, $cache);

        $expectedDefinition = new ObjectDefinition('foo');
        $cache->expects($this->once())
            ->method('set')
            ->with($this->isType('string'), $expectedDefinition);

        $this->assertEquals($expectedDefinition, $source->getDefinition('foo'));
    }

    /**
     * @test
     */
    public function should_save_null_to_cache_and_return_null()
    {
        $cache = $this->easySpy(CacheInterface::class, [
            'get' => false,
        ]);

        $source = new CachedDefinitionSource(new DefinitionArray(), $cache);

        $cache->expects($this->once())
            ->method('set')
            ->with($this->isType('string'), null);
        $this->assertNull($source->getDefinition('foo'));
    }
}
