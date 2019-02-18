<?php

declare(strict_types=1);

namespace DI\Proxy;

/**
 * Class LazyLoadingValueHolderFactory
 *
 * To expose the least amount of the ProxyManager's internals we are extending the LazyLoadingValueHolderFactory
 * The protected `generateProxy` method will generate AND write the proxy classes to disk
 *
 */
class LazyLoadingValueHolderFactory extends \ProxyManager\Factory\LazyLoadingValueHolderFactory
{
    public function generateProxyClassToFile(string $className, array $proxyOptions = []) : string
    {
        return $this->generateProxy($className, $proxyOptions);
    }
}
