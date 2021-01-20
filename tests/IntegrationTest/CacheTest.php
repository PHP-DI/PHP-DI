<?php
declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use function DI\create;
use DI\Definition\ObjectDefinition;
use DI\Definition\Source\SourceCache;

class CacheTest extends BaseContainerTest
{
    public function setUp(): void
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
    public function cached_definitions_should_be_overridable()
    {
        $builder = new ContainerBuilder();
        $builder->enableDefinitionCache();
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);
        $container = $builder->build();
        $this->assertEquals('bar', $container->get('foo'));
        $container->set('foo', 'hello');
        $this->assertEquals('hello', $container->get('foo'));
    }

    /**
     * @test
     */
    public function compiled_entries_should_not_be_put_in_cache()
    {
        $builder = new ContainerBuilder();
        $builder->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $builder->enableDefinitionCache();
        $builder->addDefinitions([
            'foo' => create(\stdClass::class),
        ]);
        $container = $builder->build();
        $container->get('foo');

        $cachedDefinition = apcu_fetch(SourceCache::CACHE_KEY . 'foo');
        self::assertFalse($cachedDefinition);
    }

    /**
     * @test
     */
    public function non_compiled_entries_should_be_put_in_cache()
    {
        $builder = new ContainerBuilder();
        $builder->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $builder->enableDefinitionCache();
        $container = $builder->build();
        $container->get(\stdClass::class);

        $cachedDefinition = apcu_fetch(SourceCache::CACHE_KEY . \stdClass::class);
        self::assertInstanceOf(ObjectDefinition::class, $cachedDefinition);
    }
}
