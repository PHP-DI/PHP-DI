<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\ClassDefinition;
use DI\Definition\Source\PHPFileDefinitionSource;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Source\PHPFileDefinitionSource
 */
class PHPFileDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_load_definition_from_file()
    {
        $source = new PHPFileDefinitionSource(__DIR__ . '/Fixtures/definitions.php');

        /** @var ValueDefinition $definition */
        $definition = $source->getDefinition('foo');
        $this->assertInstanceOf('DI\Definition\ValueDefinition', $definition);
        $this->assertEquals('bar', $definition->getValue());
        $this->assertInternalType('string', $definition->getValue());

        /** @var ClassDefinition $definition */
        $definition = $source->getDefinition('bim');
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);
        $this->assertEquals('bim', $definition->getName());
        $this->assertEquals('bim', $definition->getClassName());
    }
}
