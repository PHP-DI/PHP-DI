<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source;

use Closure;
use DI\Definition\ClosureDefinition;
use DI\Definition\Source\ArrayDefinitionSource;
use DI\Definition\ClassDefinition;
use DI\Definition\ValueDefinition;
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
        $this->assertInstanceOf(ValueDefinition::class, $definition);
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
            'array'   => ['a', 'b', 'c'],
            'assoc'   => ['a' => 'b'],
            'closure' => function() {},
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
        $this->assertEquals(['a', 'b', 'c'], $definition->getValue());
        $this->assertInternalType('array', $definition->getValue());

        $definition = $source->getDefinition('assoc');
        $this->assertNotNull($definition);
        $this->assertEquals(['a' => 'b'], $definition->getValue());
        $this->assertInternalType('array', $definition->getValue());

        $definition = $source->getDefinition('closure');
        $this->assertNotNull($definition);
        $this->assertInstanceOf(Closure::class, $definition->getValue());
    }

    public function testClassDefinition()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions([
            'foo' => Entry::object(),
        ]);
        /** @var $definition ClassDefinition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf(ClassDefinition::class, $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getClassName());
    }

    public function testClosureDefinition()
    {
        $source = new ArrayDefinitionSource();
        $source->addDefinitions([
            'foo' => Entry::factory(function() {
                return 'bar';
            }),
        ]);
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf(ClosureDefinition::class, $definition);
        $this->assertEquals('foo', $definition->getName());

        $container = $this->getMockBuilder('DI\Container')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals('bar', $definition->getValue($container));
    }
}
