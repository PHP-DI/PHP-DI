<?php

namespace DI\Test\UnitTest\Annotation\Fixtures;

class NonImportedInjectFixture
{
    /**
     * @Inject("foo")
     */
    protected $property1;
}
