<?php

declare(strict_types=1);

use DI\ContainerBuilder;

require_once __DIR__ . '/vendor/autoload.php';

class Foo
{
}
class Bar
{
}

$builder = new ContainerBuilder();
$builder->useAutowiring(false);
$builder->useAnnotations(false);
$builder->enableCompilation(__DIR__ . '/tmp', 'Factory');
$builder->addDefinitions(__DIR__ . '/factory/config.php');

$container = $builder->build();

$container->get('empty');
$container->get('container');
$container->get('entry');
$container->get('injected-entry');
