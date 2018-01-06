<?php
declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use DI\Definition\Source\SourceCache;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
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
}
