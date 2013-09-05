<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\FileLoader;

/**
 * Test class for DefinitionFileLoader
 */
class DefinitionFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \DI\Definition\FileLoader\Exception\FileNotFoundException
     */
    public function testFileExists()
    {
        $this->getMockForAbstractClass('DI\Definition\FileLoader\DefinitionFileLoader', array('abcFile.php'));
    }

    /**
     * @expectedException \DI\Definition\FileLoader\Exception\FileNotFoundException
     */
    public function testFileIsNotADir()
    {
        $this->getMockForAbstractClass('DI\Definition\FileLoader\DefinitionFileLoader', array(__DIR__));
    }
}
