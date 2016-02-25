<?php

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;

/**
 * @coversNothing
 */
class ServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function test_service_provider()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'native' => 'this',
        ]);
        $builder->addDefinitions('DI\Test\IntegrationTest\Interop\Fixture\ProviderA');
        $builder->addDefinitions('DI\Test\IntegrationTest\Interop\Fixture\ProviderB');
        $builder->addDefinitions([
            'native' => \DI\decorate(function ($previous) {
                return $previous . '!';
            }),
        ]);
        $container = $builder->build();

        $this->assertEquals('a', $container->get('a'));
        $this->assertEquals('ab', $container->get('ab'));
        $this->assertEquals('b', $container->get('b'));
        $this->assertEquals('ba', $container->get('ba'));
        $this->assertEquals('bye', $container->get('overridden'));
        $this->assertEquals('hello world', $container->get('extended'));
        $this->assertEquals(' world', $container->get('no_previous'));
        $this->assertEquals('this is awesome!', $container->get('native'));
    }

    /**
     * Test a bug that happened when overriding an incomplete autowiring definition.
     *
     * @test
     */
    public function test_override_incomplete_autowiring_definition()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions('DI\Test\IntegrationTest\Interop\Fixture\ProviderA');
        $this->assertNull($builder->build()->get('DI\Test\IntegrationTest\Interop\Fixture\Object1'));
    }
}
