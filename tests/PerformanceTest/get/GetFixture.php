<?php

namespace DI\Test\PerformanceTest\Get;

class GetFixture
{
    public function __construct()
    {
    }
}

class A
{
    public function __construct(B $b, C $c, $value)
    {
    }
}

class B
{
    public function __construct(D $d)
    {
    }

    public function setValue($value, $value2)
    {
    }
}

class C
{
    public function __construct(E $e)
    {
    }
}

class D
{
    public function __construct(F $f)
    {
    }
}

class E
{
}

class F
{
}
