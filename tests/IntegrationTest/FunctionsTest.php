<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function including_functions_twice_should_not_error()
    {
        include __DIR__ . '/../../src/functions.php';
        include __DIR__ . '/../../src/functions.php';
    }
}
