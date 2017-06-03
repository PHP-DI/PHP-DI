<?php

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;

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
}
