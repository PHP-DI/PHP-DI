<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\Reference;
use DI\Definition\ArrayDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\Source\DefinitionArray;
use DI\Definition\ValueDefinition;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Definition\Source\DefinitionArray
 */
class DefinitionArrayTest extends TestCase
{
    public function testEntryNotFound()
    {
        $source = new DefinitionArray();
        $this->assertNull($source->getDefinition('foo'));
    }

    public function testValueDefinition()
    {
        $source = new DefinitionArray([
            'foo' => 'bar',
        ]);

        /** @var ValueDefinition $definition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf(ValueDefinition::class, $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getValue());
    }

    public function testValueTypes()
    {
        $definitions = [
            'integer' => 1,
            'string'  => 'test',
            'float'   => 1.0,
        ];
        $source = new DefinitionArray($definitions);

        /** @var ValueDefinition $definition */
        $definition = $source->getDefinition('integer');
        $this->assertInstanceOf(ValueDefinition::class, $definition);
        $this->assertEquals(1, $definition->getValue());
        $this->assertIsInt($definition->getValue());

        $definition = $source->getDefinition('string');
        $this->assertInstanceOf(ValueDefinition::class, $definition);
        $this->assertEquals('test', $definition->getValue());
        $this->assertIsString($definition->getValue());

        $definition = $source->getDefinition('float');
        $this->assertInstanceOf(ValueDefinition::class, $definition);
        $this->assertEquals(1.0, $definition->getValue());
        $this->assertIsFloat($definition->getValue());
    }

    public function testArrayDefinitions()
    {
        $source = new DefinitionArray();
        $definitions = [
            'array'   => ['a', 'b', 'c'],
            'assoc'   => ['a' => 'b'],
            'links'   => ['a' => \DI\get('b')],
        ];
        $source->addDefinitions($definitions);

        /** @var ArrayDefinition $definition */
        $definition = $source->getDefinition('array');
        $this->assertInstanceOf(ArrayDefinition::class, $definition);
        $this->assertEquals(['a', 'b', 'c'], $definition->getValues());
        $this->assertIsArray($definition->getValues());

        $definition = $source->getDefinition('assoc');
        $this->assertInstanceOf(ArrayDefinition::class, $definition);
        $this->assertEquals(['a' => 'b'], $definition->getValues());
        $this->assertIsArray($definition->getValues());

        $definition = $source->getDefinition('links');
        $this->assertInstanceOf(ArrayDefinition::class, $definition);
        $this->assertInstanceOf(Reference::class, $definition->getValues()['a']);
        $this->assertEquals('b', $definition->getValues()['a']->getTargetEntryName());
        $this->assertIsArray($definition->getValues());
    }

