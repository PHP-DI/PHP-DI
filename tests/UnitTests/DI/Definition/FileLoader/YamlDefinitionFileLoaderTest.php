<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\FileLoader;

use DI\Definition\FileLoader\YamlDefinitionFileLoader;

/**
 * Test class for YamlDefinitionFileLoader
 */
class YamlDefinitionFileLoaderTest extends DefinitionFileLoaderBaseTestCase
{
    public function testLoad()
    {
        $loader = new YamlDefinitionFileLoader(__DIR__ . '/Fixtures/definitions.yaml');
        $definitions = $loader->load();
        $this->assertEquals(self::$definitionsReference, $definitions);
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/69
     */
    public function testLoadEmptyNoError()
    {
        $loader = new YamlDefinitionFileLoader(__DIR__ . '/Fixtures/definitions_empty.yaml');
        $definitions = $loader->load();
        $this->assertInternalType('array', $definitions);
    }

    /**
     * @expectedException \DI\Definition\FileLoader\Exception\ParseException
     */
    public function testLoadInvalid()
    {
        $loader = new YamlDefinitionFileLoader(__DIR__ . '/Fixtures/definitions_invalid.yaml');
        $loader->load();
    }
}
