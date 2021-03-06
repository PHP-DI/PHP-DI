<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Attributes\Fixtures;

use DI\Attribute\Inject;

class InjectFixture
{
    #[Inject('foo')]
    protected $property1;

    /**
     * @var Dependency
     */
    #[Inject]
    protected $property2;

    #[Inject(name: 'foo')]
    protected $property3;

    #[Inject]
    public function method1()
    {
    }

    #[Inject(['foo', 'bar'])]
    public function method2($str1, $str2)
    {
    }

    #[Inject(['str1' => 'foo'])]
    public function method3($str1)
    {
    }

    #[Inject(['str1' => []])]
    public function method4($str1)
    {
    }
}
