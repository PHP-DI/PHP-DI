<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Fixtures\PreConstructorInjection;

use DI\Annotation\Inject;

/**
 * Fixture class
 */
class Class1
{

    /**
     * @Inject
     * @var \DI\Test\IntegrationTest\Fixtures\PreConstructorInjection\Class2
     */
    private $class2;

    public $dependencyInjected = false;

    /**
     * Inject the dependencies
     */
    public function __construct()
    {
        if ($this->class2 !== null) {
            $this->dependencyInjected = true;
        }
    }

}
