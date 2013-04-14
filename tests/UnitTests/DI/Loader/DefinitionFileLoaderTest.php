<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Loader;

/**
 * Test class for DefinitionFileLoader
 */
class DefinitionFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \DI\Loader\Exception\FileNotFoundException
     */
    public function testFileExists()
    {
        $this->getMockForAbstractClass('DI\\Loader\\DefinitionFileLoader', array('abcFile.php'));
    }
}
