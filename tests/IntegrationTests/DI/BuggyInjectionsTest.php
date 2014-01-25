<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use DI\ContainerBuilder;

/**
 * Tests buggy cases
 *
 * @coversNothing
 */
class BuggyInjectionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The parameter 'dependency' of IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Buggy1::__construct has no value defined or guessable
     */
    public function testConstructorNonTypeHintedMethod()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Buggy1');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting dependencies into IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Buggy2: No entry or class found for 'nonExistentEntry'
     */
    public function testConstructorNonExistentEntry()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Buggy2');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting 'namedDependency' in IntegrationTests\DI\Fixtures\SetterInjectionTest\NamedInjectionClass::dependency. No entry or class found for 'namedDependency'
     */
    public function testSetterNamedInjectionNotFound()
    {
        $container = ContainerBuilder::buildDevContainer();
        // Exception (bean not defined)
        $container->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\NamedInjectionClass');
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The parameter 'dependency' of IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy1::setDependency has no value defined or guessable
     */
    public function testSetterNonTypeHintedMethod()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy1');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting dependencies into IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy3: No entry or class found for 'nonExistentBean'
     */
    public function testSetterNamedUnknownBean()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy3');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting 'db.host' in IntegrationTests\DI\Fixtures\ValueInjectionTest\ValueInjectionClass::value. No entry or class found for 'db.host'
     */
    public function testValueException()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get('IntegrationTests\DI\Fixtures\ValueInjectionTest\ValueInjectionClass');
    }
}
