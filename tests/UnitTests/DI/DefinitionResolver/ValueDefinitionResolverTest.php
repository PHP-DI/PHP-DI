<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\DefinitionResolver;

use DI\Definition\CallableDefinition;
use DI\Definition\ValueDefinition;
use DI\DefinitionResolver\ValueDefinitionResolver;

/**
 * @covers \DI\DefinitionResolver\ValueDefinitionResolver
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
     * @expectedExceptionMessage This definition resolver is only compatible with ValueDefinition objects, DI\Definition\CallableDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new CallableDefinition('foo', function () {
        });
        $resolver = new ValueDefinitionResolver();

        $resolver->resolve($definition);
    }
}
