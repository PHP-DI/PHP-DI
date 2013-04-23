<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\InheritanceTest;

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
