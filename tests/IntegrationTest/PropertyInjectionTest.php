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
use DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\Issue1\AnotherIssue1;
use DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\Issue1;
use DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\NamedBean;

/**
 * Test class for bean injection
 *
 * @coversNothing
 */
class PropertyInjectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Injection of named beans
     */
    public function testNamedInjection()
    {
        $container = ContainerBuilder::buildDevContainer();
        // Configure the named bean
        $bean = new NamedBean();
        $bean->nameForTest = 'namedDependency';
        $container->set('namedDependency', $bean);
        $bean2 = new NamedBean();
        $bean2->nameForTest = 'namedDependency2';
        $container->set('namedDependency2', $bean2);
        // Test
        $class = $container->get('DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\NamedInjectionClass');
        $dependency = $class->getDependency();
        $this->assertNotNull($dependency);
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\NamedBean', $dependency);
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
        $container = ContainerBuilder::buildDevContainer();
        $container->get('DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\NamedInjectionClass');
    }

    /**
     * Check that @ var annotation takes "use" statements into account
     */
    public function testIssue1()
    {
        $container = ContainerBuilder::buildDevContainer();
        /** @var $object Issue1 */
        $object = $container->get('DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\Issue1');
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\Class2', $object->class2);
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\Class2', $object->alias);
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\Class2', $object->namespaceAlias);

        /** @var $object AnotherIssue1 */
        $object = $container->get('DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\Issue1\AnotherIssue1');
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\Class2', $object->dependency);
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\Issue1\Dependency', $object->sameNamespaceDependency);
    }

    /**
     * Check error cases
     * @expectedException \PhpDocReader\AnnotationException
     */
    public function testNotFoundVarAnnotation()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get('DI\Test\IntegrationTest\Fixtures\PropertyInjectionTest\NotFoundVarAnnotation');
    }
}
