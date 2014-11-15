<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Fixtures;

/**
 * Fixture class for testing circular dependencies
 *
 */
class Class1CircularDependencies
{
    /**
     * @Inject
     * @var \DI\Test\UnitTest\Fixtures\Class2CircularDependencies
     */
    public $class2;
}
