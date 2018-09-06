<?php
declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions\AutowireDefinition;

class Variadic
{
    public $values;

    public function __construct(...$values)
    {
        $this->values = $values;
    }
}