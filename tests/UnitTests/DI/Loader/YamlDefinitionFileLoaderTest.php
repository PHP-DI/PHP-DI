<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Loader;

use DI\Loader\YamlDefinitionFileLoader;

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
     * @expectedException \DI\Loader\Exception\ParseException
     */
    public function testLoadInvalid()
    {
        $loader = new YamlDefinitionFileLoader(__DIR__ . '/Fixtures/definitions_invalid.yaml');
        $loader->load();
    }
}