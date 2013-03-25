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
use IntegrationTests\DI\Fixtures\SetterInjectionTest\Class1;
use IntegrationTests\DI\Fixtures\SetterInjectionTest\NamedBean;
use IntegrationTests\DI\Fixtures\SetterInjectionTest\NamedInjectionClass;

/**
 * Test class for setter injection
 */
class SetterInjectionTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        // Reset the singleton instance to ensure all tests are independent
        Container::reset();
        $container = Container::getInstance();
        $container->getConfiguration()->addDefinitions(
            array(
                'IntegrationTests\DI\Fixtures\SetterInjectionTest\Interface1' => array(
                    'class' => 'IntegrationTests\DI\Fixtures\SetterInjectionTest\Class3',
                )
            )
        );
    }

    public function testBasicInjection()
    {
        /** @var $class1 Class1 */
        $class1 = Container::getInstance()->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Class1');
        $dependency = $class1->getDependency();
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\SetterInjectionTest\Class2', $dependency);
    }

    public function testInterfaceInjection()
    {
        /** @var $class1 Class1 */
        $class1 = Container::getInstance()->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Class1');
        $dependency = $class1->getInterface1();
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\SetterInjectionTest\Interface1', $dependency);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\SetterInjectionTest\Class3', $dependency);
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
        /** @var $class NamedInjectionClass */
        $class = Container::getInstance()->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\NamedInjectionClass');
        $dependency = $class->getDependency();
        $this->assertNotNull($dependency);
        $this->assertInstanceOf('\IntegrationTests\DI\Fixtures\SetterInjectionTest\NamedBean', $dependency);
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
        Container::getInstance()->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\NamedInjectionClass');
    }

    /**
     * @expectedException \DI\Definition\AnnotationException
     * @expectedExceptionMessage @Inject was found on IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy1::setDependency() but the parameter $dependency has no type: impossible to deduce its type
     */
    public function testNonTypeHintedMethod()
    {
        Container::getInstance()->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy1');
    }

    /**
     * @expectedException \DI\Definition\AnnotationException
     * @expectedExceptionMessage @Inject was found on IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy2::setDependency(), the method should have exactly one parameter
     */
    public function testNoParametersMethod()
    {
        Container::getInstance()->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy2');
    }

    /**
     * @expectedException \DI\NotFoundException
     * @expectedExceptionMessage @Inject was found on IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy3::setDependency(...) but no bean or value 'nonExistentBean' was found
     */
    public function testNamedUnknownBean()
    {
        Container::getInstance()->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy3');
    }

}
