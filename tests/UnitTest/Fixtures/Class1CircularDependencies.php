<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Fixtures;

/**
 * Fixture class for testing circular dependencies.
 */
class Class1CircularDependencies
{
    /**
     * @Inject
     * @var \DI\Test\UnitTest\Fixtures\Class2CircularDependencies
     */
    public $class2;
}
