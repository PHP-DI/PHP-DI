<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Resolver;

use DI\Definition\AliasDefinition;
use DI\Definition\Resolver\AliasDefinitionResolver;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\DefinitionResolver\AliasDefinitionResolver
 */
class AliasDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolve()
    {
        $container = $this->getMock('DI\Container', array(), array(), '', false);
        $container->expects($this->once())
            ->method('get')
            ->with('bar')
            ->will($this->returnValue(42));

        $definition = new AliasDefinition('foo', 'bar');
        $resolver = new AliasDefinitionResolver($container);

        $value = $resolver->resolve($definition);

        $this->assertEquals(42, $value);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with AliasDefinition objects, DI\Definition\ValueDefinition given
     */
    public function testInvalidDefinitionType()
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $definition = new ValueDefinition('foo', 'bar');
        $resolver = new AliasDefinitionResolver($container);

        $resolver->resolve($definition);
    }
}
