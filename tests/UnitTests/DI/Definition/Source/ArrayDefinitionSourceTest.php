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
use DI\Entry;

/**
 * Test class for ArrayDefinitionSource
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
            'foo' => Entry::object(),
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
            'foo' => Entry::factory($callable),
        ));
        /** @var CallableDefinition $definition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\CallableDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
    }

    public function testLoadFromFile()
    {
        $source = new ArrayDefinitionSource(__DIR__ . '/Fixtures/definitions.php');

        $definition = $source->getDefinition('foo');
        $this->assertNotNull($definition);
        $this->assertEquals('bar', $definition->getValue());
        $this->assertInternalType('string', $definition->getValue());

        /** @var $definition ClassDefinition */
        $definition = $source->getDefinition('bim');
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);
        $this->assertEquals('bim', $definition->getName());
        $this->assertEquals('bim', $definition->getClassName());
    }
}
