<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions\ObjectDefinition;

class Class1
{
    public $count = 0;
    public $items = [];

    public function increment()
    {
        $this->count++;
    }

    public function add($item)
    {
        $this->items[] = $item;
    }
}
