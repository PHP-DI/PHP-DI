<?php

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;

/**
 * Test the definition syntax.
 *
 * @coversNothing
 */
class DefinitionsTest extends \PHPUnit_Framework_TestCase
{
    public function test_multiple_method_call()
    {
        $container = $this->createContainer([
            'DI\Test\IntegrationTest\Fixtures\DefinitionTest\Class1' => \DI\object()
                ->method('increment')
                ->method('increment'),
        ]);

        $class = $container->get('DI\Test\IntegrationTest\Fixtures\DefinitionTest\Class1');
        $this->assertEquals(2, $class->count);
    }

    public function test_override_parameter_with_multiple_method_call()
    {
        $container = $this->createContainer(
            [
                'DI\Test\IntegrationTest\Fixtures\DefinitionTest\Class1' => \DI\object()
                    ->method('add', 'foo')
                    ->method('add', 'foo'),
            ],
            [
                // Override a method parameter
                'DI\Test\IntegrationTest\Fixtures\DefinitionTest\Class1' => \DI\object()
                    ->methodParameter('add', 0, 'bar'),
            ]
        );

        // Should override only the first method call
        $class = $container->get('DI\Test\IntegrationTest\Fixtures\DefinitionTest\Class1');
        $this->assertEquals(['bar', 'foo'], $class->items);
    }

    private function createContainer(array $definitions, array $definitions2 = [])
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);
        $builder->addDefinitions($definitions);
        if (! empty($definitions2)) {
            $builder->addDefinitions($definitions2);
        }
        return $builder->build();
    }
}
