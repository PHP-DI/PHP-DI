<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Annotation\Fixtures;

use DI\Annotation\Inject;
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
