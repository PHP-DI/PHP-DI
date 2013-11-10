<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\DefinitionHelper;

use DI\Definition\ClosureDefinition;
use DI\DefinitionHelper\CallableDefinitionHelper;

class CallableDefinitionHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDefinition()
    {
        $callable = function() {};
        $helper = new CallableDefinitionHelper($callable);
        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof ClosureDefinition);
        $this->assertSame('foo', $definition->getName());
        $this->assertSame($callable, $definition->getClosure());
    }
}
