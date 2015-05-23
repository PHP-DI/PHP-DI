<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest;

use DI\Annotation\Inject;

/**
 * Fixture class
 */
class LazyInjectionClass
{
    /**
     * @Inject
     * @var \DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\Class2
     */
    private $class2;

    /**
     * @return Class2
     */
    public function getClass2()
    {
        return $this->class2;
    }

    /**
     * @throws \Exception
     * @return boolean
     */
    public function getDependencyAttribute()
    {
        if ($this->class2 === null) {
            throw new \Exception('Injection of $class2 failed');
        }
        return $this->class2->getBoolean();
    }
}
