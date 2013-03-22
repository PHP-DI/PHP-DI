<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\SetterInjectionTest;

use DI\Annotations\Inject;
use IntegrationTests\DI\Fixtures\SetterInjectionTest\Class2;

/**
 * Fixture class
 */
class Buggy1
{

    /**
     * @var Class2
     */
    private $dependency;

    /**
     * @Inject
     * @param $dependency
     */
    public function setDependency($dependency)
    {
        $this->dependency = $dependency;
    }

    /**
     * @return Class2
     */
    public function getDependency()
    {
        return $this->dependency;
    }

}
