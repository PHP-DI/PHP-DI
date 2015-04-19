<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest;

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
     * @expectedExceptionMessage The parameter 'dependency' of DI\Test\IntegrationTest\Fixtures\ConstructorInjectionTest\Buggy1::__construct has no value defined or guessable
     */
    public function testConstructorNonTypeHintedMethod()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get('DI\Test\IntegrationTest\Fixtures\ConstructorInjectionTest\Buggy1');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting dependencies into DI\Test\IntegrationTest\Fixtures\ConstructorInjectionTest\Buggy2: No entry or class found for 'nonExistentEntry'
     */
    public function testConstructorNonExistentEntry()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->get('DI\Test\IntegrationTest\Fixtures\ConstructorInjectionTest\Buggy2');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting in DI\Test\IntegrationTest\Fixtures\SetterInjectionTest\NamedInjectionClass::dependency. No entry or class found for 'namedDependency'
     */
    public function testSetterNamedInjectionNotFound()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $container = $builder->build();
        // Exception (bean not defined)
        $container->get('DI\Test\IntegrationTest\Fixtures\SetterInjectionTest\NamedInjectionClass');
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The parameter 'dependency' of DI\Test\IntegrationTest\Fixtures\SetterInjectionTest\Buggy1::setDependency has no value defined or guessable
     */
    public function testSetterNonTypeHintedMethod()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->get('DI\Test\IntegrationTest\Fixtures\SetterInjectionTest\Buggy1');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting dependencies into DI\Test\IntegrationTest\Fixtures\SetterInjectionTest\Buggy3: No entry or class found for 'nonExistentBean'
     */
    public function testSetterNamedUnknownBean()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->get('DI\Test\IntegrationTest\Fixtures\SetterInjectionTest\Buggy3');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting in DI\Test\IntegrationTest\Fixtures\ValueInjectionTest\ValueInjectionClass::value. No entry or class found for 'db.host'
     */
    public function testValueException()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->get('DI\Test\IntegrationTest\Fixtures\ValueInjectionTest\ValueInjectionClass');
    }
}
