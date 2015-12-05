<?php

namespace DI\Test\UnitTest\Definition\Source;

use Assembly\ArrayDefinitionProvider;
use Assembly\ParameterDefinition;
use DI\Definition\Source\InteropDefinitionProvider;

class InteropDefinitionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function returns_definitions_from_provider()
    {
        $definition = new ParameterDefinition('bar');
        $definitionProvider = new ArrayDefinitionProvider([
            'foo' => $definition,
        ]);

        $source = new InteropDefinitionProvider($definitionProvider);

        $this->assertSame($definition, $source->getDefinition('foo'));
    }

    /**
     * @test
     */
    public function returns_null_for_unknown_definition()
    {
        $source = new InteropDefinitionProvider(new ArrayDefinitionProvider());

        $this->assertNull($source->getDefinition('foo'));
    }
}
