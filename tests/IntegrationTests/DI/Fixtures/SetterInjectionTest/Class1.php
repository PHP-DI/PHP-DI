<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\SetterInjectionTest;

use DI\Annotation\Inject;

/**
 * Fixture class
 */
class Class1
{

    /**
     * @var Class2
     */
    private $dependency;

    /**
     * @var Interface1
     */
    private $interface1;

    /**
     * @Inject
     * @param Class2 $dependency
     */
    public function setDependency(Class2 $dependency)
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

    /**
     * @Inject
     * @param Interface1 $interface
     */
    public function setInterface1(Interface1 $interface)
    {
        $this->interface1 = $interface;
    }

    /**
     * @return Interface1
     */
    public function getInterface1()
    {
        return $this->interface1;
    }

}
