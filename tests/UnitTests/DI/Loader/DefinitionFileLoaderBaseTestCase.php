<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Loader;

/**
 * Abstract test class for DefinitionFileLoaders
 */
abstract class DefinitionFileLoaderBaseTestCase extends \PHPUnit_Framework_TestCase
{
    protected static $definitionsReference = array();

    public static function setUpBeforeClass()
    {
        self::$definitionsReference = include __DIR__ . '/Fixtures/definitions.php';
    }
}