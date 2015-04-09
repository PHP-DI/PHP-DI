<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\ArrayDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\ClassDefinition;
use DI\Definition\Source\ArrayDefinitionSource;
use DI\Definition\ValueDefinition;

/**
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
        $source = new ArrayDefinitionSource(array(
            'foo' => 'bar',
        ));

        /** @var ValueDefinition $definition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\ValueDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getValue());
    }

    public function testValueTypes()
    {
        $definitions = array(
            'integer' => 1,
            'string'  => 'test',
            'float'   => 1.0,
        );
        $source = new ArrayDefinitionSource($definitions);

        /** @var ValueDefinition $definition */
        $definition = $source->getDefinition('integer');
        $this->assertTrue($definition instanceof ValueDefinition);
        $this->assertEquals(1, $definition->getValue());
        $this->assertInternalType('integer', $definition->getValue());

        $definition = $source->getDefinition('string');
        $this->assertTrue($definition instanceof ValueDefinition);
        $this->assertEquals('test', $definition->getValue());
        $this->assertInternalType('string', $definition->getValue());

        $definition = $source->getDefinition('float');
        $this->assertTrue($definition instanceof ValueDefinition);
        $this->assertEquals(1.0, $definition->getValue());
        $this->assertInternalType('float', $definition->getValue());
    }

    public function testArrayDefinitions()
    {
        $source = new ArrayDefinitionSource();
        $definitions = array(
            'array'   => array('a', 'b', 'c'),
            'assoc'   => array('a' => 'b'),
            'links'   => array('a' => \DI\get('b')),
        );
        $source->addDefinitions($definitions);

        /** @var ArrayDefinition $definition */
        $definition = $source->getDefinition('array');
        $this->assertTrue($definition instanceof ArrayDefinition);
        $this->assertEquals(array('a', 'b', 'c'), $definition->getValues());
        $this->assertInternalType('array', $definition->getValues());

        $definition = $source->getDefinition('assoc');
        $this->assertTrue($definition instanceof ArrayDefinition);
        $this->assertEquals(array('a' => 'b'), $definition->getValues());
        $this->assertInternalType('array', $definition->getValues());

        $definition = $source->getDefinition('links');
        $this->assertTrue($definition instanceof ArrayDefinition);
        $this->assertEquals(array('a' => \DI\get('b')), $definition->getValues());
        $this->assertInternalType('array', $definition->getValues());
    }

    public function testClassDefinition()
    {
        $source = new ArrayDefinitionSource(array(
            'foo' => \DI\object(),
        ));
        /** @var $definition ClassDefinition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getClassName());
    }

    public function testFactoryDefinition()
    {
        $callable = function () {
            return 'bar';
        };
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(array(
            'foo' => \DI\factory($callable),
        ));
        /** @var FactoryDefinition $definition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\FactoryDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
    }

    public function testClosureIsCastedToFactoryDefinition()
    {
        $callable = function () {
            return 'bar';
        };
        $source = new ArrayDefinitionSource();
        $source->addDefinitions(array(
            'foo' => $callable,
        ));
        /** @var FactoryDefinition $definition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\FactoryDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
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

    public function testAddDefinitionsInConstructor()
    {
        $definition = new ValueDefinition('foo', 'bar');

        $source = new ArrayDefinitionSource(array('foo' => $definition));
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

    public function testWildcards()
    {
        $source = new ArrayDefinitionSource(array(
            'foo*' => 'bar',
            'Namespaced\*Interface' => \DI\object('Namespaced\*'),
            'Namespaced2\*Interface' => \DI\object('Namespaced2\Foo'),
            'Multiple\*\*\Matches' => \DI\object('Multiple\*\*\Implementation')
        ));

        $definition = $source->getDefinition('foo1');
        $this->assertInstanceOf('DI\Definition\ValueDefinition', $definition);
        $this->assertEquals('foo1', $definition->getName());
        $this->assertEquals('bar', $definition->getValue());

        $definition = $source->getDefinition('Namespaced\FooInterface');
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);
        $this->assertEquals('Namespaced\FooInterface', $definition->getName());
        $this->assertEquals('Namespaced\Foo', $definition->getClassName());

        $definition = $source->getDefinition('Namespaced2\FooInterface');
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);
        $this->assertEquals('Namespaced2\FooInterface', $definition->getName());
        $this->assertEquals('Namespaced2\Foo', $definition->getClassName());

        $definition = $source->getDefinition('Multiple\Foo\Bar\Matches');
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);
        $this->assertEquals('Multiple\Foo\Bar\Matches', $definition->getName());
        $this->assertEquals('Multiple\Foo\Bar\Implementation', $definition->getClassName());
    }

    /**
     * An exact match (in the definitions array) should prevail over matching with wildcards.
     */
    public function testExactMatchShouldPrevailOverWildcard()
    {
        $source = new ArrayDefinitionSource(array(
            'fo*' => 'bar',
            'foo' => 'bim',
        ));
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\ValueDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bim', $definition->getValue());
    }

    /**
     * The wildcard should not match empty strings
     */
    public function testWildcardShouldNotMatchEmptyString()
    {
        $source = new ArrayDefinitionSource(array(
            'foo*' => 'bar',
        ));
        $this->assertNull($source->getDefinition('foo'));
    }

    /**
     * The wildcard should not match across namespaces.
     */
    public function testWildcardShouldNotMatchAcrossNamespaces()
    {
        $source = new ArrayDefinitionSource(array(
            'My\*Interface' => \DI\object('My\*'),
        ));
        $this->assertNull($source->getDefinition('My\Foo\BarInterface'));
    }
}
