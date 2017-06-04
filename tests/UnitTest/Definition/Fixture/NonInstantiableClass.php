<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Fixture;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class NonInstantiableClass
{
    private function __construct()
    {
    }
}
