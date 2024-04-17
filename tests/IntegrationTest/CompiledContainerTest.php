<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use function DI\autowire;
use DI\ContainerBuilder;
use function DI\create;
use DI\Definition\Exception\InvalidDefinition;
use function DI\get;
use DI\DependencyException;

/**
 * Tests specific to the compiled container.
 */
class CompiledContainerTest extends BaseContainerTest
{
    /** @test */
    public function the_same_container_can_be_recreated_multiple_times()
    {
        $builder = new ContainerBuilder;
        $builder->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
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
        $compiledContainerClass = self::generateCompiledClassName();

        // Create a first compiled container in the file
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);
        $builder->enableCompilation(self::COMPILATION_DIR, $compiledContainerClass);
        $builder->build();

        // Create a second compiled container in the same file but with a DIFFERENT configuration
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            'foo' => 'DIFFERENT',
        ]);
        $builder->enableCompilation(self::COMPILATION_DIR, $compiledContainerClass);
        $container = $builder->build();

        // The second container is actually using the config of the first because the container was already compiled
        // (the compiled file already existed so the second container did not recompile into it)
        // This behavior is obvious for performance reasons.
        self::assertEquals('bar', $container->get('foo'));
    }

    /**
     * @test
     */
    public function anonymous_classes_cannot_be_compiled()
    {
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage('Entry "foo" cannot be compiled: anonymous classes cannot be compiled');
        $class = get_class(new class() {
        });

        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            'foo' => create($class),
        ]);
        $builder->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $builder->build();
    }

    /**
     * @test
     */
    public function object_nested_in_other_definitions_cannot_be_compiled()
    {
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage('Entry "stdClass" cannot be compiled: An object was found but objects cannot be compiled');
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            \stdClass::class => create()
                ->property('foo', new \stdClass),
        ]);
        $builder->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $builder->build();
    }

    /**
     * @test
     */
    public function object_nested_in_arrays_cannot_be_compiled()
    {
        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('Error while compiling foo. Error while compiling <nested definition>. Error while compiling <nested definition>. An object was found but objects cannot be compiled');
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
        $builder->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $builder->build();
    }

    /**
     * @test
     */
    public function entries_cannot_be_overridden_by_definitions_in_the_compiled_container()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('You cannot set a definition at runtime on a compiled container. You can either put your definitions in a file, disable compilation or ->set() a raw value directly (PHP object, string, int, ...) instead of a PHP-DI definition.');
        $builder = new ContainerBuilder;
        $builder->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $builder->addDefinitions([
            'foo' => create(\stdClass::class),
        ]);
        $container = $builder->build();

        $container->set('foo', create(ContainerSetTest\Dummy::class));
    }

    /**
     * @test
     */
    public function compiling_to_an_invalid_class_name_throws_an_error()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The container cannot be compiled: `123-abc` is not a valid PHP class name');
        $builder = new ContainerBuilder;
        $builder->enableCompilation(self::COMPILATION_DIR, '123-abc');
        $builder->build();
    }

    /**
     * @test
     */
    public function the_compiled_container_can_extend_a_custom_class()
    {
        $builder = new ContainerBuilder;
        $builder->enableCompilation(
            self::COMPILATION_DIR,
            self::generateCompiledClassName(),
            // Customize the parent class
            CompiledContainerTest\CustomParentContainer::class
        );
        $container = $builder->build();

        self::assertInstanceOf(CompiledContainerTest\CustomParentContainer::class, $container);
    }

    /**
     * @test
     */
    public function proxy_classes_can_be_pregenerated_at_compile_time()
    {
        $builder = new ContainerBuilder;
        $builder->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $builder->writeProxiesToFile(true, self::COMPILATION_DIR);
        $builder->addDefinitions([
          'foo' => create(\stdClass::class)->lazy(),
          'bar' => autowire(CompiledContainerTest\ConstructorWithAbstractClassTypehint::class)->lazy(),
        ]);
        $builder->build();

        $countProxyClasses = count(glob(self::COMPILATION_DIR . '/ProxyManagerGeneratedProxy*'));

        $this->assertEquals(2, $countProxyClasses);
    }

    /**
     * @test
     * @see https://github.com/PHP-DI/PHP-DI/issues/881
     */
    public function works_with_variadic_parameters()
    {
        $builder = new ContainerBuilder();
        $builder->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $builder->addDefinitions([
            'foo' => create(CompiledContainerTest\WithVariadicParameter::class)
                ->constructor(
                    get(CompiledContainerTest\C::class),
                    get(CompiledContainerTest\B::class),
                    get(CompiledContainerTest\A::class),
                )
                ->method(
                    'method',
                    get(CompiledContainerTest\A::class),
                    get(CompiledContainerTest\B::class),
                    get(CompiledContainerTest\C::class),
                ),
        ]);

        $container = $builder->build();
        $instance = $container->get('foo');

        self::assertInstanceOf(CompiledContainerTest\WithVariadicParameter::class, $instance);
        self::assertNotEmpty($instance->a);
        self::assertNotEmpty($instance->b);
        self::assertNotEmpty($instance->c);
        self::assertNotEmpty($instance->a1);
        self::assertNotEmpty($instance->b1);
        self::assertNotEmpty($instance->c1);
    }

    /**
     * @test
     * @see https://github.com/PHP-DI/PHP-DI/issues/565
     */
    public function recursively_compiles_referenced_definitions_found()
    {
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            'foo' => create(CompiledContainerTest\Property::class)
                ->property('foo', get(CompiledContainerTest\ConstructorWithTypehint::class)),
        ]);
        $builder->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $this->assertEntryIsCompiled($builder->build(), CompiledContainerTest\ConstructorWithTypehint::class);
        // Dependency of a dependency
        $this->assertEntryIsCompiled($builder->build(), CompiledContainerTest\ConstructorWithAnotherTypehint::class);
    }

    /**
     * @test
     * @see https://github.com/PHP-DI/PHP-DI/issues/567
     */
    public function invalid_definitions_referenced_in_the_configuration_throw_an_error()
    {
        $message = <<<MESSAGE
Entry "DI\Test\IntegrationTest\CompiledContainerTest\AbstractClass" cannot be compiled: the class is not instantiable
Full definition:
Object (
    class = #NOT INSTANTIABLE# DI\Test\IntegrationTest\CompiledContainerTest\AbstractClass
    lazy = false
)
MESSAGE;
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage($message);
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            CompiledContainerTest\ConstructorWithAbstractClassTypehint::class => autowire(),
            CompiledContainerTest\AbstractClass::class => autowire(),
        ]);
        $builder->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $builder->build();
    }

    /**
     * @test
     * @see https://github.com/PHP-DI/PHP-DI/issues/567
     */
    public function invalid_definitions_transitively_referenced_are_skipped_and_do_not_throw_an_error()
    {
        $builder = new ContainerBuilder;
        $builder->addDefinitions([
            CompiledContainerTest\ConstructorWithAbstractClassTypehint::class => autowire(),
        ]);
        $builder->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $builder->build();
        $this->assertEntryIsNotCompiled($builder->build(), CompiledContainerTest\AbstractClass::class);
    }
}

namespace DI\Test\IntegrationTest\CompiledContainerTest;

class Property
{
    public $foo;
}

class CustomParentContainer extends \DI\Container
{
}

class ConstructorWithTypehint
{
    public function __construct(ConstructorWithAnotherTypehint $param)
    {

    }
}

class ConstructorWithAnotherTypehint
{
    public function __construct(\stdClass $param)
    {

    }
}

class ConstructorWithAbstractClassTypehint
{
    public function __construct(AbstractClass $param)
    {

    }
}

abstract class AbstractClass
{
}

class A
{
}

class B
{
}

class C
{
}

class WithVariadicParameter
{
    public A $a;
    public B $b;
    public C $c;
    public A $a1;
    public B $b1;
    public C $c1;

    public function __construct(A|B|C ...$v)
    {
        $this->a = $v[2];
        $this->b = $v[1];
        $this->c = $v[0];
    }

    public function method(A $a, B|C ...$v): void
    {
        $this->a1 = $a;
        $this->b1 = $v[0];
        $this->c1 = $v[1];
    }
}
