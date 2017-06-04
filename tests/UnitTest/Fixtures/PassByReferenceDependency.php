<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Fixtures;

use stdClass;

class PassByReferenceDependency
{
    public function __construct(stdClass &$object)
    {
        $object->foo = 'bar';
    }
}
