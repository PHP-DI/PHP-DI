<?php


namespace DI\Test\IntegrationTest\Definitions\ObjectDefinition;


class C
{
    public $a;

    public function __construct(A $a)
    {
        $this->a = $a;
    }
}