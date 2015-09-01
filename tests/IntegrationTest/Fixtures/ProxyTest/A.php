<?php

namespace DI\Test\IntegrationTest\Fixtures\ProxyTest;

class A
{
    /**
     * @var B
     */
    private $b;

    public function __construct(B $b)
    {
        $this->b = $b;
    }

    /**
     * @return B
     */
    public function getB()
    {
        return $this->b;
    }
}
