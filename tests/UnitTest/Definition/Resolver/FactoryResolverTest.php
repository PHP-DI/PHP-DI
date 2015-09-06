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

/**
 * @covers \DI\Definition\Resolver\FactoryResolver
 */
class FactoryResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FactoryResolver
     */
    private $resolver;

    public function setUp()
    {
        $container = EasyMock::mock('Interop\Container\ContainerInterface');
        $this->resolver = new FactoryResolver($container);
    }

    public function provideCallables()
    {
        return [
            'closure'        => [function () { return 'bar'; }],
            'string'         => [__NAMESPACE__ . '\FactoryDefinitionResolver_test'],
            'array'          => [[new FactoryDefinitionResolverTestClass(), 'foo']],
            'invokableClass' => [new FactoryDefinitionResolverCallableClass()],
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
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The factory definition "foo" is not callable
     */
    public function should_throw_if_the_factory_is_not_callable()
    {
        $definition = new FactoryDefinition('foo', 'Hello world');

        $this->resolver->resolve($definition);
    }
}

class FactoryDefinitionResolverTestClass
{
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