    public function testObjectDefinition()
    {
        $source = new DefinitionArray([
            'foo' => \DI\create(),
        ]);
        /** @var $definition ObjectDefinition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf(ObjectDefinition::class, $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getClassName());
    }

    public function testFactoryDefinition()
    {
        $callable = function () {
            return 'bar';
        };
        $source = new DefinitionArray();
        $source->addDefinitions([
            'foo' => \DI\factory($callable),
        ]);
        /** @var FactoryDefinition $definition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf(FactoryDefinition::class, $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
    }

    public function testClosureIsCastedToFactoryDefinition()
    {
        $callable = function () {
            return 'bar';
        };
        $source = new DefinitionArray();
        $source->addDefinitions([
            'foo' => $callable,
        ]);
        /** @var FactoryDefinition $definition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf(FactoryDefinition::class, $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
    }

    public function testAddDefinition()
    {
        $source = new DefinitionArray();
        $definition = new ValueDefinition('bar');
        $definition->setName('foo');

        $source->addDefinition($definition);
        $this->assertSame($definition, $source->getDefinition('foo'));
    }

    public function testAddDefinitions()
    {
        $source = new DefinitionArray();
        $definition = new ValueDefinition('bar');

        $source->addDefinitions(['foo' => $definition]);
        $this->assertSame($definition, $source->getDefinition('foo'));
    }

    public function testAddDefinitionsInConstructor()
    {
        $definition = new ValueDefinition('bar');

        $source = new DefinitionArray(['foo' => $definition]);
        $this->assertSame($definition, $source->getDefinition('foo'));
    }

    public function testAddDefinitionsOverrideExisting()
    {
        $source = new DefinitionArray();
        $definition1 = new ValueDefinition('bar');
        $definition2 = new ValueDefinition('bar');

        $source->addDefinitions(['foo' => $definition1]);
        $source->addDefinitions(['foo' => $definition2]);

        $this->assertSame($definition2, $source->getDefinition('foo'));
    }

    public function testWildcards()
    {
        $source = new DefinitionArray([
            'foo*'                   => 'bar',
            'Namespaced\*Interface'  => \DI\create('Namespaced\*'),
            'Namespaced2\*Interface' => \DI\create('Namespaced2\Foo'),
            'Multiple\*\*\Matches'   => \DI\create('Multiple\*\*\Implementation'),
        ]);

        $definition = $source->getDefinition('foo1');
        $this->assertInstanceOf(ValueDefinition::class, $definition);
        $this->assertEquals('foo1', $definition->getName());
        $this->assertEquals('bar', $definition->getValue());

        $definition = $source->getDefinition('Namespaced\FooInterface');
        $this->assertInstanceOf(ObjectDefinition::class, $definition);
        $this->assertEquals('Namespaced\FooInterface', $definition->getName());
        $this->assertEquals('Namespaced\Foo', $definition->getClassName());

        $definition = $source->getDefinition('Namespaced2\FooInterface');
        $this->assertInstanceOf(ObjectDefinition::class, $definition);
        $this->assertEquals('Namespaced2\FooInterface', $definition->getName());
        $this->assertEquals('Namespaced2\Foo', $definition->getClassName());

        $definition = $source->getDefinition('Multiple\Foo\Bar\Matches');
        $this->assertInstanceOf(ObjectDefinition::class, $definition);
        $this->assertEquals('Multiple\Foo\Bar\Matches', $definition->getName());
        $this->assertEquals('Multiple\Foo\Bar\Implementation', $definition->getClassName());
    }

    /**
     * An exact match (in the definitions array) should prevail over matching with wildcards.
     */
    public function testExactMatchShouldPrevailOverWildcard()
    {
        $source = new DefinitionArray([
            'fo*' => 'bar',
            'foo' => 'bim',
        ]);
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf(ValueDefinition::class, $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bim', $definition->getValue());
    }

    /**
     * The wildcard should not match empty strings.
     */
    public function testWildcardShouldNotMatchEmptyString()
    {
        $source = new DefinitionArray([
            'foo*' => 'bar',
        ]);
        $this->assertNull($source->getDefinition('foo'));
    }

    /**
     * The wildcard should not match across namespaces.
     */
    public function testWildcardShouldNotMatchAcrossNamespaces()
    {
        $source = new DefinitionArray([
            'My\*Interface' => \DI\create('My\*'),
        ]);
        $this->assertNull($source->getDefinition('My\Foo\BarInterface'));
    }

    /**
     * @see https://github.com/PHP-DI/PHP-DI/issues/379
     */
    public function testWildcardStringsAreEscaped()
    {
        $source = new DefinitionArray([
            'foo.*' => 'bar',
        ]);
        $this->assertNotNull($source->getDefinition('foo.test'));
        $this->assertNull($source->getDefinition('footest'));
    }

    /**
     * @see https://github.com/PHP-DI/PHP-DI/issues/242
     */
    public function testDefinitionsWithoutKeyThrowAnError()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The PHP-DI definition is not indexed by an entry name in the definition array');
        new DefinitionArray([
            'foo' => 'bar',
            'baz', // error => this entry is not indexed by a string
        ]);
    }

    /**
     * @see https://github.com/PHP-DI/PHP-DI/issues/242
     */
    public function testDefinitionsWithoutKeyThrowAnError2()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The PHP-DI definition is not indexed by an entry name in the definition array');
        $source = new DefinitionArray;
        $source->addDefinitions([
            'foo' => 'bar',
            'baz', // error => this entry is not indexed by a string
        ]);
    }
}
