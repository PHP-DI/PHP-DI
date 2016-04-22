<?php

namespace DI\Test\IntegrationTest\Interop\Fixture;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class ProviderB implements ServiceProvider
{
    public function getServices()
    {
        return [
            'b' => [ self::class, 'getB' ],
            'ba' => [ self::class, 'getBa' ],
            'overridden' => [ self::class, 'getOverridden' ],
            'extended' => [ self::class, 'getExtended' ],
            'no_previous' => [ self::class, 'getNoPrevious' ],
            'native' => [ self::class, 'getNative' ],
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
