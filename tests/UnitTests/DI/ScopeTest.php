<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI;

use DI\Scope;

/**
 * @covers \DI\Scope
 */
class ScopeTest extends \PHPUnit_Framework_TestCase
{
    public function testPrototype()
    {
        $scope = Scope::PROTOTYPE();
        $this->assertEquals('prototype', $scope->getValue());
    }

    public function testSingleton()
    {
        $scope = Scope::SINGLETON();
        $this->assertEquals('singleton', $scope->getValue());
    }
}
