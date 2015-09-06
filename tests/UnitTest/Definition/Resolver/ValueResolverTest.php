<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\ValueDefinition;
use DI\Definition\Resolver\ValueResolver;

/**
 * @covers \DI\Definition\Resolver\ValueResolver
 */
class ValueResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_resolve_value_definitions()
    {
        $resolver = new ValueResolver();

        $definition = new ValueDefinition('foo', 'bar');

        $this->assertTrue($resolver->isResolvable($definition));
        $this->assertEquals('bar', $resolver->resolve($definition));
    }
}
