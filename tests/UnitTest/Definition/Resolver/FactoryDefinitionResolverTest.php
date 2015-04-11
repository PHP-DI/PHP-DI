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
use DI\Definition\ValueDefinition;
use DI\Definition\Resolver\FactoryDefinitionResolver;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\Resolver\FactoryDefinitionResolver
 */
class FactoryDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FactoryDefinitionResolver
     */
    private $resolver;

    public function setUp()
    {
        $container = EasyMock::mock('Interop\Container\ContainerInterface');
        $this->resolver = new FactoryDefinitionResolver($container);
    }

    public function provideCallables()
    {
        return array(
            'closure'        => array(function () { return 'bar'; }),
            'string'         => array(__NAMESPACE__ . '\FactoryDefinitionResolver_test'),
            'array'          => array(array(new FactoryDefinitionResolverTestClass(), 'foo')),
            'invokableClass' => array(new FactoryDefinitionResolverCallableClass()),
        );
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

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with FactoryDefinition objects, DI\Definition\ValueDefinition given
     */
    public function should_only_resolve_factory_definitions()
    {
        $definition = new ValueDefinition('foo', 'bar');

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
