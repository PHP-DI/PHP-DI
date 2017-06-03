<?php

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\AliasDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\Source\CachedDefinitionSource;
use DI\Definition\Source\DefinitionArray;
use DI\Definition\Source\DefinitionSource;

/**
 * @covers \DI\Definition\Source\CachedDefinitionSource
 */
class CachedDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!CachedDefinitionSource::isSupported()) {
            $this->markTestSkipped('APCu extension is required');
        }

        apcu_clear_cache();
    }

    /**
     * @test
     */
    public function should_get_from_cache()
    {
        $definition = new AliasDefinition('foo', 'bar');

        $source = $this->createMock(DefinitionSource::class);
        $source
            ->expects($this->once()) // The sub-source should be called ONLY ONCE
            ->method('getDefinition')
            ->willReturn($definition);

        $source = new CachedDefinitionSource($source);

        self::assertEquals($definition, $source->getDefinition('foo'));
        self::assertEquals($definition, $source->getDefinition('foo'));
    }

    /**
     * @test
     */
    public function should_save_to_cache_and_return()
    {
        $cachedSource = new DefinitionArray([
            'foo' => \DI\create(),
        ]);

        $source = new CachedDefinitionSource($cachedSource);

        // Sanity check
        self::assertSavedInCache('foo', null);

        // Return the definition
        self::assertEquals(new ObjectDefinition('foo'), $source->getDefinition('foo'));

        // The definition is saved in the cache
        self::assertSavedInCache('foo', new ObjectDefinition('foo'));
    }

    /**
     * @test
     */
    public function should_save_null_to_cache_and_return_null()
    {
        $source = new CachedDefinitionSource(new DefinitionArray());

        self::assertNull($source->getDefinition('foo'));
        self::assertSavedInCache('foo', null);
    }

    private static function assertSavedInCache(string $definitionName, $expectedValue)
    {
        $definitions = apcu_fetch(CachedDefinitionSource::CACHE_KEY);
        self::assertEquals($expectedValue, $definitions[$definitionName]);
    }
}
