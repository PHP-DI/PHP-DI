<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\ConstructorInjectionTest;

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
     * @param Class2     $dependency
     * @param Interface1 $interface
     */
    public function __construct(Class2 $dependency, Interface1 $interface)
    {
        $this->dependency = $dependency;
        $this->interface1 = $interface;
    }

    /**
     * @return Class2
     */
    public function getDependency()
    {
        return $this->dependency;
    }

    /**
     * @return Interface1
     */
    public function getInterface1()
    {
        return $this->interface1;
    }

}
