<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ArrayDefinition;
use DI\Definition\ArrayDefinitionExtension;
use DI\Definition\ValueDefinition;
use DI\Scope;

/**
 * @covers \DI\Definition\ArrayDefinitionExtension
 */
class ArrayDefinitionExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function test_getters()
    {
        $definition = new ArrayDefinitionExtension('foo', array('hello'));

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getSubDefinitionName());
        $this->assertEquals(array('hello'), $definition->getValues());
    }

    /**
     * @test
     */
    public function scope_should_be_singleton()
    {
        $definition = new ArrayDefinitionExtension('foo', array());

        $this->assertEquals(Scope::SINGLETON, $definition->getScope());
    }

    /**
     * @test
     */
    public function should_not_be_cacheable()
    {
        $definition = new ArrayDefinitionExtension('foo', array());

        $this->assertNotInstanceOf('DI\Definition\CacheableDefinition', $definition);
    }

    /**
     * @test
     */
    public function should_append_values_after_sub_definitions_values()
    {
        $definition = new ArrayDefinitionExtension('name', array('foo'));
        $definition->setSubDefinition(new ArrayDefinition('name', array('bar')));

        $expected = array(
            'bar',
            'foo',
        );

        $this->assertEquals($expected, $definition->getValues());
    }

    /**
     * @test
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage Definition name tries to add array entries but the previous definition is not an array
     */
    public function should_error_if_not_extending_an_array()
    {
        $definition = new ArrayDefinitionExtension('name', array('foo'));
        $definition->setSubDefinition(new ValueDefinition('name', 'value'));
    }
}
