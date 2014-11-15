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

    public function testResolve()
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $definition = new FactoryDefinition('foo', function () {
            return 'bar';
        });
        $resolver = new FactoryDefinitionResolver($container);

        $value = $resolver->resolve($definition);

        $this->assertEquals('bar', $value);
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
