<?php

namespace DI\Test\IntegrationTest\Interop\Fixture;

use DI\ServiceProvider;
use Interop\Container\ContainerInterface;

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

    public static function getNative(ContainerInterface $container, $previous = null)
    {
        return $previous . ' is';
    }
}
