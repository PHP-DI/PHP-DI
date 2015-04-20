<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition;

use DI\Definition\AbstractFunctionCallDefinition;
use DI\Definition\ObjectDefinition;
use DI\Scope;

/**
 * @covers \DI\Definition\AbstractFunctionCallDefinition
 */
class AbstractFunctionCallDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicMethods()
    {
        /** @var AbstractFunctionCallDefinition $definition */
        $definition = $this->getMockForAbstractClass('DI\Definition\AbstractFunctionCallDefinition');

        $this->assertNull($definition->getName());
        $this->assertEquals(Scope::PROTOTYPE, $definition->getScope());
        $this->assertEmpty($definition->getParameters());
    }

    public function testNotCacheable()
    {
        /** @var AbstractFunctionCallDefinition $definition */
        $definition = $this->getMockForAbstractClass('DI\Definition\AbstractFunctionCallDefinition');

        $this->assertNotInstanceOf('DI\Definition\CacheableDefinition', $definition);
    }

    public function testEmptyParameters()
    {
        /** @var AbstractFunctionCallDefinition $definition */
        $definition = $this->getMockForAbstractClass('DI\Definition\AbstractFunctionCallDefinition');

        $this->assertEmpty($definition->getParameters());
    }

    public function testGetParameter()
    {
        /** @var AbstractFunctionCallDefinition $definition */
        $definition = $this->getMockForAbstractClass('DI\Definition\AbstractFunctionCallDefinition');
        $definition->replaceParameters(array('bar'));

        $this->assertEquals('bar', $definition->getParameter(0));
    }

    public function testHasParameter()
    {
        /** @var AbstractFunctionCallDefinition $definition */
        $definition = $this->getMockForAbstractClass('DI\Definition\AbstractFunctionCallDefinition');
        $definition->replaceParameters(array('bar'));

        $this->assertTrue($definition->hasParameter(0));
        $this->assertFalse($definition->hasParameter(1));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage There is no parameter value for index 0
     */
    public function testGetUndefinedParameter()
    {
        /** @var AbstractFunctionCallDefinition $definition */
        $definition = $this->getMockForAbstractClass('DI\Definition\AbstractFunctionCallDefinition');
        $definition->getParameter(0);
    }

    public function testReplaceParameters()
    {
        /** @var AbstractFunctionCallDefinition $definition */
        $definition = $this->getMockForAbstractClass('DI\Definition\AbstractFunctionCallDefinition');
        $definition->replaceParameters(array('bar'));
        $definition->replaceParameters(array('bim'));

        $this->assertEquals(array('bim'), $definition->getParameters());
    }
}
