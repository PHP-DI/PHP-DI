<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\BeanInjectionTest\Issue1;

use \DI\Annotations\Inject;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\Class2;

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
