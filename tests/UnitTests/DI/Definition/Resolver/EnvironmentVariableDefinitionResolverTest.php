<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Resolver;

use DI\Definition\EntryReference;
use DI\Definition\FactoryDefinition;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Resolver\EnvironmentVariableDefinitionResolver;

/**
 * @covers \DI\Definition\Resolver\EnvironmentVariableDefinitionResolver
 */
class EnvironmentVariableDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $variableReader;
    private $resolver;
    private $definedDefinition;
    private $undefinedDefinition;
    private $optionalDefinition;
    private $linkedDefinition;
    private $invalidDefinition;

    public function setUp()
    {
        $this->container = $this->getMock('Interop\Container\ContainerInterface');

        $this->variableReader = function ($variableName) {
            if ('DEFINED' === $variableName) {
                return '<value>';
            }

            return false;
        };

        $this->resolver = new EnvironmentVariableDefinitionResolver($this->container, $this->variableReader);
        $this->definedDefinition = new EnvironmentVariableDefinition('foo', 'DEFINED');
        $this->undefinedDefinition = new EnvironmentVariableDefinition('foo', 'UNDEFINED');
        $this->optionalDefinition = new EnvironmentVariableDefinition('foo', 'UNDEFINED', true, '<default>');
        $this->linkedDefinition = new EnvironmentVariableDefinition('foo', 'UNDEFINED', true, new EntryReference('foo'));
        $this->invalidDefinition = new FactoryDefinition('foo', function () {});
    }

    public function testResolve()
    {
        $value = $this->resolver->resolve($this->definedDefinition);

        $this->assertEquals('<value>', $value);
    }

    public function testResolveDefault()
    {
        $value = $this->resolver->resolve($this->optionalDefinition);

        $this->assertEquals('<default>', $value);
    }

    public function testResolveWithLinkedDefault()
    {
        $this->container->expects($this->once())
            ->method('get')
            ->with('foo')
            ->will($this->returnValue('bar'));

        $value = $this->resolver->resolve($this->linkedDefinition);

        $this->assertEquals('bar', $value);
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The environment variable 'UNDEFINED' has not been defined
     */
    public function testResolveFailure()
    {
        $this->resolver->resolve($this->undefinedDefinition);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with EnvironmentVariableDefinition objects, DI\Definition\FactoryDefinition given
     */
    public function testResolveFailureWithInvalidDefinitionType()
    {
        $this->resolver->resolve($this->invalidDefinition);
    }

    public function testIsResolvable()
    {
        $this->assertTrue(
            $this->resolver->isResolvable($this->definedDefinition)
        );
    }

    public function testIsResolvableDefault()
    {
        $this->assertTrue(
            $this->resolver->isResolvable($this->optionalDefinition)
        );
    }

    public function testIsResolvableFailure()
    {
        $this->assertFalse(
            $this->resolver->isResolvable($this->undefinedDefinition)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with EnvironmentVariableDefinition objects, DI\Definition\FactoryDefinition given
     */
    public function testIsResolvableFailureWithInvalidDefinitionType()
    {
        $this->resolver->isResolvable($this->invalidDefinition);
    }
}
