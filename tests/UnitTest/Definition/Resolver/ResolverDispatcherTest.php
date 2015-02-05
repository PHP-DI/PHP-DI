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
 * @covers \DI\Definition\Resolver\InstanceDefinitionResolver
 */
class ResolverDispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_resolve_using_sub_resolvers()
    {
        $resolvers = array(
            'DI\Definition\ValueDefinition' => EasyMock::mock('DI\Definition\Resolver\DefinitionResolver', array(
                'resolve' => 'foo',
            )),
            'DI\Definition\StringDefinition' => EasyMock::mock('DI\Definition\Resolver\DefinitionResolver', array(
                'resolve' => 'bar',
            )),
        );

        $dispatcher = new ResolverDispatcher($resolvers);

        $this->assertEquals('foo', $dispatcher->resolve(new ValueDefinition('name', 'value')));
        $this->assertEquals('bar', $dispatcher->resolve(new StringDefinition('name', 'value')));
    }

    /**
     * @test
     */
    public function it_should_test_if_resolvable_using_sub_resolvers()
    {
        $resolvers = array(
            'DI\Definition\ValueDefinition' => EasyMock::mock('DI\Definition\Resolver\DefinitionResolver', array(
                'isResolvable' => true,
            )),
            'DI\Definition\StringDefinition' => EasyMock::mock('DI\Definition\Resolver\DefinitionResolver', array(
                'isResolvable' => false,
            )),
        );

        $dispatcher = new ResolverDispatcher($resolvers);

        $this->assertTrue($dispatcher->isResolvable(new ValueDefinition('name', 'value')));
        $this->assertFalse($dispatcher->isResolvable(new StringDefinition('name', 'value')));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No definition resolver was configured for definition of type DI\Definition\ValueDefinition
     */
    public function it_should_throw_if_non_handled_definition()
    {
        $dispatcher = new ResolverDispatcher(array());
        $dispatcher->resolve(new ValueDefinition('name', 'value'));
    }
}
