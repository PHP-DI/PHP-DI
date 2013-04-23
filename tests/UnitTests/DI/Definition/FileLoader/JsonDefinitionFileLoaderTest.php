<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\FileLoader;

use DI\Definition\FileLoader\JsonDefinitionFileLoader;

/**
 * Test class for JsonDefinitionFileLoader
 */
class JsonDefinitionFileLoaderTest extends DefinitionFileLoaderBaseTestCase
{
    public function testLoad()
    {
        $loader = new JsonDefinitionFileLoader(__DIR__ . '/Fixtures/definitions.json');
        $definitions = $loader->load();
        $this->assertEquals(self::$definitionsReference, $definitions);
    }

    /**
     * @expectedException \DI\Definition\FileLoader\Exception\ParseException
     */
    public function testLoadValidateFail()
    {
        $loader = new JsonDefinitionFileLoader(__DIR__ . '/Fixtures/definitions_invalid.json');
        $loader->load();
    }
}
