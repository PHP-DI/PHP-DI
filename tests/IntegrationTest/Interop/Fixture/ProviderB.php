<?php

namespace DI\Test\IntegrationTest\Interop\Fixture;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class ProviderB implements ServiceProvider
{
    public function getServices()
    {
        return [
            'b' => [__CLASS__, 'getB'],
            'ba' => [__CLASS__, 'getBa'],
            'overridden' => [__CLASS__, 'getOverridden'],
            'extended' => [__CLASS__, 'getExtended'],
            'no_previous' => [__CLASS__, 'getNoPrevious'],
            'native' => [__CLASS__, 'getNative'],
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

    public static function getExtended(ContainerInterface $container, callable $getPrevious = null)
    {
        return $getPrevious() . ' world';
    }

    public static function getNoPrevious(ContainerInterface $container, callable $getPrevious = null)
    {
        return $getPrevious;
    }

    public static function getNative(ContainerInterface $container, callable $getPrevious = null)
    {
        return $getPrevious() . ' awesome';
    }
}
