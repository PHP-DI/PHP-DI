<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use function DI\create;

/**
 * Tests specific to the compiled container.
 */
class CompiledContainerTest extends BaseContainerTest
{
    /** @test */
    public function the_same_container_can_be_recreated_multiple_times()
    {
        $builder = new ContainerBuilder;
        $builder->compile(self::generateCompilationFileName());
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);

        // The container can be built twice without error
        $builder->build();
        $builder->build();
    }

    /** @test */
    public function the_container_is_compiled_once_and_never_recompiled_after()
    {
        $compiledContainerFile = self::generateCompilationFileName();

        // Create a first compiled container in the file
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);
        $builder->compile($compiledContainerFile);
        $builder->build();

        // Create a second compiled container in the same file but with a DIFFERENT configuration
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            'foo' => 'DIFFERENT',
        ]);
        $builder->compile($compiledContainerFile);
        $container = $builder->build();

        // The second container is actually using the config of the first because the container was already compiled
        // (the compiled file already existed so the second container did not recompile into it)
        // This behavior is obvious for performance reasons.
        self::assertEquals('bar', $container->get('foo'));
    }

    /**
     * @test
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage Entry "foo" cannot be compiled: anonymous classes cannot be compiled
     */
    public function anonymous_classes_cannot_be_compiled()
    {
        $class = get_class(new class() {
        });

        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            'foo' => create($class),
        ]);
        $builder->compile(self::generateCompilationFileName());
        $builder->build();
    }

    /**
     * @test
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage Entry "stdClass" cannot be compiled: An object was found but objects cannot be compiled
     */
    public function object_nested_in_other_definitions_cannot_be_compiled()
    {
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            \stdClass::class => create()
                ->property('foo', new \stdClass),
        ]);
        $builder->compile(self::generateCompilationFileName());
        $builder->build();
    }

    /**
     * @test
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while compiling foo. An object was found but objects cannot be compiled
     */
    public function object_nested_in_arrays_cannot_be_compiled()
    {
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            'foo' => [
                'bar' => [
                    'baz' => [
                        new \stdClass,
                    ],
                ],
            ],
        ]);
        $builder->compile(self::generateCompilationFileName());
        $builder->build();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The file in which to compile the container must have a name that is a valid class name: foo-bar is not a valid PHP class name
     */
    public function the_compiled_filename_must_be_a_valid_class_name()
    {
        $builder = new ContainerBuilder;
        $builder->compile('/tmp/foo-bar.php');
        $builder->build();
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage You cannot set a definition at runtime on a compiled container. You can either put your definitions in a file, disable compilation or ->set() a raw value directly (PHP object, string, int, ...) instead of a PHP-DI definition.
     */
    public function entries_cannot_be_overridden_by_definitions_in_the_compiled_container()
    {
        $builder = new ContainerBuilder;
        $builder->compile(self::generateCompilationFileName());
        $builder->addDefinitions([
            'foo' => create(\stdClass::class),
        ]);
        $container = $builder->build();

        $container->set('foo', create(ContainerSetTest\Dummy::class));
    }
}

namespace DI\Test\IntegrationTest\CompiledContainerTest;

class Property
{
    public $foo;
}
