<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Fixtures\ConstructorInjectionTest;

/**
 * Fixture class
 */
class Buggy1
{
    public function __construct($dependency)
    {
        $this->dependency = $dependency;
    }
}
