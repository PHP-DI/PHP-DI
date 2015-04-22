<?php

use DI\ContainerBuilder;
use Doctrine\Common\Cache\ArrayCache;

require_once __DIR__ . '/vendor/autoload.php';

$builder = new ContainerBuilder();
$builder->useAutowiring(true);
$builder->useAnnotations(false);
$builder->setDefinitionCache(new ArrayCache());
$builder->addDefinitions(array(
    'link' => 'Hello',
));
$container = $builder->build();

for ($i = 0; $i < 100; $i++) {
    $container->call(function ($foo, $bar) {}, array(
        'foo',
        'bar',
    ));
}

for ($i = 0; $i < 100; $i++) {
    $container->call(function ($foo, $bar) {}, array(
        'foo' => 'foo',
        'bar' => 'bar',
    ));
}

for ($i = 0; $i < 100; $i++) {
    $container->call(function (stdClass $foo) {});
}

for ($i = 0; $i < 100; $i++) {
    $container->call(function ($foo, $bar) {}, array(
        'foo' => \DI\get('link'),
        'bar' => \DI\get('link'),
    ));
}
