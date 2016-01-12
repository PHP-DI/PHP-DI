<?php

namespace DI\Test\UnitTest\Annotation\Fixtures;

use Foo\Bar\SomeUnknownImportedAnnotation;

class MixedAnnotationsFixture
{
    /**
     * @Inject("foo")
     * @SomeRandomAnnotation
     * @SomeUnknownImportedAnnotation
     */
    protected $property1;
}
