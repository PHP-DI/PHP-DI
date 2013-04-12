<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use DI\Container;

/**
 * Test class for setter injection
 */
class SetterInjectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting dependency in IntegrationTests\DI\Fixtures\SetterInjectionTest\NamedInjectionClass::dependency. No bean, value or class found for 'namedDependency'
     */
    public function testNamedInjectionNotFound()
    {
        $container = new Container();
        // Exception (bean not defined)
        $container->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\NamedInjectionClass');
    }

    /**
     * @expectedException \DI\Definition\DefinitionException
     * @expectedExceptionMessage The parameter 'dependency' of IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy1::setDependency has no type defined or guessable
     */
    public function testNonTypeHintedMethod()
    {
        $container = new Container();
        $container->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy1');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting dependencies into IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy3: No bean, value or class found for 'nonExistentBean'
     */
    public function testNamedUnknownBean()
    {
        $container = new Container();
        $container->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy3');
    }

}
