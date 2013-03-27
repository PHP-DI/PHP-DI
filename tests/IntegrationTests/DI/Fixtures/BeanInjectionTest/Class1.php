<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\BeanInjectionTest;

use DI\Annotation\Inject;

/**
 * Fixture class
 */
class Class1
{

    /**
     * @Inject
     * @var \IntegrationTests\DI\Fixtures\BeanInjectionTest\Class2
     */
    private $class2;

    /**
     * @Inject
     * @var \IntegrationTests\DI\Fixtures\BeanInjectionTest\Interface1
     */
    private $interface1;

    /**
     * @return Class2
     */
    public function getClass2()
    {
        return $this->class2;
    }

    /**
     * @return Interface1
     */
    public function getInterface1()
    {
        return $this->interface1;
    }

}
