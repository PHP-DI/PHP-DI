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
use DI\Definition\Resolver\ValueDefinitionResolver;

/**
 * @covers \DI\Definition\Resolver\ValueDefinitionResolver
 */
class ValueDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_resolve_value_definitions()
    {
        $resolver = new ValueDefinitionResolver();

        $definition = new ValueDefinition('foo', 'bar');

        $this->assertTrue($resolver->isResolvable($definition));
        $this->assertEquals('bar', $resolver->resolve($definition));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with ValueDefinition objects, DI\Definition\FactoryDefinition given
     */
    public function should_only_resolve_value_definitions()
    {
        $resolver = new ValueDefinitionResolver();

        $definition = new FactoryDefinition('foo', function () {
        });

        $resolver->resolve($definition);
    }
}
