<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source;

use DI\Definition\ClassDefinition;
use DI\Definition\Source\PHPFileDefinitionSource;

/**
 * Test class for PHPFileDefinitionSource
 */
class PHPFileDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \DI\Definition\Source\PHPFileDefinitionSource
     */
    public function testLoadFromFile()
    {
        $source = new PHPFileDefinitionSource(__DIR__ . '/Fixtures/definitions.php');

        $definition = $source->getDefinition('foo');
        $this->assertNotNull($definition);
        $this->assertEquals('bar', $definition->getValue());
        $this->assertInternalType('string', $definition->getValue());

        /** @var $definition ClassDefinition */
        $definition = $source->getDefinition('bim');
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);
        $this->assertEquals('bim', $definition->getName());
        $this->assertEquals('bim', $definition->getClassName());
    }
}
