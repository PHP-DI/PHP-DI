<?php

namespace DI\Test\IntegrationTest;

use DI\Container;
use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Fixtures\InheritanceTest\BaseClass;
use DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency;
use DI\Test\IntegrationTest\Fixtures\InheritanceTest\SubClass;

/**
 * Test class for bean injection.
 *
 * @coversNothing
 */
class InheritanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test a dependency is injected if the injection is defined on a parent class.
     *
     * @dataProvider containerProvider
     */
    public function testInjectionSubClass(Container $container)
    {
        /** @var $instance SubClass */
        $instance = $container->get(SubClass::class);

        $this->assertInstanceOf(Dependency::class, $instance->property1);
        $this->assertInstanceOf(Dependency::class, $instance->property2);
        $this->assertInstanceOf(Dependency::class, $instance->property3);
        $this->assertInstanceOf(Dependency::class, $instance->property4);
    }

    /**
     * Test a dependency is injected if the injection is defined on a child class.
     *
     * @dataProvider containerProvider
     */
    public function testInjectionBaseClass(Container $container)
    {
        /** @var $instance SubClass */
        $instance = $container->get(BaseClass::class);

        $this->assertInstanceOf(Dependency::class, $instance->property1);
        $this->assertInstanceOf(Dependency::class, $instance->property2);
        $this->assertInstanceOf(Dependency::class, $instance->property3);
        $this->assertInstanceOf(Dependency::class, $instance->property4);
    }

    /**
     * PHPUnit data provider: generates container configurations for running the same tests
     * for each configuration possible.
     * @return array
     */
    public static function containerProvider()
    {
        // Test with a container using annotations
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->useAnnotations(true);
        $containerAnnotations = $builder->build();
        $containerAnnotations->set(BaseClass::class, \DI\get(SubClass::class));

        // Test with a container using PHP configuration -> entries are different,
        // definitions shouldn't be shared between 2 different entries se we redefine all properties and methods
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);
        $containerPHPDefinitions = $builder->build();
        $containerPHPDefinitions->set(Dependency::class, \DI\create());
        $containerPHPDefinitions->set(
            BaseClass::class,
            \DI\create(SubClass::class)
                ->property('property1', \DI\get(Dependency::class))
                ->property('property4', \DI\get(Dependency::class))
                ->constructor(\DI\get(Dependency::class))
                ->method('setProperty2', \DI\get(Dependency::class))
        );
        $containerPHPDefinitions->set(
            SubClass::class,
            \DI\create()
                ->property('property1', \DI\get(Dependency::class))
                ->property('property4', \DI\get(Dependency::class))
                ->constructor(\DI\get(Dependency::class))
                ->method('setProperty2', \DI\get(Dependency::class))
        );

        return [
            'annotation' => [$containerAnnotations],
            'php'        => [$containerPHPDefinitions],
        ];
    }
}
