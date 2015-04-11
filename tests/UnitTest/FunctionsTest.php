<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest;

use DI\Definition\ArrayDefinition;
use DI\Definition\ArrayDefinitionExtension;
use DI\Definition\ClassDefinition;
use DI\Definition\Helper\ArrayDefinitionExtensionHelper;
use DI\Definition\Helper\ClassDefinitionHelper;
use DI\Definition\Helper\EnvironmentVariableDefinitionHelper;

/**
 * Tests the helper functions.
 */
class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::\DI\value
     */
    public function test_value()
    {
        $definition = \DI\value('foo');

        $this->assertInstanceOf('DI\Definition\Helper\ValueDefinitionHelper', $definition);
        $this->assertEquals('foo', $definition->getDefinition('entry')->getValue());
    }

    /**
     * @covers ::\DI\object
     */
    public function test_object()
    {
        $helper = \DI\object();

        $this->assertTrue($helper instanceof ClassDefinitionHelper);
        $definition = $helper->getDefinition('entry');
        $this->assertTrue($definition instanceof ClassDefinition);
        $this->assertEquals('entry', $definition->getClassName());

        $helper = \DI\object('foo');

        $this->assertTrue($helper instanceof ClassDefinitionHelper);
        $definition = $helper->getDefinition('entry');
        $this->assertTrue($definition instanceof ClassDefinition);
        $this->assertEquals('foo', $definition->getClassName());
    }

    /**
     * @covers ::\DI\factory
     */
    public function test_factory()
    {
        $definition = \DI\factory(function () {
            return 42;
        });

        $this->assertInstanceOf('DI\Definition\Helper\FactoryDefinitionHelper', $definition);
        $callable = $definition->getDefinition('entry')->getCallable();
        $this->assertEquals(42, $callable());
    }

    /**
     * @covers ::\DI\get
     */
    public function test_get()
    {
        $reference = \DI\get('foo');

        $this->assertInstanceOf('DI\Definition\EntryReference', $reference);
        $this->assertEquals('foo', $reference->getName());
    }

    /**
     * @covers ::\DI\link
     */
    public function test_link()
    {
        $reference = \DI\link('foo');

        $this->assertInstanceOf('DI\Definition\EntryReference', $reference);
        $this->assertEquals('foo', $reference->getName());
    }

    /**
     * @covers ::\DI\env
     */
    public function test_env()
    {
        $definition = \DI\env('foo');

        $this->assertTrue($definition instanceof EnvironmentVariableDefinitionHelper);
        $definition = $definition->getDefinition('entry');
        $this->assertEquals('foo', $definition->getVariableName());
        $this->assertFalse($definition->isOptional());
    }

    /**
     * @covers ::\DI\env
     */
    public function test_env_default_value()
    {
        $definition = \DI\env('foo', 'default');

        $this->assertTrue($definition instanceof EnvironmentVariableDefinitionHelper);
        $definition = $definition->getDefinition('entry');
        $this->assertEquals('foo', $definition->getVariableName());
        $this->assertTrue($definition->isOptional());
        $this->assertEquals('default', $definition->getDefaultValue());
    }

    /**
     * @covers ::\DI\env
     */
    public function test_env_default_value_null()
    {
        $definition = \DI\env('foo', null);

        $this->assertTrue($definition instanceof EnvironmentVariableDefinitionHelper);
        $definition = $definition->getDefinition('entry');
        $this->assertEquals('foo', $definition->getVariableName());
        $this->assertTrue($definition->isOptional());
        $this->assertSame(null, $definition->getDefaultValue());
    }

    /**
     * @covers ::\DI\add
     */
    public function test_add_value()
    {
        $helper = \DI\add('hello');

        $this->assertTrue($helper instanceof ArrayDefinitionExtensionHelper);

        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof ArrayDefinitionExtension);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getSubDefinitionName());
        $definition->setSubDefinition(new ArrayDefinition('foo', array()));
        $this->assertEquals(array('hello'), $definition->getValues());
    }

    /**
     * @covers ::\DI\add
     */
    public function test_add_array()
    {
        $helper = \DI\add(array('hello', 'world'));

        $this->assertTrue($helper instanceof ArrayDefinitionExtensionHelper);

        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof ArrayDefinitionExtension);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getSubDefinitionName());
        $definition->setSubDefinition(new ArrayDefinition('foo', array()));
        $this->assertEquals(array('hello', 'world'), $definition->getValues());
    }

    /**
     * @covers ::\DI\string
     */
    public function test_string()
    {
        $helper = \DI\string('bar');

        $this->assertInstanceOf('DI\Definition\Helper\StringDefinitionHelper', $helper);

        $definition = $helper->getDefinition('foo');

        $this->assertInstanceOf('DI\Definition\StringDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getExpression());
    }
}
