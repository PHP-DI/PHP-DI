<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\Resolver\ResolverDispatcher;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\Resolver\ResolverDispatcher
 */
class ResolverDispatcherTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $proxyFactory;
    /**
     * @var ResolverDispatcher
     */
    private $resolver;

    public function setUp()
    {
        $this->container = EasyMock::mock('Interop\Container\ContainerInterface');
        $this->proxyFactory = EasyMock::mock('DI\Proxy\ProxyFactory');
        $this->resolver = new ResolverDispatcher($this->container, $this->proxyFactory);
    }

    /**
     * @test
     */
    public function should_resolve_using_sub_resolvers()
    {
        $this->assertEquals('foo', $this->resolver->resolve(new ValueDefinition('name', 'foo')));
        $this->assertEquals('bar', $this->resolver->resolve(new StringDefinition('name', 'bar')));
    }

    /**
     * @test
     */
    public function should_test_if_resolvable_using_sub_resolvers()
    {
        $this->assertTrue($this->resolver->isResolvable(new ValueDefinition('name', 'value')));
        $this->assertTrue($this->resolver->isResolvable(new StringDefinition('name', 'value')));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No definition resolver was configured for definition of type
     */
    public function should_throw_if_non_handled_definition()
    {
        $this->resolver->resolve(EasyMock::mock('DI\Definition\Definition'));
    }
}
