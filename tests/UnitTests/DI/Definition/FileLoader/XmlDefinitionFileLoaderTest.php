<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\FileLoader;

use DI\Definition\FileLoader\XmlDefinitionFileLoader;

/**
 * Test class for XmlDefinitionFileLoader
 */
class XmlDefinitionFileLoaderTest extends DefinitionFileLoaderBaseTestCase
{

//    public function testLoad()
//    {
//        $loader = new XmlDefinitionFileLoader(__DIR__ . '/Fixtures/definitions.xml', false);
//        $definitions = $loader->load();
//        $this->assertEquals(self::$definitionsReference, $definitions);
//    }
//
//    public function testLoadValidate()
//    {
//        $loader = new XmlDefinitionFileLoader(__DIR__ . '/Fixtures/definitions.xml');
//        $definitions = $loader->load();
//        $this->assertEquals(self::$definitionsReference, $definitions);
//    }

    /**
     * @expectedException \DI\Definition\FileLoader\Exception\ParseException
     */
    public function testLoadValidateFail()
    {
        $loader = new XmlDefinitionFileLoader(__DIR__ . '/Fixtures/definitions_invalid.xml');
        $loader->load();
    }

}
