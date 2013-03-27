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
use IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Class1;

/**
 * Test class for constructor injection
 */
class ConstructorInjectionTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        // Reset the singleton instance to ensure all tests are independent
        Container::reset();
        $container = Container::getInstance();
        $container->getConfiguration()->addDefinitions(
            array(
                'IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Interface1' => array(
                    'class' => 'IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Class3',
                )
            )
        );
    }

    public function testBasicInjection()
    {
        /** @var $class1 Class1 */
        $class1 = Container::getInstance()->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Class1');
        $dependency = $class1->getDependency();
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Class2', $dependency);
    }

    public function testInterfaceInjection()
    {
        /** @var $class1 Class1 */
        $class1 = Container::getInstance()->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Class1');
        $dependency = $class1->getInterface1();
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Interface1', $dependency);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Class3', $dependency);
    }

    /**
     * @expectedException \DI\Definition\DefinitionException
     * @expectedExceptionMessage The parameter 'dependency' of the constructor of 'IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Buggy1' has no type defined or guessable
     */
    public function testNonTypeHintedMethod()
    {
        Container::getInstance()->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Buggy1');
    }

    /**
     * @expectedException \DI\Definition\DefinitionException
     * @expectedExceptionMessage The parameter 'dependency' of the constructor of 'IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Buggy2' has no type defined or guessable
     */
    public function testNamedUnknownBean()
    {
        Container::getInstance()->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Buggy2');
    }

}
