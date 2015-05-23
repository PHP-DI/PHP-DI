<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest;

use \DI\Annotation\Inject;
use \DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\Class2 as Alias;
use \DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest as NamespaceAlias;

/**
 * Fixture class
 */
class Issue1
{

    /**
     * @Inject
     * @var Class2
     */
    public $class2;

    /**
     * @Inject
     * @var Alias
     */
    public $alias;

    /**
     * @Inject
     * @var NamespaceAlias\Class2
     */
    public $namespaceAlias;

}
