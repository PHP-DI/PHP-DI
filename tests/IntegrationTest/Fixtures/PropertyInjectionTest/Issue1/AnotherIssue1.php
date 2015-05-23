<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\Issue1;

use \DI\Annotation\Inject;
use \DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\Class2;

/**
 * Fixture class
 */
class AnotherIssue1
{

    /**
     * @Inject
     * @var Class2
     */
    public $dependency;

    /**
     * @Inject
     * @var Dependency
     */
    public $sameNamespaceDependency;

}
