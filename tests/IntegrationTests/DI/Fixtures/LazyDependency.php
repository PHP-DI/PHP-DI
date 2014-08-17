<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures;

use DI\Annotation\Injectable;

/**
 * Fixture class
 * @Injectable(lazy=true)
 */
class LazyDependency
{
    /**
     * @return boolean
     */
    public function getValue()
    {
        return true;
    }
}
