<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source;

use DI\Definition\CallableDefinition;
use DI\Definition\ClassDefinition;
use DI\Definition\Source\ArrayDefinitionSource;

/**
 * Test class for ArrayDefinitionSource
 *
 * @covers \DI\Definition\Source\ArrayDefinitionSource
 */
class ArrayDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{
    public function testValueDefinition()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(
            array(
                'foo' => 'bar',
            )
        );
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\ValueDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getValue());
    }

    public function testValueTypes()
    {
        $source = new ArrayDefinitionSource();
        $definitions = array(
            'integer' => 1,
            'string'  => 'test',
            'float'   => 1.0,
            'array'   => array('a', 'b', 'c'),
            'assoc'   => array('a' => 'b'),
            'closure' => function () {
            },
        );
        $source->addDefinitions($definitions);

        $definition = $source->getDefinition('integer');
        $this->assertNotNull($definition);
        $this->assertEquals(1, $definition->getValue());
        $this->assertInternalType('integer', $definition->getValue());

        $definition = $source->getDefinition('string');
        $this->assertNotNull($definition);
        $this->assertEquals('test', $definition->getValue());
        $this->assertInternalType('string', $definition->getValue());

        $definition = $source->getDefinition('float');
        $this->assertNotNull($definition);
        $this->assertEquals(1.0, $definition->getValue());
        $this->assertInternalType('float', $definition->getValue());

        $definition = $source->getDefinition('array');
        $this->assertNotNull($definition);
        $this->assertEquals(array('a', 'b', 'c'), $definition->getValue());
        $this->assertInternalType('array', $definition->getValue());

        $definition = $source->getDefinition('assoc');
        $this->assertNotNull($definition);
        $this->assertEquals(array('a' => 'b'), $definition->getValue());
        $this->assertInternalType('array', $definition->getValue());

        $definition = $source->getDefinition('closure');
        $this->assertNotNull($definition);
        $this->assertInstanceOf('Closure', $definition->getValue());
    }

    public function testClassDefinition()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(array(
            'foo' => \DI\object(),
        ));
        /** @var $definition ClassDefinition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getClassName());
    }

    public function testClosureDefinition()
    {
        $callable = function () {
            return 'bar';
        };
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(array(
            'foo' => \DI\factory($callable),
        ));
        /** @var CallableDefinition $definition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\CallableDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
    }

    public function testChainableSource()
    {
        $source = new ArrayDefinitionSource(__DIR__ . '/Fixtures/definitions.php');

        $otherSource = $this->getMockForAbstractClass('DI\Definition\Source\DefinitionSource');
        $otherSource->expects($this->once())
            ->method('getDefinition')
            ->with('some unknown entry')
            ->will($this->returnValue(42));

        $source->chain($otherSource);

        $this->assertEquals(42, $source->getDefinition('some unknown entry'));
    }
}
