<?php

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\FactoryDefinition;
use DI\Definition\Resolver\FactoryResolver;
use EasyMock\EasyMock;
use Interop\Container\ContainerInterface;

/**
 * @covers \DI\Definition\Resolver\FactoryResolver
 */
class FactoryResolverTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    /**
     * @var FactoryResolver
     */
    private $resolver;

    public function setUp()
    {
        /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->easyMock(ContainerInterface::class);
        $this->resolver = new FactoryResolver($container);
    }

    /**
     * @test
     */
    public function should_resolve_callables()
    {
        $definition = new FactoryDefinition('foo', function () {
            return 'bar';
        });

        $value = $this->resolver->resolve($definition);

        $this->assertEquals('bar', $value);
    }

    /**
     * @test
     */
    public function should_inject_container()
    {
        $definition = new FactoryDefinition('foo', function ($c) {
            return $c;
        });

        $value = $this->resolver->resolve($definition);

        $this->assertInstanceOf(ContainerInterface::class, $value);
    }

    /**
     * @test
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessageRegExp /Entry "foo" cannot be resolved: factory ('|")Hello world('|") is neither a callable nor a valid container entry/
     */
    public function should_throw_if_the_factory_is_not_callable()
    {
        $definition = new FactoryDefinition('foo', 'Hello world');

        $this->resolver->resolve($definition);
    }

    /**
     * @test
     * @expectedException  \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage Entry "foo" cannot be resolved: Unable to invoke the callable because no value was given for parameter 3 ($c)
     */
    public function should_throw_if_not_enough_parameters()
    {
        $definition = new FactoryDefinition('foo', function ($a, $b, $c) {
        });

        $this->resolver->resolve($definition);
    }

    /**
     * @test
     */
    public function should_inject_parameters()
    {
        $testCase = $this;
        $definition = new FactoryDefinition('foo', function ($c, $par1, $par2) use ($testCase) {
            $testCase->assertEquals('Parameter 1', $par1);
            $testCase->assertEquals(2, $par2);

            return $c;
        }, null, ['par1' => 'Parameter 1', 'par2' => 2]);

        $value = $this->resolver->resolve($definition);

        $this->assertInstanceOf(ContainerInterface::class, $value);
    }
}
