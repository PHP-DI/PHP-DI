<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ArrayDefinitionExtension;
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

        $this->assertEquals(Scope::SINGLETON(), $definition->getScope());
    }

    /**
     * @test
     */
    public function should_not_be_cacheable()
    {
        $definition = new ArrayDefinitionExtension('foo', array());

        $this->assertNotInstanceOf('DI\Definition\CacheableDefinition', $definition);
    }
}
