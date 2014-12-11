<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Helper;

use DI\Definition\ArrayDefinitionExtension;
use DI\Definition\Helper\DefinitionExtensionHelper;

/**
 * @covers \DI\Definition\Helper\DefinitionExtensionHelper
 */
class DefinitionExtensionHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function test_array_extension_without_entry_name()
    {
        $helper = new DefinitionExtensionHelper();
        $helper->add(array(
            'hello',
        ));

        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof ArrayDefinitionExtension);
        $this->assertSame('foo', $definition->getName());
        $this->assertSame('foo', $definition->getExtendedDefinitionName());
        $this->assertEquals(array('hello'), $definition->getValues());
    }

    /**
     * @test
     */
    public function test_array_extension_with_entry_name()
    {
        $helper = new DefinitionExtensionHelper('bar');
        $helper->add(array(
            'hello',
        ));

        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof ArrayDefinitionExtension);
        $this->assertSame('foo', $definition->getName());
        $this->assertSame('bar', $definition->getExtendedDefinitionName());
        $this->assertEquals(array('hello'), $definition->getValues());
    }

    /**
     * @test
     */
    public function add_should_be_fluent()
    {
        $helper = new DefinitionExtensionHelper();

        $this->assertSame($helper, $helper->add(array()));
    }
}
