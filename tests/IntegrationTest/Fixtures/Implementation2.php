<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Fixtures;

/**
 * Fixture class.
 */
class Implementation2 implements Interface2
{
    /**
     * @var Interface1
     */
    public $dependency;

    public function __construct(Interface1 $dependency) {
        $this->dependency = $dependency;
    }
}
