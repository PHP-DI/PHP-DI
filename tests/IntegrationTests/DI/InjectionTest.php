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
use IntegrationTests\DI\Fixtures\Class1;

/**
 * Test class for injection
 */
class InjectionTest extends \PHPUnit_Framework_TestCase
{

    const DEFINITION_REFLECTION = 1;
    const DEFINITION_ANNOTATIONS = 2;
    const DEFINITION_ARRAY = 3;

    public static function containerProvider()
    {
        // Test with a container using annotations and reflection
        $containerReflection = new Container();
        $containerReflection->getConfiguration()->useReflection(true);
        $containerReflection->getConfiguration()->useAnnotations(false);
        $containerReflection->getConfiguration()->addDefinitions(
            array(
                'foo' => 'bar',
                'IntegrationTests\DI\Fixtures\Interface1' => array(
                    'class' => 'IntegrationTests\DI\Fixtures\Implementation1',
                ),
                'namedDependency' => array(
                    'class' => 'IntegrationTests\DI\Fixtures\Class2',
                ),
            )
        );

        // Test with a container using annotations
        $containerAnnotations = new Container();
        $containerAnnotations->getConfiguration()->useReflection(true);
        $containerAnnotations->getConfiguration()->useAnnotations(true);
        $containerAnnotations->getConfiguration()->addDefinitions(
            array(
                'foo' => 'bar',
                'IntegrationTests\DI\Fixtures\Interface1' => array(
                    'class' => 'IntegrationTests\DI\Fixtures\Implementation1',
                ),
                'namedDependency' => array(
                    'class' => 'IntegrationTests\DI\Fixtures\Class2',
                ),
            )
        );

        // Test with a container using array configuration
        $containerArray = new Container();
        $containerArray->getConfiguration()->useReflection(false);
        $containerArray->getConfiguration()->useAnnotations(false);
        $containerArray->getConfiguration()->addDefinitions(
            array(
                'foo' => 'bar',
                'IntegrationTests\DI\Fixtures\Class1'          => array(
                    'properties'  => array(
                        'property1' => 'IntegrationTests\DI\Fixtures\Class2',
                        'property2' => 'IntegrationTests\DI\Fixtures\Interface1',
                        'property3' => 'namedDependency',
                        'property4' => 'foo',
                    ),
                    'constructor' => array(
                        'param1' => 'IntegrationTests\DI\Fixtures\Class2',
                        'param2' => 'IntegrationTests\DI\Fixtures\Interface1'
                    ),
                    'methods'     => array(
                        'method1' => 'IntegrationTests\DI\Fixtures\Class2',
                        'method2' => array('IntegrationTests\DI\Fixtures\Interface1'),
                        'method3' => array(
                            'param1' => 'namedDependency',
                            'param2' => 'foo',
                        ),
                    ),
                ),
                'IntegrationTests\DI\Fixtures\Class2'          => array(),
                'IntegrationTests\DI\Fixtures\Implementation1' => array(),
                'IntegrationTests\DI\Fixtures\Interface1'      => array(
                    'class' => 'IntegrationTests\DI\Fixtures\Implementation1',
                ),
                'namedDependency' => array(
                    'class' => 'IntegrationTests\DI\Fixtures\Class2',
                ),
            )
        );

        return array(
            array(self::DEFINITION_REFLECTION, $containerReflection),
            array(self::DEFINITION_ANNOTATIONS, $containerAnnotations),
            array(self::DEFINITION_ARRAY, $containerArray),
        );
    }

    /**
     * @dataProvider containerProvider
     */
    public function testConstructorInjection($type, Container $container)
    {
        /** @var $class1 Class1 */
        $class1 = $container->get('IntegrationTests\DI\Fixtures\Class1');
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->constructorParam1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->constructorParam2);
    }

    /**
     * @dataProvider containerProvider
     */
    public function testPropertyInjection($type, Container $container)
    {
        // Only constructor injection with reflection
        if ($type == self::DEFINITION_REFLECTION) {
            return;
        }
        /** @var $class1 Class1 */
        $class1 = $container->get('IntegrationTests\DI\Fixtures\Class1');
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->property1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->property2);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->property3);
        $this->assertEquals('bar', $class1->property4);
    }

    /**
     * @dataProvider containerProvider
     */
    public function testMethodInjection($type, Container $container)
    {
        // Only constructor injection with reflection
        if ($type == self::DEFINITION_REFLECTION) {
            return;
        }
        /** @var $class1 Class1 */
        $class1 = $container->get('IntegrationTests\DI\Fixtures\Class1');
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method1Param1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->method2Param1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method3Param1);
        $this->assertEquals('bar', $class1->method3Param2);
    }

}
