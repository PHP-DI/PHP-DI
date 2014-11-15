<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Fixtures\SetterInjectionTest;

use DI\Annotation\Inject;

/**
 * Fixture class
 */
class Buggy1
{
    /**
     * @Inject
     * @param $dependency
     */
    public function setDependency($dependency)
    {
        $this->dependency = $dependency;
    }
}
