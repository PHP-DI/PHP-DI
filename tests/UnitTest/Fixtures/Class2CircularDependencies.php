<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Fixtures;

/**
 * Fixture class for testing circular dependencies.
 */
class Class2CircularDependencies
{
    /**
     * @Inject
     * @var Class1CircularDependencies
     */
    public Class1CircularDependencies $class1;
}
