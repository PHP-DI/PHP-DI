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
use IntegrationTests\DI\Fixtures\PropertyInjectionTest\Class2;
use IntegrationTests\DI\Fixtures\PropertyInjectionTest\Issue1\AnotherIssue1;
use IntegrationTests\DI\Fixtures\PropertyInjectionTest\Issue1\Dependency;
use IntegrationTests\DI\Fixtures\PropertyInjectionTest\Issue1;
use IntegrationTests\DI\Fixtures\PropertyInjectionTest\NamedBean;
use IntegrationTests\DI\Fixtures\PropertyInjectionTest\NamedInjectionClass;
use IntegrationTests\DI\Fixtures\PropertyInjectionTest\NotFoundVarAnnotation;

/**
 * Test class for bean injection
 */
class PropertyInjectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Injection of named beans
     */
    public function testNamedInjection()
    {
        $container = new Container();
        // Configure the named bean
        $bean = new NamedBean();
        $bean->nameForTest = 'namedDependency';
        $container->set('namedDependency', $bean);
        $bean2 = new NamedBean();
        $bean2->nameForTest = 'namedDependency2';
        $container->set('namedDependency2', $bean2);
        // Test
        $class = $container->get(NamedInjectionClass::class);
        $dependency = $class->getDependency();
        $this->assertNotNull($dependency);
        $this->assertInstanceOf(NamedBean::class, $dependency);
        $this->assertEquals('namedDependency', $dependency->nameForTest);
        $this->assertSame($bean, $dependency);
        $this->assertNotSame($bean2, $dependency);
    }

    /**
     * @expectedException \DI\DependencyException
     */
    public function testNamedInjectionNotFound()
    {
        // Exception (bean not defined)
        $container = new Container();
        $container->get(NamedInjectionClass::class);
    }

    /**
     * Check that @ var annotation takes "use" statements into account
     */
    public function testIssue1()
    {
        $container = new Container();
        /** @var $object Issue1 */
        $object = $container->get(Issue1::class);
        $this->assertInstanceOf(Class2::class, $object->class2);
        $this->assertInstanceOf(Class2::class, $object->alias);
        $this->assertInstanceOf(Class2::class, $object->namespaceAlias);

        /** @var $object AnotherIssue1 */
        $object = $container->get(AnotherIssue1::class);
        $this->assertInstanceOf(Class2::class, $object->dependency);
        $this->assertInstanceOf(Dependency::class, $object->sameNamespaceDependency);
    }

    /**
     * Check error cases
     * @expectedException \PhpDocReader\AnnotationException
     */
    public function testNotFoundVarAnnotation()
    {
        $container = new Container();
        $container->get(NotFoundVarAnnotation::class);
    }
}
