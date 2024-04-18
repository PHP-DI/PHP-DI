<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Issues\Issue881;

final class ClassWithVariadicParameter
{
    public A $a;
    public B $b;
    public C $c;
    public A $a1;
    public B $b1;
    public C $c1;

    public function __construct(A|B|C ...$v)
    {
        $this->a = $v[2];
        $this->b = $v[1];
        $this->c = $v[0];
    }

    public function method(A $a, B|C ...$v): void
    {
        $this->a1 = $a;
        $this->b1 = $v[0];
        $this->c1 = $v[1];
    }
}
