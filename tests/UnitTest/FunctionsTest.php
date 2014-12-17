<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest;

/**
 * Tests the helper functions.
 */
class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::\DI\object
     */
    public function test_object()
    {
        $definition = \DI\object();

        $this->assertInstanceOf('DI\Definition\Helper\ClassDefinitionHelper', $definition);
        $this->assertEquals('entry', $definition->getDefinition('entry')->getClassName());

        $definition = \DI\object('foo');

        $this->assertInstanceOf('DI\Definition\Helper\ClassDefinitionHelper', $definition);
        $this->assertEquals('foo', $definition->getDefinition('entry')->getClassName());
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
     * @covers ::\DI\link
     */
    public function test_link()
    {
        $reference = \DI\link('foo');

        $this->assertInstanceOf('DI\Definition\EntryReference', $reference);
        $this->assertEquals('foo', $reference->getName());
    }

    /**
     * @covers ::\DI\add
     */
    public function test_add_value()
    {
        $helper = \DI\add('hello');

        $this->assertInstanceOf('DI\Definition\Helper\ArrayDefinitionExtensionHelper', $helper);

        $definition = $helper->getDefinition('foo');

        $this->assertInstanceOf('DI\Definition\ArrayDefinitionExtension', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getExtendedDefinitionName());
        $this->assertEquals(array('hello'), $definition->getValues());
    }

    /**
     * @covers ::\DI\add
     */
    public function test_add_array()
    {
        $helper = \DI\add(array('hello', 'world'));

        $this->assertInstanceOf('DI\Definition\Helper\ArrayDefinitionExtensionHelper', $helper);

        $definition = $helper->getDefinition('foo');

        $this->assertInstanceOf('DI\Definition\ArrayDefinitionExtension', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getExtendedDefinitionName());
        $this->assertEquals(array('hello', 'world'), $definition->getValues());
    }
}
