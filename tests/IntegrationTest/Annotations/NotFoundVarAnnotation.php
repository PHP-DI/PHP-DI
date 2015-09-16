<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Annotations;

use \DI\Annotation\Inject;

class NotFoundVarAnnotation
{
    /**
     * @Inject
     * @var this_is_a_non_existent_class
     */
    public $class2;
}
