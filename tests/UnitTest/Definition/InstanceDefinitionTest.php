<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ClassDefinition;
use DI\Definition\InstanceDefinition;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\InstanceDefinition
 */
class InstanceDefinitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_contain_an_instance()
    {
        $instance = new \stdClass();

        $definition = new InstanceDefinition($instance, EasyMock::mock('DI\Definition\ClassDefinition'));

        $this->assertSame($instance, $definition->getInstance());
    }

    /**
     * @test
     */
    public function should_contain_a_class_definition()
    {
        $classDefinition = EasyMock::mock('DI\Definition\ClassDefinition');

        $definition = new InstanceDefinition(new \stdClass(), $classDefinition);

        $this->assertSame($classDefinition, $definition->getClassDefinition());
    }
}
