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
use DI\Definition\Resolver\StringDefinitionResolver;
use DI\Definition\StringDefinition;
use DI\NotFoundException;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\Resolver\StringDefinitionResolver
 */
class StringDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_resolve_bare_strings()
    {
        $container = EasyMock::mock('Interop\Container\ContainerInterface');

        $definition = new StringDefinition('foo', 'bar');
        $resolver = new StringDefinitionResolver($container);

        $value = $resolver->resolve($definition);

        $this->assertEquals('bar', $value);
    }

    /**
     * @test
     */
    public function it_should_resolve_references()
    {
        $container = EasyMock::mock('Interop\Container\ContainerInterface', array(
            'get' => 'bar',
        ));

        $definition = new StringDefinition('foo', '{test}');
        $resolver = new StringDefinitionResolver($container);

        $value = $resolver->resolve($definition);

        $this->assertEquals('bar', $value);
    }

    /**
     * @test
     */
    public function it_should_resolve_multiple_references()
    {
        $container = EasyMock::mock('Interop\Container\ContainerInterface');
        $container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(array('tmp'), array('logs'))
            ->willReturnOnConsecutiveCalls('/private/tmp', 'myapp-logs');

        $definition = new StringDefinition('foo', '{tmp}/{logs}/app.log');
        $resolver = new StringDefinitionResolver($container);

        $value = $resolver->resolve($definition);

        $this->assertEquals('/private/tmp/myapp-logs/app.log', $value);
    }

    /**
     * @test
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while parsing string expression for entry foo: No entry or class found for 'test'
     */
    public function it_should_throw_on_unknown_entry_name()
    {
        $container = EasyMock::mock('Interop\Container\ContainerInterface', array(
            'get' => new NotFoundException("No entry or class found for 'test'"),
        ));

        $definition = new StringDefinition('foo', '{test}');
        $resolver = new StringDefinitionResolver($container);

        $resolver->resolve($definition);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with StringDefinition objects, DI\Definition\FactoryDefinition given
     */
    public function it_should_error_with_unsupported_definitions()
    {
        $container = EasyMock::mock('Interop\Container\ContainerInterface');
        $definition = new FactoryDefinition('foo', function () {
        });
        $resolver = new StringDefinitionResolver($container);

        $resolver->resolve($definition);
    }
}
