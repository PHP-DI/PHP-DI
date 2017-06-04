<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Fixtures\InheritanceTest;

use DI\Annotation\Inject;

/**
 * Fixture class.
 */
class SubClass extends BaseClass
{
    /**
     * @Inject
     * @var Dependency
     */
    public $property4;
}
