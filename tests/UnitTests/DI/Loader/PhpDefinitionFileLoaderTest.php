<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Loader;

use DI\Loader\PhpDefinitionFileLoader;

/**
 * Test class for PhpDefinitionFileLoader
 */
class PhpDefinitionFileLoaderTest extends DefinitionFileLoaderBaseTestCase
{
    public function testLoad()
    {
        $loader = new PhpDefinitionFileLoader(__DIR__ . '/Fixtures/definitions.php');
        $definitions = $loader->load();
        $this->assertEquals(self::$definitionsReference, $definitions);
    }

    /**
     * @expectedException \DI\Loader\Exception\ParseException
     */
    public function testLoadInvalid()
    {
        $loader = new PhpDefinitionFileLoader(__DIR__ . '/Fixtures/definitions_invalid.php');
        $loader->load();
    }
}