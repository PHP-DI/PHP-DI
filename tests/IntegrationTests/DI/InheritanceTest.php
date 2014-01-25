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
     * PHPUnit data provider: generates container configurations for running the same tests
     * for each configuration possible
     * @return array
     */
    public static function containerProvider()
    {
        // Test with a container using annotations
        $builder = new ContainerBuilder();
        $builder->useReflection(true);
        $builder->useAnnotations(true);
        $containerAnnotations = $builder->build();
        $containerAnnotations->set(
            'IntegrationTests\DI\Fixtures\InheritanceTest\BaseClass',
            \DI\object('IntegrationTests\DI\Fixtures\InheritanceTest\SubClass')
        );

        // Test with a container using PHP configuration -> entries are different,
        // definitions shouldn't be shared between 2 different entries se we redefine all properties and methods
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $containerPHPDefinitions = $builder->build();
        $containerPHPDefinitions->set('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency', \DI\object());
        $containerPHPDefinitions->set(
            'IntegrationTests\DI\Fixtures\InheritanceTest\BaseClass',
            \DI\object('IntegrationTests\DI\Fixtures\InheritanceTest\SubClass')
                ->property('property1', \DI\link('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency'))
                ->property('property4', \DI\link('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency'))
                ->constructor(\DI\link('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency'))
                ->method('setProperty2', \DI\link('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency'))
        );
        $containerPHPDefinitions->set(
            'IntegrationTests\DI\Fixtures\InheritanceTest\SubClass',
            \DI\object()
                ->property('property1', \DI\link('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency'))
                ->property('property4', \DI\link('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency'))
                ->constructor(\DI\link('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency'))
                ->method('setProperty2', \DI\link('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency'))
        );

        return array(
            'annotation' => array($containerAnnotations),
            'php'        => array($containerPHPDefinitions),
        );
    }
}
