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
use DI\ContainerBuilder;
use IntegrationTests\DI\Fixtures\InheritanceTest\SubClass;

/**
 * Test class for bean injection
 */
class InheritanceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test a dependency is injected if the injection is defined on a parent class
     *
     * @dataProvider containerProvider
     */
    public function testInjectionSubClass(Container $container)
    {
        /** @var $instance SubClass */
        $instance = $container->get('IntegrationTests\DI\Fixtures\InheritanceTest\SubClass');

        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency', $instance->property1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency', $instance->property2);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency', $instance->property3);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency', $instance->property4);
    }

    /**
     * Test a dependency is injected if the injection is defined on a child class
     *
     * @dataProvider containerProvider
     */
    public function testInjectionBaseClass(Container $container)
    {
        /** @var $instance SubClass */
        $instance = $container->get('IntegrationTests\DI\Fixtures\InheritanceTest\BaseClass');

        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency', $instance->property1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency', $instance->property2);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency', $instance->property3);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency', $instance->property4);
    }


    /**
     * PHPUnit data provider: generates container configurations for running the same tests for each configuration possible
     * @return array
     */
    public static function containerProvider()
    {
        // Test with a container using annotations
        $builder = new ContainerBuilder();
        $builder->useReflection(true);
        $builder->useAnnotations(true);
        $containerAnnotations = $builder->build();
        $containerAnnotations->set('IntegrationTests\DI\Fixtures\InheritanceTest\BaseClass')
            ->bindTo('IntegrationTests\DI\Fixtures\InheritanceTest\SubClass');

        // Test with a container using array configuration
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $containerFullArrayDefinitions = $builder->build();
        $containerFullArrayDefinitions->addDefinitions(
            array(
                'IntegrationTests\DI\Fixtures\InheritanceTest\BaseClass' => array(
                    'class'       => 'IntegrationTests\DI\Fixtures\InheritanceTest\SubClass',
                    'properties'  => array(
                        'property1' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                        'property4' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                    ),
                    'constructor' => array(
                        'param1' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                    ),
                    'methods'     => array(
                        'setProperty2' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                    ),
                ),
                'IntegrationTests\DI\Fixtures\InheritanceTest\SubClass'  => array(
                    'properties'  => array(
                        'property1' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                        'property4' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                    ),
                    'constructor' => array(
                        'param1' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                    ),
                    'methods'     => array(
                        'setProperty2' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                    ),
                ),
            )
        );

        // Test with a container using array configuration
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $containerInheritanceDefinitions = $builder->build();
        $containerInheritanceDefinitions->addDefinitions(
            array(
                'IntegrationTests\DI\Fixtures\InheritanceTest\BaseClass' => array(
                    'class'       => 'IntegrationTests\DI\Fixtures\InheritanceTest\SubClass',
                    'properties'  => array(
                        'property1' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                    ),
                    'constructor' => array(
                        'param1' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                    ),
                    'methods'     => array(
                        'setProperty2' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                    ),
                ),
                'IntegrationTests\DI\Fixtures\InheritanceTest\SubClass'  => array(
                    'properties' => array(
                        'property4' => 'IntegrationTests\DI\Fixtures\InheritanceTest\Dependency',
                    ),
                ),
            )
        );

        return array(
            array($containerAnnotations),
            array($containerFullArrayDefinitions),
            array($containerInheritanceDefinitions),
        );
    }

}
