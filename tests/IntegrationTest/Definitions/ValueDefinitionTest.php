<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\IntegrationTest;
use stdClass;

/**
 * Test value definitions.
 *
 * @coversNothing
 */
class ValueDefinitionTest extends IntegrationTest
{
    /**
     * @dataProvider provideBuilder
     */
    public function test_string(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'string' => 'foo',
        ]);

        $container = $builder->build();
        $this->assertEquals('foo', $container->get('string'));
    }

    /**
     * @dataProvider provideBuilder
     */
    public function test_int(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'int' => 123,
        ]);

        $container = $builder->build();
        $this->assertEquals(123, $container->get('int'));
    }

    /**
     * @dataProvider provideBuilder
     */
    public function test_object(ContainerBuilder $builder)
    {
        if ($builder->isCompiled()) {
            $this->setExpectedException(
                'DI\Compiler\CompilationException',
                'Impossible to compile objects to PHP code, use a factory or a class definition instead'
            );
        }

        $builder->addDefinitions([
            'object' => new stdClass(),
        ]);

        $container = $builder->build();
        $this->assertEquals(new \stdClass(), $container->get('object'));
    }

    /**
     * @dataProvider provideBuilder
     */
    public function test_function_helper(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'helper' => \DI\value('foo'),
        ]);

        $container = $builder->build();
        $this->assertEquals('foo', $container->get('helper'));
    }

    /**
     * @dataProvider provideBuilder
     */
    public function test_closure(ContainerBuilder $builder)
    {
        if ($builder->isCompiled()) {
            $this->setExpectedException(
                'DI\Compiler\CompilationException',
                'Impossible to compile objects to PHP code, use a factory or a class definition instead'
            );
        }

        $builder->addDefinitions([
            'closure' => \DI\value(function () {
                return 'foo';
            }),
        ]);

        $container = $builder->build();
        $closure = $container->get('closure');
        $this->assertInstanceOf('Closure', $closure);
        $this->assertEquals('foo', call_user_func($closure));
    }
}
