<?php

namespace DI\Test\IntegrationTest\Interop\Fixture;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class ProviderA implements ServiceProvider
{
    public function getServices()
    {
        return [
            'a' => [__CLASS__, 'getA'],
            'ab' => [__CLASS__, 'getAb'],
            'overridden' => [__CLASS__, 'getOverridden'],
            'extended' => [__CLASS__, 'getExtended'],
            'native' => [__CLASS__, 'getNative'],
            'DI\Test\IntegrationTest\Interop\Fixture\Object1' => [__CLASS__, 'getObject'],
        ];
    }

    public static function getA()
    {
        return 'a';
    }

    public static function getAb(ContainerInterface $container)
    {
        return $container->get('a') . 'b';
    }

    public static function getOverridden()
    {
        return 'hello';
    }

    public static function getExtended()
    {
        return 'hello';
    }

    public static function getNative(ContainerInterface $container, callable $getPrevious = null)
    {
        return $getPrevious() . ' is';
    }

    public static function getObject(ContainerInterface $container, callable $getPrevious = null)
    {
        return new Object1('foo');
    }
}
