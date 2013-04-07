<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use DI\Container;
use IntegrationTests\DI\Fixtures\PreConstructorInjection\Class1;

/**
 * Testing the injection is done before the constructor is called
 */
class PreConstructorInjectionInjectionTest extends \PHPUnit_Framework_TestCase
{

    public function testDependenciesAreInjectedBeforeConstructorIsCalled()
    {
        $container = new Container();
        /** @var $class1 Class1 */
        $class1 = $container->get('IntegrationTests\DI\Fixtures\PreConstructorInjection\Class1');
        $this->assertTrue($class1->dependencyInjected);
    }

}
