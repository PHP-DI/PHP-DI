<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Fixtures\InheritanceTest;

use DI\Annotation\Inject;

/**
 * Fixture class.
 */
abstract class BaseClass
{
    /**
     * @Inject
     */
    public Dependency $property1;

    public Dependency $property2;

    public Dependency $property3;

    public function __construct(Dependency $param1)
    {
        $this->property3 = $param1;
    }

    /**
     * @Inject
     */
    public function setProperty2(Dependency $property2)
    {
        $this->property2 = $property2;
    }
}
