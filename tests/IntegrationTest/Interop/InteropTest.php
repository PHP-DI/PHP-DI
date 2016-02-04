<?php

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;

/**
 * @coversNothing
 */
class InteropTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function test_interop_definitions()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions('DI\Test\IntegrationTest\Interop\Fixture\ProviderA');
        $builder->addDefinitions('DI\Test\IntegrationTest\Interop\Fixture\ProviderB');
        $container = $builder->build();

        $this->assertEquals('a', $container->get('a'));
        $this->assertEquals('ab', $container->get('ab'));
        $this->assertEquals('b', $container->get('b'));
        $this->assertEquals('ba', $container->get('ba'));
        $this->assertEquals('bye', $container->get('overridden'));
        $this->assertEquals('hello world', $container->get('extended'));
    }
}
