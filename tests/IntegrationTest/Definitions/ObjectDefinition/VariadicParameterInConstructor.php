<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions\ObjectDefinition;

final class VariadicParameterInConstructor
{
    public A|null $a;
    public B|null $b;
    public C|null $c;

    public function __construct(A $a, B|C ...$v)
    {
        $this->a = $a;
        $this->b = $v[0] ?? null;
        $this->c = $v[1] ?? null;
    }
}
