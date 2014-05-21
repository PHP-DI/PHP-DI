<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\AbstractFunctionCallDefinition;
use DI\Definition\ClassDefinition;
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
        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
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

    public function testMergeParameters()
    {
        /** @var AbstractFunctionCallDefinition $definition1 */
        $definition1 = $this->getMockForAbstractClass('DI\Definition\AbstractFunctionCallDefinition');
        $definition1->replaceParameters(array(
            0 => 'a',
            1 => 'b',
        ));
        /** @var AbstractFunctionCallDefinition $definition2 */
        $definition2 = $this->getMockForAbstractClass('DI\Definition\AbstractFunctionCallDefinition');
        $definition2->replaceParameters(array(
            1 => 'c',
            2 => 'd',
        ));

        $definition1->merge($definition2);

        $this->assertEquals(array('a', 'b', 'd'), $definition1->getParameters());
    }

    /**
     * Check that a merge will preserve "null" injections
     */
    public function testMergeParametersPreservesNull()
    {
        /** @var AbstractFunctionCallDefinition $definition1 */
        $definition1 = $this->getMockForAbstractClass('DI\Definition\AbstractFunctionCallDefinition');
        $definition1->replaceParameters(array(
            0 => null,
        ));
        /** @var AbstractFunctionCallDefinition $definition2 */
        $definition2 = $this->getMockForAbstractClass('DI\Definition\AbstractFunctionCallDefinition');
        $definition2->replaceParameters(array(
            0 => 'bar',
        ));

        $definition1->merge($definition2);

        $this->assertEquals(array(null), $definition1->getParameters());
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage DI definition conflict: trying to merge incompatible definitions
     */
    public function testMergeIncompatibleObject()
    {
        /** @var AbstractFunctionCallDefinition $definition1 */
        $definition1 = $this->getMockForAbstractClass('DI\Definition\AbstractFunctionCallDefinition');
        $definition2 = new ClassDefinition('foo');

        $definition1->merge($definition2);
    }
}
