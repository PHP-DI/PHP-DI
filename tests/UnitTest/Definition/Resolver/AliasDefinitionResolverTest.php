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
use DI\Definition\Resolver\AliasDefinitionResolver;
use DI\Definition\ValueDefinition;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\Resolver\AliasDefinitionResolver
 */
class AliasDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_resolve_aliases()
    {
        $container = EasyMock::mock('Interop\Container\ContainerInterface', array(
            'get' => 42,
        ));
        $resolver = new AliasDefinitionResolver($container);

        $value = $resolver->resolve(new AliasDefinition('foo', 'bar'));

        $this->assertEquals(42, $value);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with AliasDefinition objects, DI\Definition\ValueDefinition given
     */
    public function should_only_resolve_aliases()
    {
        $container = EasyMock::mock('Interop\Container\ContainerInterface');
        $resolver = new AliasDefinitionResolver($container);

        $resolver->resolve(new ValueDefinition('foo', 'bar'));
    }
}
