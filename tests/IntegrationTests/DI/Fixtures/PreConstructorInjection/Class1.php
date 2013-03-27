<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\PreConstructorInjection;

use DI\Annotations\Inject;

/**
 * Fixture class
 */
class Class1
{

    /**
     * @Inject
     * @var \IntegrationTests\DI\Fixtures\PreConstructorInjection\Class2
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
