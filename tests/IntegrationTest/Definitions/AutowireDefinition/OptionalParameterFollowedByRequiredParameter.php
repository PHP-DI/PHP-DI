<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions\AutowireDefinition;

class OptionalParameterFollowedByRequiredParameter
{
    public $first;
    public $second;

    public function __construct($first, \stdClass $second)
    {
        $this->first = $first;
        $this->second = $second;
    }
}
