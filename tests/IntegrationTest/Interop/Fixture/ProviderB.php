<?php

namespace DI\Test\IntegrationTest\Interop\Fixture;

use DI\ServiceProvider;
use Interop\Container\ContainerInterface;

class ProviderB implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'b' => 'getB',
            'ba' => 'getBa',
            'overridden' => 'getOverridden',
            'extended' => 'getExtended',
        ];
    }

    public static function getB()
    {
        return 'b';
    }

    public static function getBa(ContainerInterface $container)
    {
        return $container->get('b') . $container->get('a');
    }

    public static function getOverridden()
    {
        return 'bye';
    }

    public static function getExtended(ContainerInterface $container, $previous = null)
    {
        return $previous . ' world';
    }
}
