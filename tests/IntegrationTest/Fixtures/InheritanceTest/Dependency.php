<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Fixtures\InheritanceTest;

/**
 * Fixture class.
 */
class Dependency
{
    public function getBoolean(): bool
    {
        return true;
    }
}
