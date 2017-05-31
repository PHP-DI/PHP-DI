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
    public static function setUpBeforeClass()
    {
        if (file_exists(__DIR__ . '/tmp/CompiledContainer.php')) {
            unlink(__DIR__ . '/tmp/CompiledContainer.php');
        }

        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        if (file_exists(__DIR__ . '/tmp/CompiledContainer.php')) {
            unlink(__DIR__ . '/tmp/CompiledContainer.php');
        }

        parent::setUp();
    }

    public function provideContainer() : array
    {
        if (file_exists(__DIR__ . '/tmp/CompiledContainer.php')) {
            unlink(__DIR__ . '/tmp/CompiledContainer.php');
        }

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
