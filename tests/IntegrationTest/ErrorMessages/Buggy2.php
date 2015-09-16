<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\ErrorMessages;

use DI\Annotation\Inject;

class Buggy2
{
    /**
     * @Inject({"nonExistentEntry"})
     * @param $dependency
     */
    public function __construct($dependency)
    {
    }
}
