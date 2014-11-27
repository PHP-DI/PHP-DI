<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\AliasDefinition;
use DI\Definition\ArrayDefinition;
use DI\Definition\Resolver\AliasDefinitionResolver;
use DI\Definition\Resolver\ArrayDefinitionResolver;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Resolver\ArrayDefinitionResolver
 */
class ArrayDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testGetContainer()
    {
        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $resolver = new ArrayDefinitionResolver($container);

        $this->assertSame($container, $resolver->getContainer());
    }

    public function testResolve()
    {
        $container = $this->getMock('DI\Container', array(), array(), '', false);
        $container->expects($this->once())
            ->method('get')
            ->with('bar')
            ->will($this->returnValue(42));
        $resolver = new ArrayDefinitionResolver($container);

        $definition = new ArrayDefinition('foo', array('bar', \DI\link('bar')));

        $value = $resolver->resolve($definition);

        $this->assertEquals(array('bar', 42), $value);
    }

    public function testResolveShouldPreserveKeys()
    {
        $container = $this->getMock('DI\Container', array(), array(), '', false);
        $container->expects($this->once())
            ->method('get')
            ->with('bar')
            ->will($this->returnValue(42));
        $resolver = new ArrayDefinitionResolver($container);

        $definition = new ArrayDefinition('foo', array(
            'hello' => \DI\link('bar')
        ));

        $value = $resolver->resolve($definition);

        $this->assertEquals(array('hello' => 42), $value);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with ArrayDefinition objects, DI\Definition\ValueDefinition given
     */
    public function testInvalidDefinitionType()
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $definition = new ValueDefinition('foo', 'bar');
        $resolver = new ArrayDefinitionResolver($container);

        $resolver->resolve($definition);
    }
}
