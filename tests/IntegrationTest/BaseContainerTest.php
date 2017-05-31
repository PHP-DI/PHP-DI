<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
abstract class BaseContainerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        if (file_exists(__DIR__ . '/tmp/CompiledContainer.php')) {
            unlink(__DIR__ . '/tmp/CompiledContainer.php');
        }
    }

    public function provideContainer() : array
    {
        return [
            'not-compiled' => [
                new ContainerBuilder,
            ],
            'compiled' => [
                (new ContainerBuilder)->compile(__DIR__ . '/tmp/CompiledContainer.php'),
            ],
        ];
    }
}
