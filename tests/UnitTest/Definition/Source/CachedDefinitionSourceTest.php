<?php

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\Source\DefinitionArray;
use DI\Definition\Source\CachedDefinitionSource;
use DI\Definition\ValueDefinition;
use Doctrine\Common\Cache\Cache;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\Source\CachedDefinitionSource
 */
class CachedDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_get_from_cache()
    {
        /** @var Cache $cache */
        $cache = EasyMock::spy('Doctrine\Common\Cache\Cache', [
            'fetch' => 'foo',
        ]);

        $source = new CachedDefinitionSource(new DefinitionArray(), $cache);

        $this->assertEquals($cache, $source->getCache());

        $this->assertEquals('foo', $source->getDefinition('foo'));
    }

    /**
     * @test
     */
    public function should_save_to_cache_and_return()
    {
        $cache = EasyMock::spy('Doctrine\Common\Cache\Cache', [
            'fetch' => false,
        ]);

        $cachedSource = new DefinitionArray([
            'foo' => 'bar',
        ]);

        $source = new CachedDefinitionSource($cachedSource, $cache);

        $expectedDefinition = new ValueDefinition('foo', 'bar');
        $cache->expects($this->once())
            ->method('save')
            ->with($this->isType('string'), $expectedDefinition);

        $this->assertEquals($expectedDefinition, $source->getDefinition('foo'));
    }

    /**
     * @test
     */
    public function should_save_null_to_cache_and_return_null()
    {
        $cache = EasyMock::spy('Doctrine\Common\Cache\Cache', [
            'fetch' => false,
        ]);

        $source = new CachedDefinitionSource(new DefinitionArray(), $cache);

        $cache->expects($this->once())
            ->method('save')
            ->with($this->isType('string'), null);
        $this->assertNull($source->getDefinition('foo'));
    }
}
