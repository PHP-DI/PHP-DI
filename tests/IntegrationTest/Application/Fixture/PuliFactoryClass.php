<?php

namespace DI\Test\IntegrationTest\Application\Fixture;

use Puli\Discovery\InMemoryDiscovery;
use Puli\Repository\InMemoryRepository;

class PuliFactoryClass
{
    /**
     * @var InMemoryRepository
     */
    public static $repository;

    /**
     * @var InMemoryDiscovery
     */
    public static $discovery;

    public function createRepository()
    {
        return self::$repository;
    }

    public function createDiscovery()
    {
        return self::$discovery;
    }
}
