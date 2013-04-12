<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\PropertyInjectionTest;

use DI\Annotation\Inject;

/**
 * Fixture class
 */
class NamedInjectionClass
{

    /**
     * @Inject(name="namedDependency")
     */
    private $dependency;

    public function getDependency()
    {
        return $this->dependency;
    }

}
