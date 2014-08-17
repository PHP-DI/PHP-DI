<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\EnvironmentVariableDefinition;
use DI\Scope;

/**
 * Test class for EnvironmentVariableDefinition
 *
 * @covers \DI\Definition\EnvironmentVariableDefinition
 */
class EnvironmentVariableDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $definition = new EnvironmentVariableDefinition('foo', 'bar', false, 'default');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getVariableName());
        $this->assertFalse($definition->isOptional());
        $this->assertEquals('default', $definition->getDefaultValue());
    }

    public function testScope()
    {
        $definition = new EnvironmentVariableDefinition('foo', 'bar');

        $this->assertEquals(Scope::SINGLETON(), $definition->getScope());
    }

    public function testCacheable()
    {
        $this->assertInstanceOf('DI\Definition\CacheableDefinition', new EnvironmentVariableDefinition('foo', 'bar'));
    }
}
