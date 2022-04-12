<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Fixtures;

use DI\Attribute\Inject;

/**
 * Fixture class for testing circular dependencies.
 */
class Class2CircularDependencies
{
    #[Inject]
    public Class1CircularDependencies $class1;
}
