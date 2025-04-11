<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\ObjectDefinition;
use DI\Definition\Source\DefinitionFile;
use DI\Definition\ValueDefinition;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Definition\Source\DefinitionFile
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\DI\Definition\Source\DefinitionFile::class)]
class DefinitionFileTest extends TestCase
{
    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_load_definition_from_file()
    {
        $source = new DefinitionFile(__DIR__ . '/Fixtures/definitions.php');

        /** @var ValueDefinition $definition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf(ValueDefinition::class, $definition);
        $this->assertEquals('bar', $definition->getValue());
        $this->assertIsString($definition->getValue());

        /** @var ObjectDefinition $definition */
        $definition = $source->getDefinition('bim');
        $this->assertInstanceOf(ObjectDefinition::class, $definition);
        $this->assertEquals('bim', $definition->getName());
        $this->assertEquals('bim', $definition->getClassName());
    }

    /**
     * @see https://github.com/PHP-DI/PHP-DI/issues/242
     */
    public function testDefinitionsWithoutKeyThrowAnError()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The PHP-DI definition is not indexed by an entry name in the definition array');
        $source = new DefinitionFile(__DIR__ . '/Fixtures/definitions-fail.php');
        $source->getDefinition('foo');
    }
}
