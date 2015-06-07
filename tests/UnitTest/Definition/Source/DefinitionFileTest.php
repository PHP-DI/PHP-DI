<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\ObjectDefinition;
use DI\Definition\Source\DefinitionFile;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Source\DefinitionFile
 */
class DefinitionFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_load_definition_from_file()
    {
        $source = new DefinitionFile(__DIR__ . '/Fixtures/definitions.php');

        /** @var ValueDefinition $definition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\ValueDefinition', $definition);
        $this->assertEquals('bar', $definition->getValue());
        $this->assertInternalType('string', $definition->getValue());

        /** @var ObjectDefinition $definition */
        $definition = $source->getDefinition('bim');
        $this->assertInstanceOf('DI\Definition\ObjectDefinition', $definition);
        $this->assertEquals('bim', $definition->getName());
        $this->assertEquals('bim', $definition->getClassName());
    }
}
