<?php

namespace DI\Test\IntegrationTest;

use DI\Cache\ArrayCache;
use DI\ContainerBuilder;
use DI\Definition\Source\CachedDefinitionSource;

/**
 * Test caching.
 *
 * @coversNothing
 */
class CacheTest extends \PHPUnit_Framework_TestCase
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
    public function cached_definitions_should_be_overridables()
    {
        $builder = new ContainerBuilder();
        $builder->setDefinitionCache(new ArrayCache());
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);

        $container = $builder->build();

        $this->assertEquals('bar', $container->get('foo'));

        $container->set('foo', 'hello');

        $this->assertEquals('hello', $container->get('foo'));
    }
}
