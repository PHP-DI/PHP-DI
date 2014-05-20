<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\ClassDefinition;

use DI\Definition\ClassDefinition\MethodInjection;

/**
 * Test class for MethodInjection
 *
 * @covers \DI\Definition\ClassDefinition\MethodInjection
 */
class MethodInjectionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $definition = new MethodInjection('class', 'foo', array());

        $this->assertEquals('foo', $definition->getMethodName());
        $this->assertEmpty($definition->getParameters());
    }

    public function testGetParameter()
    {
        $definition = new MethodInjection('class', 'foo', array('bar'));

        $this->assertEquals('bar', $definition->getParameter(0));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage There is no parameter value for index 0
     */
    public function testGetUndefinedParameter()
    {
        $definition = new MethodInjection('class', 'foo', array());
        $definition->getParameter(0);
    }

    public function testReplaceParameters()
    {
        $definition = new MethodInjection('class', 'foo', array('bar'));
        $definition->replaceParameters(array('bim'));

        $this->assertEquals(array('bim'), $definition->getParameters());
    }

    public function testMergeParameters()
    {
        $definition1 = new MethodInjection('class', 'foo', array(
            0 => 'a',
            1 => 'b',
        ));
        $definition2 = new MethodInjection('class', 'foo', array(
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
        $definition1 = new MethodInjection('class', 'foo', array(
            0 => null,
        ));
        $definition2 = new MethodInjection('class', 'foo', array(
            0 => 'bar',
        ));

        $definition1->merge($definition2);

        $this->assertEquals(array(null), $definition1->getParameters());
    }
}
