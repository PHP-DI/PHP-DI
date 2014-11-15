<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Fixtures;

use Exception;

/**
 * Fixture class for testing Container::newInstanceWithoutConstructor
 */
class NewInstanceWithoutConstructor
{

    /**
     * If the constructor is called, it will throw an exception
     */
    public function __construct()
    {
        throw new Exception("The constructor is called");
    }

}
