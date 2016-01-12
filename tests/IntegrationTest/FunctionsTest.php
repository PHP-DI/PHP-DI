<?php

namespace DI\Test\IntegrationTest;

/**
 * @coversNothing
 */
class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function including_functions_twice_should_not_error()
    {
        include __DIR__ . '/../../src/DI/functions.php';
        include __DIR__ . '/../../src/DI/functions.php';
    }
}
