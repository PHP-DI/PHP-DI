<?php

namespace DI\Test\IntegrationTest\Interop\Fixture;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class ProviderB implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'b' => 'getB',
            'ba' => 'getBa',
            'overridden' => 'getOverridden',
            'extended' => 'getExtended',
            'no_previous' => 'getNoPrevious',
            'native' => 'getNative',
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

    public static function getNoPrevious(ContainerInterface $container, $previous = null)
    {
        return $previous . ' world';
    }

    public static function getNative(ContainerInterface $container, $previous = null)
    {
        return $previous . ' awesome';
    }
}
