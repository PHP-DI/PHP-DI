<?php

namespace DI\Test\UnitTest\Fixtures;

use Exception;

/**
 * Fixture class for testing Container::newInstanceWithoutConstructor
 */
class NewInstanceWithoutConstructor
{

    /**
     * If the constructor is called, it will throw an exception
     */
    public function __construct()
    {
        throw new Exception("The constructor is called");
    }

}
