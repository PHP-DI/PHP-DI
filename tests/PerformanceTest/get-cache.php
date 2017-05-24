<?php

use DI\ContainerBuilder;
use DI\Test\PerformanceTest\Get\A;
use DI\Test\PerformanceTest\Get\B;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/get/GetFixture.php';

$useCache = (($argv[1] ?? '') === '--use-cache');

if ($useCache) {
    echo 'Using cache';
} else {
    echo 'Not using cache';
}

for ($i = 0; $i < 100; $i++) {
    $builder = new ContainerBuilder();
    $builder->useAutowiring(true);
    $builder->useAnnotations(false);
    $builder->addDefinitions(__DIR__ . '/get/config.php');
    if ($useCache) {
        $builder->setDefinitionCache(new \Symfony\Component\Cache\Simple\ApcuCache());
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
