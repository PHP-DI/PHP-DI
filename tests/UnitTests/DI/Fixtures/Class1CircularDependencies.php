<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Fixtures;

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
