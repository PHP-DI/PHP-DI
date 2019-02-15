<?php

declare(strict_types=1);

namespace DI\Proxy;

class LazyLoadingValueHolderFactory extends \ProxyManager\Factory\LazyLoadingValueHolderFactory
{
    public function generateProxyClassToFile(string $className, array $proxyOptions = []) : string
    {
        return $this->generateProxy($className, $proxyOptions);
    }
}
