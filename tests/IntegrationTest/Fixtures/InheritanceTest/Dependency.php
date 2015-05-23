<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Fixtures\InheritanceTest;

/**
 * Fixture class
 */
class Dependency
{

    /**
     * @return boolean
     */
    public function getBoolean()
    {
        return true;
    }

}
