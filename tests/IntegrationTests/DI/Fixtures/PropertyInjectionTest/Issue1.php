<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\PropertyInjectionTest;

use \DI\Annotation\Inject;
use \IntegrationTests\DI\Fixtures\PropertyInjectionTest\Class2 as Alias;
use \IntegrationTests\DI\Fixtures\PropertyInjectionTest as NamespaceAlias;

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
