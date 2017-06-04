<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Annotation\Fixtures;

class NonImportedInjectFixture
{
    /**
     * @Inject("foo")
     */
    protected $property1;
}
