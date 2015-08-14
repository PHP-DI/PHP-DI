<?php

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\ObjectDefinition;
use DI\Definition\ValueDefinition;
use DI\Definition\Source\CachedDefinitionSource;
use DI\Definition\Source\DefinitionArray;
use Doctrine\Common\Cache\Cache;
use EasyMock\EasyMock;

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
        /** @var Cache $cache */
        $cache = $this->easySpy(Cache::class, [
            'fetch' => 'foo',
        ]);

        $source = new CachedDefinitionSource(new DefinitionArray(), $cache);

        $this->assertEquals('foo', $source->getDefinition('foo'));
    }

    /**
     * @test
     */
    public function should_save_to_cache_and_return()
    {
        $cache = $this->easySpy(Cache::class, [
            'fetch' => false,
        ]);

        $cachedSource = new DefinitionArray([
            'foo' => \DI\object(),
        ]);

        $source = new CachedDefinitionSource($cachedSource, $cache);

        $expectedDefinition = new ObjectDefinition('foo');
        $expectedDefinition->setName('foo');
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
        $cache = $this->easySpy(Cache::class, [
            'fetch' => false,
        ]);

        $source = new CachedDefinitionSource(new DefinitionArray(), $cache);

        $cache->expects($this->once())
            ->method('save')
            ->with($this->isType('string'), null);
        $this->assertNull($source->getDefinition('foo'));
    }
}
