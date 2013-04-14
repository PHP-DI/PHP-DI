<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\FileLoader;

use DI\Definition\FileLoader\ArrayDefinitionFileLoader;

/**
 * Test class for ArrayDefinitionFileLoader
 */
class ArrayDefinitionFileLoaderTest extends DefinitionFileLoaderBaseTestCase
{
    public function testLoad()
    {
        $loader = new ArrayDefinitionFileLoader(__DIR__ . '/Fixtures/definitions.php');
        $definitions = $loader->load();
        $this->assertEquals(self::$definitionsReference, $definitions);
    }

    /**
     * @expectedException \DI\Definition\FileLoader\Exception\ParseException
     */
    public function testLoadInvalid()
    {
        $loader = new ArrayDefinitionFileLoader(__DIR__ . '/Fixtures/definitions_invalid.php');
        $loader->load();
    }
}
