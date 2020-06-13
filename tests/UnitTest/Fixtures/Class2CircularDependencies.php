<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Fixtures;

use DI\Annotation\Inject;

/**
 * Fixture class for testing circular dependencies.
 */
class Class2CircularDependencies
{
    /**
     * @Inject
     * @var \DI\Test\UnitTest\Fixtures\Class1CircularDependencies
     */
    public $class1;
}
