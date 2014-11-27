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

/**
 * @covers \DI\Definition\Resolver\FactoryDefinitionResolver
 */
class FactoryDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testGetContainer()
    {
        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $resolver = new FactoryDefinitionResolver($container);

        $this->assertSame($container, $resolver->getContainer());
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
     * @dataProvider provideCallables
     */
    public function testResolve($callable)
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $definition = new FactoryDefinition('foo', $callable);
        $resolver = new FactoryDefinitionResolver($container);

        $value = $resolver->resolve($definition);

        $this->assertEquals('bar', $value);
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The factory definition "foo" is not callable
     */
    public function testNotCallable()
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $definition = new FactoryDefinition('foo', 'Hello world');
        $resolver = new FactoryDefinitionResolver($container);

        $resolver->resolve($definition);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with FactoryDefinition objects, DI\Definition\ValueDefinition given
     */
    public function testInvalidDefinitionType()
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $definition = new ValueDefinition('foo', 'bar');
        $resolver = new FactoryDefinitionResolver($container);

        $resolver->resolve($definition);
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
