<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\ParameterInjection;

/**
 * Test class for ParameterInjection
 */
class ParameterInjectionTest extends \PHPUnit_Framework_TestCase
{

    public function testDefaultValues()
    {
        $definition = new ParameterInjection('foo');

        $this->assertEquals('foo', $definition->getParameterName());
        $this->assertNull($definition->getEntryName());
        $this->assertFalse($definition->isLazy());
    }

    public function testValues()
    {
        $definition = new ParameterInjection('foo', 'bar', true);

        $this->assertEquals('foo', $definition->getParameterName());
        $this->assertEquals('bar', $definition->getEntryName());
        $this->assertTrue($definition->isLazy());
    }

    public function testMerge()
    {
        $definition1 = new ParameterInjection('param', 'bar1', false);
        $definition2 = new ParameterInjection('param', 'bar2', true);

        $definition1->merge($definition2);

        // The latter prevails
        $this->assertEquals('bar2', $definition1->getEntryName());
        $this->assertTrue($definition1->isLazy());
    }

}
