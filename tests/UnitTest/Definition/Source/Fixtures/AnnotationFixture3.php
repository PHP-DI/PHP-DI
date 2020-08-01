<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

class AnnotationFixture3
{
    /**
     * @Inject
     */
    public function method1(AnnotationFixture2 $param1, bool $param2)
    {
    }
}
