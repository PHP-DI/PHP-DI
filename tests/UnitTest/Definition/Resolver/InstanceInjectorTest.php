<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\InstanceDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\ObjectDefinition\PropertyInjection;
use DI\Definition\Resolver\InstanceInjector;
use DI\Definition\Resolver\ResolverDispatcher;
use DI\Proxy\ProxyFactory;
use DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureClass;
use EasyMock\EasyMock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Definition\Resolver\InstanceInjector
 */
class InstanceInjectorTest extends TestCase
{
    use EasyMock;

    /**
     * @test
     */
    public function should_inject_properties_on_instance()
    {
        $instance = new FixtureClass('');

        $objectDefinition = new ObjectDefinition(get_class($instance));
        $objectDefinition->addPropertyInjection(new PropertyInjection('prop', 'value'));

        $resolver = $this->buildResolver();
        $resolver->resolve(new InstanceDefinition($instance, $objectDefinition));

        $this->assertEquals('value', $instance->prop);
    }

    /**
     * @test
     */
    public function should_inject_methods_on_instance()
    {
        $instance = new FixtureClass('');

        $objectDefinition = new ObjectDefinition(get_class($instance));
        $objectDefinition->addMethodInjection(new MethodInjection('method', ['value']));

        $resolver = $this->buildResolver();
        $resolver->resolve(new InstanceDefinition($instance, $objectDefinition));

        $this->assertEquals('value', $instance->methodParam1);
    }

    private function buildResolver()
    {
        /** @var ResolverDispatcher $resolverDispatcher */
        $resolverDispatcher = $this->easyMock(ResolverDispatcher::class);
        /** @var ProxyFactory $factory */
        $factory = $this->easyMock(ProxyFactory::class);

        return new InstanceInjector($resolverDispatcher, $factory);
    }
}
