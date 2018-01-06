<?php
declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\ObjectDefinition;
use DI\Definition\Reference;
use DI\Definition\Source\DefinitionArray;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\Source\SourceCache;
use EasyMock\EasyMock;
use PHPUnit\Framework\TestCase;

class SourceCacheTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        if (! SourceCache::isSupported()) {
            $this->markTestSkipped('APCu extension is required');
        }

        apcu_clear_cache();
    }

    /**
     * @test
     */
    public function should_get_from_cache()
    {
        $definition = new ObjectDefinition('foo');

        $wrappedSource = $this->createMock(DefinitionSource::class);
        $wrappedSource
            ->expects($this->once())// The sub-source should be called ONLY ONCE
            ->method('getDefinition')
            ->willReturn($definition);

        $source = new SourceCache($wrappedSource);

        self::assertSame($definition, $source->getDefinition('foo'));
        self::assertSame($definition, $source->getDefinition('foo'));
    }

    /**
     * @test
     */
    public function should_save_to_cache_and_return()
    {
        $cachedSource = new DefinitionArray([
            'foo' => \DI\create(),
        ]);

        $source = new SourceCache($cachedSource);

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
        $source = new SourceCache(new DefinitionArray);

        self::assertNull($source->getDefinition('foo'));
        self::assertSavedInCache('foo', null);
    }

    /**
     * @test
     */
    public function should_only_cache_object_and_autowire_definitions()
    {
        $definition = new Reference('foo');

        $wrappedSource = $this->createMock(DefinitionSource::class);
        $wrappedSource
            ->expects($this->exactly(2))
            ->method('getDefinition')
            ->willReturn($definition);

        $source = new SourceCache($wrappedSource);

        self::assertSame($definition, $source->getDefinition('foo'));
        self::assertSame($definition, $source->getDefinition('foo'));
    }

    private static function assertSavedInCache(string $definitionName, $expectedValue)
    {
        $definitions = apcu_fetch(SourceCache::CACHE_KEY);
        self::assertEquals($expectedValue, $definitions[$definitionName]);
    }
}
