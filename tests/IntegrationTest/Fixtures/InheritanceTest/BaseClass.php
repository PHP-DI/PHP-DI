<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Fixtures\InheritanceTest;

use DI\Annotation\Inject;

/**
 * Fixture class
 */
abstract class BaseClass
{
    /**
     * @Inject
     * @var Dependency
     */
    public $property1;

    /**
     * @var Dependency
     */
    public $property2;

    /**
     * @var Dependency
     */
    public $property3;

    /**
     * @param Dependency $param1
     */
    public function __construct(Dependency $param1)
    {
        $this->property3 = $param1;
    }

    /**
     * @Inject
     * @param Dependency $property2
     */
    public function setProperty2(Dependency $property2)
    {
        $this->property2 = $property2;
    }
}
