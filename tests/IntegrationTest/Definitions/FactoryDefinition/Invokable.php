<?php

namespace DI\Test\IntegrationTest\Definitions\FactoryDefinition;

use Interop\Container\ContainerInterface;

class Invokable
{
    public function __invoke(ContainerInterface $container)
    {
        return 'bar';
    }
}
