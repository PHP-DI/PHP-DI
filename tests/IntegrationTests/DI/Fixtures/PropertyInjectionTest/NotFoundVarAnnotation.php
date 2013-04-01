<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\PropertyInjectionTest;

use \DI\Annotation\Inject;

/**
 * Fixture class
 */
class NotFoundVarAnnotation
{

    /**
     * @Inject
     * @var this_is_a_non_existent_class
     */
    public $class2;

}
