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
use DI\Definition\ClassDefinition\PropertyInjection;
use DI\Definition\Source\ArrayDefinitionSource;
use DI\Definition\ValueDefinition;

/**
 * Test class for ArrayDefinitionSource
 *
 * @covers \DI\Definition\Source\ArrayDefinitionSource
 */
class ArrayDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{
    public function testEntryNotFound()
    {
        $source = new ArrayDefinitionSource();
        $this->assertNull($source->getDefinition('foo'));
    }

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
        $source = new ArrayDefinitionSource();

        $source2 = new ArrayDefinitionSource();
        $source2->addDefinitions(array(
            'foo' => 'bar',
        ));

        $source->chain($source2);

        $this->assertEquals(new ValueDefinition('foo', 'bar'), $source->getDefinition('foo'));
    }

    public function testAddDefinition()
    {
        $source = new ArrayDefinitionSource();
        $definition = new ValueDefinition('foo', 'bar');

        $source->addDefinition($definition);
        $this->assertSame($definition, $source->getDefinition('foo'));
    }

    public function testAddDefinitions()
    {
        $source = new ArrayDefinitionSource();
        $definition = new ValueDefinition('foo', 'bar');

        $source->addDefinitions(array('foo' => $definition));
        $this->assertSame($definition, $source->getDefinition('foo'));
    }

    public function testAddDefinitionsOverrideExisting()
    {
        $source = new ArrayDefinitionSource();
        $definition1 = new ValueDefinition('foo', 'bar');
        $definition2 = new ValueDefinition('foo', 'bar');

        $source->addDefinitions(array('foo' => $definition1));
        $source->addDefinitions(array('foo' => $definition2));

        $this->assertSame($definition2, $source->getDefinition('foo'));
    }

    public function testUseChainedSource()
    {
        $chainedSource = new ArrayDefinitionSource();
        $definition = new ValueDefinition('foo', 'bar');
        $chainedSource->addDefinition($definition);

        $source = new ArrayDefinitionSource();
        $source->chain($chainedSource);

        $this->assertSame($definition, $source->getDefinition('foo'));
    }

    /**
     * Tests that if the source is chained to another, then mergeable definitions are merged
     */
    public function testChainedSourceMergeableDefinitions()
    {
        $source1 = new ArrayDefinitionSource();
        $definition1 = new ClassDefinition('foo');
        $definition1->addPropertyInjection(new PropertyInjection('p1', 'val1'));
        $source1->addDefinition($definition1);

        $source2 = new ArrayDefinitionSource();
        $definition2 = new ClassDefinition('foo');
        $definition2->addPropertyInjection(new PropertyInjection('p2', 'val2'));
        $source2->addDefinition($definition2);

        $source1->chain($source2);

        /** @var ClassDefinition $mergedDefinition */
        $mergedDefinition = $source1->getDefinition('foo');

        // Check that it's a different, merged, definition
        $this->assertNotSame($definition1, $mergedDefinition);
        $this->assertNotSame($definition2, $mergedDefinition);
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $mergedDefinition);
        $this->assertEquals(new PropertyInjection('p1', 'val1'), $mergedDefinition->getPropertyInjection('p1'));
        $this->assertEquals(new PropertyInjection('p2', 'val2'), $mergedDefinition->getPropertyInjection('p2'));
    }
}
