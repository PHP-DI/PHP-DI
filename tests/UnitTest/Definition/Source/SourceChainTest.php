<?php

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\ObjectDefinition;
use DI\Definition\Definition;
use DI\Definition\Source\DefinitionArray;
use DI\Definition\Source\Autowiring;
use DI\Definition\Source\SourceChain;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Source\SourceChain
 */
class SourceChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_get_from_all_sources()
    {
        $chain = new SourceChain([
            new DefinitionArray([
                'test1' => 'test1',
            ]),
            new DefinitionArray([
                'test2' => 'test2',
            ]),
            new DefinitionArray([
                'test3' => 'test3',
            ]),
        ]);
        $this->assertValueDefinition($chain->getDefinition('test1'), 'test1');
        $this->assertValueDefinition($chain->getDefinition('test2'), 'test2');
        $this->assertValueDefinition($chain->getDefinition('test3'), 'test3');
    }

    /**
     * @test
     */
    public function should_stop_when_definition_found()
    {
        $chain = new SourceChain([
            new DefinitionArray([
                'foo' => 'bar',
            ]),
            new DefinitionArray([
                'foo' => 'bim',
            ]),
        ]);
        $this->assertValueDefinition($chain->getDefinition('foo'), 'bar');
    }

    /**
     * @test
     */
    public function setting_the_mutable_definition_source_should_chain_it_at_the_top()
    {
        $chain = new SourceChain([
            new DefinitionArray([
                'foo' => 'bar',
            ]),
        ]);

        $chain->setMutableDefinitionSource(new DefinitionArray([
            'foo' => 'bim',
        ]));
        $this->assertValueDefinition($chain->getDefinition('foo'), 'bim');
    }

    /**
     * @test
     */
    public function adding_definitions_should_go_in_the_mutable_definition_source()
    {
        $chain = new SourceChain([]);
        $mutableSource = new DefinitionArray();
        $chain->setMutableDefinitionSource($mutableSource);

        $chain->addDefinition(new ValueDefinition('foo', 'bar'));

        $this->assertValueDefinition($chain->getDefinition('foo'), 'bar');
        $this->assertSame($mutableSource->getDefinition('foo'), $chain->getDefinition('foo'));
    }

    /**
     * @test
     */
    public function search_sub_definitions_with_different_name_from_root()
    {
        $chain = new SourceChain([
            new DefinitionArray([
                'subdef' => \DI\object('stdClass')
                    ->lazy(),
            ]),
            new DefinitionArray([
                'def' => \DI\object('subdef'),
            ]),
            new Autowiring(),
        ]);

        /** @var ObjectDefinition $definition */
        $definition = $chain->getDefinition('def');
        $this->assertTrue($definition instanceof ObjectDefinition);
        $this->assertEquals('def', $definition->getName());
        $this->assertEquals('subdef', $definition->getClassName());
        $this->assertTrue($definition->isLazy());

        // Define a new root source: should be used
        $chain->setRootDefinitionSource(new DefinitionArray([
            'subdef' => \DI\object('stdClass'), // this one is not lazy
        ]));
        $definition = $chain->getDefinition('def');
        $this->assertFalse($definition->isLazy()); // shouldn't be lazy
    }

    /**
     * @test
     */
    public function search_sub_definitions_with_same_name_from_next_source()
    {
        $chain = new SourceChain([
            new DefinitionArray([
                'def' => \DI\object(),
            ]),
            new DefinitionArray([
                'def' => \DI\object('stdClass') // Should use this definition
                    ->lazy(),
            ]),
            new DefinitionArray([
                'def' => \DI\object('DateTime'), // Should NOT use this one
            ]),
            new Autowiring(),
        ]);

        /** @var ObjectDefinition $definition */
        $definition = $chain->getDefinition('def');
        $this->assertTrue($definition instanceof ObjectDefinition);
        $this->assertEquals('def', $definition->getName());
        $this->assertEquals('stdClass', $definition->getClassName());
        $this->assertTrue($definition->isLazy());
    }

    private function assertValueDefinition(Definition $definition, $value)
    {
        $this->assertTrue($definition instanceof ValueDefinition);
        /** @var ValueDefinition $definition */
        $this->assertEquals($value, $definition->getValue());
    }
}
