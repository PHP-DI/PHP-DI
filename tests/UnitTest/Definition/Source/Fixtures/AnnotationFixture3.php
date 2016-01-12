<?php

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

class AnnotationFixture3
{
    /**
     * @Inject
     * @param AnnotationFixture2 $param1
     * @param string             $param2
     */
    public function method1($param1, $param2)
    {
    }
}
