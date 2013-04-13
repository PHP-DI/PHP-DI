<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Loader;

use DI\Loader\JsonDefinitionFileLoader;

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
     * @expectedException \Exception
     */
    public function testLoadValidateFail()
    {
        $loader = new JsonDefinitionFileLoader(__DIR__ . '/Fixtures/definitions_invalid.json');
        $loader->load();
    }
}