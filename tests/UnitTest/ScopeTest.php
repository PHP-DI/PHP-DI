<?php

namespace DI\Test\UnitTest;

use DI\Scope;

/**
 * @covers \DI\Scope
 */
class ScopeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_provide_prototype_scope()
    {
        $this->assertEquals(Scope::PROTOTYPE, Scope::PROTOTYPE());
    }

    /**
     * @test
     */
    public function should_provide_singleton_scope()
    {
        $this->assertEquals(Scope::SINGLETON, Scope::SINGLETON());
    }
}
