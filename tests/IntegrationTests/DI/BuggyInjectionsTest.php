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
 * Tests buggy cases
 */
class BuggyInjectionsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The parameter 'dependency' of the constructor of 'IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Buggy1' has no type defined or guessable
     */
    public function testConstructorNonTypeHintedMethod()
    {
        $container = new Container();
        $container->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Buggy1');
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The parameter 'dependency' of the constructor of 'IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Buggy2' has no type defined or guessable
     */
    public function testConstructorNamedUnknownBean()
    {
        $container = new Container();
        $container->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Buggy2');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting 'namedDependency' in IntegrationTests\DI\Fixtures\SetterInjectionTest\NamedInjectionClass::dependency. No bean, value or class found for 'namedDependency'
     */
    public function testSetterNamedInjectionNotFound()
    {
        $container = new Container();
        // Exception (bean not defined)
        $container->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\NamedInjectionClass');
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The parameter 'dependency' of IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy1::setDependency has no type defined or guessable
     */
    public function testSetterNonTypeHintedMethod()
    {
        $container = new Container();
        $container->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy1');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting dependencies into IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy3: No bean, value or class found for 'nonExistentBean'
     */
    public function testSetterNamedUnknownBean()
    {
        $container = new Container();
        $container->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy3');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting 'db.host' in IntegrationTests\DI\Fixtures\ValueInjectionTest\ValueInjectionClass::value. No bean, value or class found for 'db.host'
     */
    public function testValueException()
    {
        $container = new Container();
        $container->get('IntegrationTests\DI\Fixtures\ValueInjectionTest\ValueInjectionClass');
    }

}
