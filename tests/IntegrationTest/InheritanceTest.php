<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest;

use DI\Container;
use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Fixtures\InheritanceTest\SubClass;

/**
 * Test class for bean injection
 *
 * @coversNothing
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
        $instance = $container->get('DI\Test\IntegrationTest\Fixtures\InheritanceTest\SubClass');

        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency', $instance->property1);
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency', $instance->property2);
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency', $instance->property3);
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency', $instance->property4);
    }

    /**
     * Test a dependency is injected if the injection is defined on a child class
     *
     * @dataProvider containerProvider
     */
    public function testInjectionBaseClass(Container $container)
    {
        /** @var $instance SubClass */
        $instance = $container->get('DI\Test\IntegrationTest\Fixtures\InheritanceTest\BaseClass');

        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency', $instance->property1);
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency', $instance->property2);
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency', $instance->property3);
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency', $instance->property4);
    }


    /**
     * PHPUnit data provider: generates container configurations for running the same tests
     * for each configuration possible
     * @return array
     */
    public static function containerProvider()
    {
        // Test with a container using annotations
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->useAnnotations(true);
        $containerAnnotations = $builder->build();
        $containerAnnotations->set(
            'DI\Test\IntegrationTest\Fixtures\InheritanceTest\BaseClass',
            \DI\extend('DI\Test\IntegrationTest\Fixtures\InheritanceTest\SubClass')
        );

        // Test with a container using PHP configuration -> entries are different,
        // definitions shouldn't be shared between 2 different entries se we redefine all properties and methods
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);
        $containerPHPDefinitions = $builder->build();
        $containerPHPDefinitions->set('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency', \DI\object());
        $containerPHPDefinitions->set(
            'DI\Test\IntegrationTest\Fixtures\InheritanceTest\BaseClass',
            \DI\object('DI\Test\IntegrationTest\Fixtures\InheritanceTest\SubClass')
                ->property('property1', \DI\get('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency'))
                ->property('property4', \DI\get('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency'))
                ->constructor(\DI\get('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency'))
                ->method('setProperty2', \DI\get('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency'))
        );
        $containerPHPDefinitions->set(
            'DI\Test\IntegrationTest\Fixtures\InheritanceTest\SubClass',
            \DI\object()
                ->property('property1', \DI\get('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency'))
                ->property('property4', \DI\get('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency'))
                ->constructor(\DI\get('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency'))
                ->method('setProperty2', \DI\get('DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency'))
        );

        return array(
            'annotation' => array($containerAnnotations),
            'php'        => array($containerPHPDefinitions),
        );
    }
}
