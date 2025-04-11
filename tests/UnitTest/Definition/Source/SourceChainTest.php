<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\Definition;
use DI\Definition\Source\DefinitionArray;
use DI\Definition\Source\SourceChain;
use DI\Definition\ValueDefinition;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Definition\Source\SourceChain
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\DI\Definition\Source\SourceChain::class)]
class SourceChainTest extends TestCase
{
    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
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
    #[\PHPUnit\Framework\Attributes\Test]
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
    #[\PHPUnit\Framework\Attributes\Test]
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
    #[\PHPUnit\Framework\Attributes\Test]
    public function adding_definitions_should_go_in_the_mutable_definition_source()
    {
        $chain = new SourceChain([]);
        $mutableSource = new DefinitionArray();
        $chain->setMutableDefinitionSource($mutableSource);

        $definition = new ValueDefinition('bar');
        $definition->setName('foo');
        $chain->addDefinition($definition);

        $this->assertValueDefinition($chain->getDefinition('foo'), 'bar');
        $this->assertSame($mutableSource->getDefinition('foo'), $chain->getDefinition('foo'));
    }

    private function assertValueDefinition(Definition $definition, $value)
    {
        $this->assertInstanceOf(ValueDefinition::class, $definition);
        /** @var ValueDefinition $definition */
        $this->assertEquals($value, $definition->getValue());
    }
}
