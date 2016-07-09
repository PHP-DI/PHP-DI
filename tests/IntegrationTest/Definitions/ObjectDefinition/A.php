<?php


namespace DI\Test\IntegrationTest\Definitions\ObjectDefinition;


class A
{
    public $b;

    public function __construct(B $b)
    {
        $this->b = $b;
    }
}