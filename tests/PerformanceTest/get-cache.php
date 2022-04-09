<?php

declare(strict_types=1);
use DI\ContainerBuilder;
use DI\Test\PerformanceTest\Get\A;
use DI\Test\PerformanceTest\Get\B;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/get/GetFixture.php';

$compile = (($argv[1] ?? '') === '--use-cache');

if ($compile) {
    echo 'Using compiled container';
} else {
    echo 'Using not compiled container';
}

for ($i = 0; $i < 100; $i++) {
    $builder = new ContainerBuilder();
    $builder->useAutowiring(true);
    $builder->useAttributes(false);
    $builder->addDefinitions(__DIR__ . '/get/config.php');
    if ($compile) {
        $builder->enableCompilation(__DIR__ . '/tmp/', "Container$i");
    }
    $container = $builder->build();

    $container->get(A::class);
    $container->get(B::class);

    $container->get('object');
    $container->get('value');
    $container->get('string');
    $container->get('alias');
    $container->get('factory');
    $container->get('array');

    $container->get('object');
    $container->get('object');
}
