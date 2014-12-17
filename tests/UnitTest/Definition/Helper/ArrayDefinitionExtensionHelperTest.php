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
use DI\Definition\Helper\ArrayDefinitionExtensionHelper;

/**
 * @covers \DI\Definition\Helper\ArrayDefinitionExtensionHelper
 */
class ArrayDefinitionExtensionHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function test_array_extension()
    {
        $helper = new ArrayDefinitionExtensionHelper(array(
            'hello',
        ));

        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof ArrayDefinitionExtension);
        $this->assertSame('foo', $definition->getName());
        $this->assertSame('foo', $definition->getExtendedDefinitionName());
        $this->assertEquals(array('hello'), $definition->getValues());
    }
}
