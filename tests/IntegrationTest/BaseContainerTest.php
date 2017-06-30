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
    const COMPILATION_DIR = __DIR__ . '/tmp';

    public static function setUpBeforeClass()
    {
        // Clear all files
        array_map('unlink', glob(self::COMPILATION_DIR . '/*'));

        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        // Clear all files
        array_map('unlink', glob(self::COMPILATION_DIR . '/*'));

        parent::setUp();
    }

    public function provideContainer() : array
    {
        // Clear all files
        array_map('unlink', glob(self::COMPILATION_DIR . '/*'));

        return [
            'not-compiled' => [
                new ContainerBuilder,
            ],
            'compiled' => [
                (new ContainerBuilder)->enableCompilation(
                    self::COMPILATION_DIR,
                    self::generateCompiledClassName()
                ),
            ],
        ];
    }

    protected static function generateCompiledClassName()
    {
        return 'Container' . uniqid();
    }
}
