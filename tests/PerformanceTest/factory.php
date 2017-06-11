<?php

declare(strict_types=1);

use DI\ContainerBuilder;

require_once __DIR__ . '/vendor/autoload.php';

class Foo {}
class Bar {}

$builder = new ContainerBuilder();
$builder->useAutowiring(false);
$builder->useAnnotations(false);
$builder->compile(__DIR__ . '/tmp/factory.php');
$builder->addDefinitions(__DIR__ . '/factory/config.php');

$container = $builder->build();

$container->get('empty');
$container->get('container');
$container->get('entry');
$container->get('injected-entry');
