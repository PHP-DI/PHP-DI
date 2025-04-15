<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Issues;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\IntegrationTest\Issues\Issue72\Class1;

/**
 * Test that the manager prioritize correctly the different sources.
 *
 * @see https://github.com/mnapoli/PHP-DI/issues/72
 */
class Issue72Test extends BaseContainerTest
{
    /**
     * @test
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function annotationDefinitionShouldOverrideReflectionDefinition(ContainerBuilder $builder)
    {
        $builder->useAutowiring(true);
        $builder->useAttributes(true);
        $container = $builder->build();

        $value = new \stdClass();
        $value->foo = 'bar';
        $container->set('service1', $value);

        /** @var Class1 $class1 */
        $class1 = $container->get(Class1::class);

        $this->assertEquals('bar', $class1->arg1->foo);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function arrayDefinitionShouldOverrideReflectionDefinition(ContainerBuilder $builder)
    {
        $builder->useAutowiring(true);
        $builder->useAttributes(false);

        // Override to 'service2' in the definition file
        $builder->addDefinitions(__DIR__ . '/Issue72/definitions.php');

        $container = $builder->build();

        /** @var Class1 $class1 */
        $class1 = $container->get(Class1::class);

        $this->assertEquals('bar', $class1->arg1->foo);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function arrayDefinitionShouldOverrideAnnotationDefinition(ContainerBuilder $builder)
    {
        $builder->useAutowiring(false);
        $builder->useAttributes(true);

        // Override 'service1' to 'service2' in the definition file
        $builder->addDefinitions(__DIR__ . '/Issue72/definitions.php');

        $container = $builder->build();

        /** @var Class1 $class1 */
        $class1 = $container->get(Class1::class);

        $this->assertEquals('bar', $class1->arg1->foo);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function arrayDefinitionShouldOverrideAnotherArrayDefinition(ContainerBuilder $builder)
    {
        $builder->useAutowiring(false);
        $builder->useAttributes(false);

        // Override 'service1' to 'service2' in the definition file
        $builder->addDefinitions(__DIR__ . '/Issue72/definitions.php');
        // Override 'service2' to 'service3' in the definition file
        $builder->addDefinitions(__DIR__ . '/Issue72/definitions2.php');

        $container = $builder->build();

        /** @var Class1 $class1 */
        $class1 = $container->get(Class1::class);

        $this->assertEquals('baz', $class1->arg1->foo);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function phpDefinitionShouldOverrideArrayDefinition(ContainerBuilder $builder)
    {
        if ($builder->isCompilationEnabled()) {
            // This behavior is not allowed on the compiled container
            return;
        }

        $builder->useAutowiring(false);
        $builder->useAttributes(false);
        $builder->addDefinitions(__DIR__ . '/Issue72/definitions.php');
        $container = $builder->build();

        // Override 'service1' to 'service2'
        $container->set(
            Class1::class,
            \DI\create()
                ->constructor(\DI\get('service2'))
        );

        /** @var Class1 $class1 */
        $class1 = $container->get(Class1::class);

        $this->assertEquals('bar', $class1->arg1->foo);
    }
}
