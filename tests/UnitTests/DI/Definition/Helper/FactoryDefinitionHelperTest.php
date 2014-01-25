<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Helper;

use DI\Definition\FactoryDefinition;
use DI\Definition\Helper\FactoryDefinitionHelper;

/**
 * @covers \DI\Definition\Helper\FactoryDefinitionHelper
 */
class FactoryDefinitionHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDefinition()
    {
        $callable = function () {
        };
        $helper = new FactoryDefinitionHelper($callable);
        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof FactoryDefinition);
        $this->assertSame('foo', $definition->getName());
        $this->assertSame($callable, $definition->getCallable());
    }
}
