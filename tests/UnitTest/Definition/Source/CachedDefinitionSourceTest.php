<?php

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\ObjectDefinition;
use DI\Definition\Source\CachedDefinitionSource;
use DI\Definition\Source\DefinitionArray;
use DI\Definition\Source\DefinitionSource;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\Source\CachedDefinitionSource
 */
class CachedDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

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
        $source = $this->createMock(DefinitionSource::class);
        $source
            ->expects($this->once()) // The sub-source should be called ONLY ONCE
            ->method('getDefinition')
            ->willReturn('bar');

        $source = new CachedDefinitionSource($source);

        self::assertEquals('bar', $source->getDefinition('foo'));
        self::assertEquals('bar', $source->getDefinition('foo'));
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
