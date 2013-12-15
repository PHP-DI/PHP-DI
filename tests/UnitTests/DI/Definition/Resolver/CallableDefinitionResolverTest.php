<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Resolver;

use DI\Definition\CallableDefinition;
use DI\Definition\ValueDefinition;
use DI\Definition\Resolver\CallableDefinitionResolver;

/**
 * @covers \DI\Definition\Resolver\CallableDefinitionResolver
 */
class CallableDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolve()
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $definition = new CallableDefinition('foo', function () {
            return 'bar';
        });
        $resolver = new CallableDefinitionResolver($container);

        $value = $resolver->resolve($definition);

        $this->assertEquals('bar', $value);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with CallableDefinition objects, DI\Definition\ValueDefinition given
     */
    public function testInvalidDefinitionType()
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $definition = new ValueDefinition('foo', 'bar');
        $resolver = new CallableDefinitionResolver($container);

        $resolver->resolve($definition);
    }
}
