<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use \DI\Container;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\Class2;
use IntegrationTests\DI\Fixtures\BeanInjectionTest\Issue14;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\LazyInjectionClass;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\NamedBean;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\NamedInjectionClass;

/**
 * Test class for bean injection
 */
class BeanInjectionTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        // Reset the singleton instance to ensure all tests are independent
        Container::reset();
        $container = Container::getInstance();
        $container->getConfiguration()->addDefinitions(
            array(
                'IntegrationTests\DI\Fixtures\BeanInjectionTest\Interface1' => array(
                    'class' => 'IntegrationTests\DI\Fixtures\BeanInjectionTest\Class3',
                )
            )
        );
    }


    public function testBasicInjection()
    {
        $container = Container::getInstance();
        $class1 = $container->get('IntegrationTests\DI\Fixtures\BeanInjectionTest\Class1');
        $dependency = $class1->getClass2();
        $this->assertNotNull($dependency);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\BeanInjectionTest\Class2', $dependency);
    }

    public function testInterfaceInjection()
    {
        $container = Container::getInstance();
        $class1 = $container->get('IntegrationTests\DI\Fixtures\BeanInjectionTest\Class1');
        $dependency = $class1->getInterface1();
        $this->assertNotNull($dependency);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\BeanInjectionTest\Class3', $dependency);
    }

    /**
     * Injection with lazy enabled
     */
    public function testLazyInjection1()
    {
        $container = Container::getInstance();
        /** @var $class LazyInjectionClass */
        $class = $container->get('IntegrationTests\DI\Fixtures\BeanInjectionTest\LazyInjectionClass');
        $dependency = $class->getClass2();
        $this->assertNotNull($dependency);
        $this->assertInstanceOf('\DI\Proxy\Proxy', $dependency);
        // Correct proxy resolution
        $this->assertTrue($dependency->getBoolean());
    }

    public function testLazyInjection2()
    {
        $container = Container::getInstance();
        /** @var $class LazyInjectionClass */
        $class = $container->get('IntegrationTests\DI\Fixtures\BeanInjectionTest\LazyInjectionClass');
        $this->assertTrue($class->getDependencyAttribute());
    }

    /**
     * Injection of named beans
     */
    public function testNamedInjection()
    {
        $container = Container::getInstance();
        // Configure the named bean
        $bean = new NamedBean();
        $bean->nameForTest = 'namedDependency';
        $container->set('namedDependency', $bean);
        $bean2 = new NamedBean();
        $bean2->nameForTest = 'namedDependency2';
        $container->set('namedDependency2', $bean2);
        // Test
        $class = $container->get('IntegrationTests\DI\Fixtures\BeanInjectionTest\NamedInjectionClass');
        $dependency = $class->getDependency();
        $this->assertNotNull($dependency);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\BeanInjectionTest\NamedBean', $dependency);
        $this->assertEquals('namedDependency', $dependency->nameForTest);
        $this->assertSame($bean, $dependency);
        $this->assertNotSame($bean2, $dependency);
    }

    /**
     * @expectedException \DI\NotFoundException
     */
    public function testNamedInjectionNotFound()
    {
        // Exception (bean not defined)
        $container = Container::getInstance();
        $container->get('IntegrationTests\DI\Fixtures\BeanInjectionTest\NamedInjectionClass');
    }

    /**
     * Check that if a dependency is already set, the container
     * will not overwrite it
     */
    public function testIssue14()
    {
        $object = new Issue14();
        $class2 = new Class2();
        $object->setClass2($class2);
        Container::getInstance()->injectAll($object);
        $this->assertSame($class2, $object->getClass2());
    }

    /**
     * Check that @ var annotation takes "use" statements into account
     */
    public function testIssue1()
    {
        /** @var $object \IntegrationTests\DI\Fixtures\BeanInjectionTest\Issue1 */
        $object = Container::getInstance()->get('IntegrationTests\DI\Fixtures\BeanInjectionTest\Issue1');
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\BeanInjectionTest\Class2', $object->class2);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\BeanInjectionTest\Class2', $object->alias);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\BeanInjectionTest\Class2', $object->namespaceAlias);

        /** @var $object \IntegrationTests\DI\Fixtures\BeanInjectionTest\Issue1\AnotherIssue1 */
        $object = Container::getInstance()->get('IntegrationTests\DI\Fixtures\BeanInjectionTest\Issue1\AnotherIssue1');
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\BeanInjectionTest\Class2', $object->dependency);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\BeanInjectionTest\Issue1\Dependency', $object->sameNamespaceDependency);
    }

    /**
     * Check error cases
     * @expectedException \DI\Definition\AnnotationException
     */
    public function testNotFoundVarAnnotation()
    {
        /** @var $object \IntegrationTests\DI\Fixtures\BeanInjectionTest\NotFoundVarAnnotation */
        Container::getInstance()->get('IntegrationTests\DI\Fixtures\BeanInjectionTest\NotFoundVarAnnotation');
    }

}
