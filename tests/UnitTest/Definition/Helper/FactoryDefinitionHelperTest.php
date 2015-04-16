<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Helper;

use DI\Definition\DecoratorDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\Helper\FactoryDefinitionHelper;
use DI\Scope;

/**
 * @covers \DI\Definition\Helper\FactoryDefinitionHelper
 */
class FactoryDefinitionHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function creates_factory_definition()
    {
        $callable = function () {
        };
        $helper = new FactoryDefinitionHelper($callable);
        $helper->scope(Scope::PROTOTYPE());
        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof FactoryDefinition);
        $this->assertSame('foo', $definition->getName());
        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
        $this->assertSame($callable, $definition->getCallable());
    }

    /**
     * @test
     */
    public function creates_decorator_definition()
    {
        $callable = function () {
        };
        $helper = new FactoryDefinitionHelper($callable, true);
        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof DecoratorDefinition);
        $this->assertSame('foo', $definition->getName());
        $this->assertSame($callable, $definition->getCallable());
    }

    /**
     * @test
     */
    public function is_singleton_by_default()
    {
        $callable = function () {
        };
        $helper = new FactoryDefinitionHelper($callable);
        $definition = $helper->getDefinition('foo');

        $this->assertEquals(Scope::SINGLETON(), $definition->getScope());
    }
}
