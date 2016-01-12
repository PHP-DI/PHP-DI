<?php

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use Doctrine\Common\Cache\ArrayCache;

/**
 * Test caching.
 *
 * @coversNothing
 */
class CacheTest extends \PHPUnit_Framework_TestCase
{
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
