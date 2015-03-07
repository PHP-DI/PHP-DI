<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\ClassDefinition;

use DI\Definition\ClassDefinition\PropertyInjection;

/**
 * @covers \DI\Definition\ClassDefinition\PropertyInjection
 */
class PropertyInjectionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $definition = new PropertyInjection('foo', 'bar');

        $this->assertEquals('foo', $definition->getPropertyName());
        $this->assertEquals('bar', $definition->getValue());
    }
}
