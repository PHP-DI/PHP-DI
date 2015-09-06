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
use DI\Definition\Resolver\AliasResolver;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\Resolver\AliasResolver
 */
class AliasResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_resolve_aliases()
    {
        $container = EasyMock::mock('Interop\Container\ContainerInterface', [
            'get' => 42,
        ]);
        $resolver = new AliasResolver($container);

        $value = $resolver->resolve(new AliasDefinition('foo', 'bar'));

        $this->assertEquals(42, $value);
    }
}
