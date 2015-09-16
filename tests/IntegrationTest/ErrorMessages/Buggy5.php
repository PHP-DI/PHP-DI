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

class Buggy5
{
    /**
     * @Inject
     */
    public function setDependency($dependency)
    {
        $this->dependency = $dependency;
    }
}
