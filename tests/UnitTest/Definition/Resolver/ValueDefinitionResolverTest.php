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
    public function testResolve()
    {
        $definition = new ValueDefinition('foo', 'bar');
        $resolver = new ValueDefinitionResolver();

        $value = $resolver->resolve($definition);

        $this->assertEquals('bar', $value);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with ValueDefinition objects, DI\Definition\FactoryDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new FactoryDefinition('foo', function () {
        });
        $resolver = new ValueDefinitionResolver();

        $resolver->resolve($definition);
    }
}
