<?php

namespace DI\Test\IntegrationTest\Interop\Fixture;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class ProviderA implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'a' => 'getA',
            'ab' => 'getAb',
            'overridden' => 'getOverridden',
            'extended' => 'getExtended',
            'native' => 'getNative',
            'DI\Test\IntegrationTest\Interop\Fixture\Object1' => 'getObject',
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
