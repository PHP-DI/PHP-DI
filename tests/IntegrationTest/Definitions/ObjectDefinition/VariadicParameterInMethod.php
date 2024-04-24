<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions\ObjectDefinition;

final class VariadicParameterInMethod
{
    public A|null $a1;
    public B|null $b1;
    public C|null $c1;
    public A $a2;
    public B $b2;
    public C|null $c2;

    public function method1(A $a, B|C ...$v): void
    {
        $this->a1 = $a;
        $this->b1 = $v[0] ?? null;
        $this->c1 = $v[1] ?? null;
    }

    public function method2(A $a, B $b, C ...$v): void
    {
        $this->a2 = $a;
        $this->b2 = $b;
        $this->c2 = $v[0] ?? null;
    }
}
