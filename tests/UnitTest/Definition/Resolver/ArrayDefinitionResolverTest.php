<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\ArrayDefinition;
use DI\Definition\Resolver\ArrayDefinitionResolver;
use DI\Definition\Resolver\DefinitionResolver;
use DI\Definition\ValueDefinition;
use EasyMock\EasyMock;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \DI\Definition\Resolver\ArrayDefinitionResolver
 */
class ArrayDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefinitionResolver|PHPUnit_Framework_MockObject_MockObject
     */
    private $parentResolver;

    /**
     * @var ArrayDefinitionResolver
     */
    private $resolver;

    public function setUp()
    {
        $this->parentResolver = EasyMock::mock('DI\Definition\Resolver\DefinitionResolver');
        $this->resolver = new ArrayDefinitionResolver($this->parentResolver);
    }

    /**
     * @test
     */
    public function should_resolve_array_of_values()
    {
        $definition = new ArrayDefinition('foo', array(
            'bar',
            42,
        ));

        $value = $this->resolver->resolve($definition);

        $this->assertEquals(array('bar', 42), $value);
    }

    /**
     * @test
     */
    public function should_resolve_nested_definitions()
    {
        $this->parentResolver->expects($this->exactly(2))
            ->method('resolve')
            ->withConsecutive(
                $this->isInstanceOf('DI\Definition\AliasDefinition'),
                $this->isInstanceOf('DI\Definition\ObjectDefinition')
            )
            ->willReturnOnConsecutiveCalls(42, new \stdClass());

        $definition = new ArrayDefinition('foo', array(
            'bar',
            \DI\get('bar'),
            \DI\object('bar'),
        ));

        $value = $this->resolver->resolve($definition);

        $this->assertEquals(array('bar', 42, new \stdClass()), $value);
    }

    /**
     * @test
     */
    public function resolve_should_preserve_keys()
    {
        $definition = new ArrayDefinition('foo', array(
            'hello' => 'world',
        ));

        $value = $this->resolver->resolve($definition);

        $this->assertEquals(array('hello' => 'world'), $value);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with ArrayDefinition objects, DI\Definition\ValueDefinition given
     */
    public function should_only_resolve_array_definitions()
    {
        $this->resolver->resolve(new ValueDefinition('foo', 'bar'));
    }

    /**
     * @test
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while resolving foo[0]. This is a message
     */
    public function should_throw_with_a_nice_message()
    {
        $this->parentResolver->expects($this->once())
            ->method('resolve')
            ->willThrowException(new \Exception('This is a message'));

        $this->resolver->resolve(new ArrayDefinition('foo', array(
            \DI\get('bar'),
        )));
    }
}
