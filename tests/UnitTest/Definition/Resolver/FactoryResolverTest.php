<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\FactoryDefinition;
use DI\Definition\Resolver\FactoryResolver;
use EasyMock\EasyMock;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \DI\Definition\Resolver\FactoryResolver
 */
class FactoryResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var FactoryResolver
     */
    private $resolver;

    public function setUp()
    {
        $this->container = EasyMock::mock('Interop\Container\ContainerInterface');
        $this->resolver = new FactoryResolver($this->container);
    }

    public function provideCallables()
    {
        return [
            'closure'               => [function () { return 'bar'; }],
            'functionString'        => [__NAMESPACE__ . '\FactoryDefinitionResolver_test'],
            'invokableObject'       => [new FactoryDefinitionResolverCallableClass],
            '[object, method]'      => [[new FactoryDefinitionResolverTestClass, 'foo']],
            '[Class, staticMethod]' => [[__NAMESPACE__ . '\FactoryDefinitionResolverTestClass', 'staticFoo']],
            'Class::staticMethod'   => [__NAMESPACE__ . '\FactoryDefinitionResolverTestClass::staticFoo'],
        ];
    }

    public function provideContainerCallables()
    {
        return [
            'closureEntry' => [
                'closure',
                'closure',
                function () { return 'bar'; },
            ],
            'invokableEntry' => [
                'invokable',
                'invokable',
                new FactoryDefinitionResolverCallableClass,
            ],
            '[classEntry, method]' => [
                [__NAMESPACE__ . '\FactoryDefinitionResolverTestClass', 'foo'],
                __NAMESPACE__ . '\FactoryDefinitionResolverTestClass',
                new FactoryDefinitionResolverTestClass,
            ],
            'classEntry::method' => [
                __NAMESPACE__ . '\FactoryDefinitionResolverTestClass::foo',
                __NAMESPACE__ . '\FactoryDefinitionResolverTestClass',
                new FactoryDefinitionResolverTestClass,
            ],
            '[arbitraryClassEntry, method]' => [
                ['some.class', 'foo'],
                'some.class',
                new FactoryDefinitionResolverTestClass,
            ],
            'arbitraryClassEntry::method' => [
                'some.class::foo',
                'some.class',
                new FactoryDefinitionResolverTestClass,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideCallables
     */
    public function should_resolve_callables($callable)
    {
        $definition = new FactoryDefinition('foo', $callable);

        $value = $this->resolver->resolve($definition);

        $this->assertEquals('bar', $value);
    }

    /**
     * @test
     * @dataProvider provideContainerCallables
     */
    public function should_resolve_callables_from_container($callable, $containerName, $containerEntry)
    {
        EasyMock::mock($this->container, [
            'has' => function ($name) use ($containerName) {
                return $name === $containerName;
            },
            'get' => function ($name) use ($containerName, $containerEntry) {
                return $name === $containerName ? $containerEntry : false;
            },
        ]);

        $definition = new FactoryDefinition('foo', $callable);

        $value = $this->resolver->resolve($definition);

        $this->assertEquals('bar', $value);
    }

    /**
     * @test
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The factory definition "foo" is not callable
     */
    public function should_throw_if_the_factory_is_not_callable()
    {
        $definition = new FactoryDefinition('foo', 'Hello world');

        $this->resolver->resolve($definition);
    }

    /**
     * @test
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The factory definition "foo" is not callable
     */
    public function should_throw_if_the_factory_is_not_callable_container_entry()
    {
        EasyMock::mock($this->container, [
            'has' => true,
            'get' => 42,
        ]);

        $definition = new FactoryDefinition('foo', 'Hello world');

        $this->resolver->resolve($definition);
    }
}

class FactoryDefinitionResolverTestClass
{
    public static function staticFoo()
    {
        return 'bar';
    }

    public function foo()
    {
        return 'bar';
    }
}

class FactoryDefinitionResolverCallableClass
{
    public function __invoke()
    {
        return 'bar';
    }
}

function FactoryDefinitionResolver_test()
{
    return 'bar';
}
