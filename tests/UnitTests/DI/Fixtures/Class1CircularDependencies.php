<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Fixtures;

use Exception;
use DI\Annotation\Scope;
use DI\Annotation\Inject;

/**
 * Fixture class for testing circular dependencies
 *
 */
class Class1CircularDependencies
{
    /**
     * @Inject
     * @var \UnitTests\DI\Fixtures\Class2CircularDependencies
     */
    public $class2;
}
