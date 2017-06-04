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
    const COMPILED_CONTAINER_DIRECTORY = __DIR__ . '/tmp';

    public static function setUpBeforeClass()
    {
        // Clear all files
        array_map('unlink', glob(self::COMPILED_CONTAINER_DIRECTORY . '/*'));

        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        // Clear all files
        array_map('unlink', glob(self::COMPILED_CONTAINER_DIRECTORY . '/*'));

        parent::setUp();
    }

    public function provideContainer() : array
    {
        // Clear all files
        array_map('unlink', glob(self::COMPILED_CONTAINER_DIRECTORY . '/*'));

        return [
            'not-compiled' => [
                new ContainerBuilder,
            ],
            'compiled' => [
                (new ContainerBuilder)->compile(self::generateCompilationFileName()),
            ],
        ];
    }

    protected static function generateCompilationFileName()
    {
        return self::COMPILED_CONTAINER_DIRECTORY . '/Container' . uniqid() . '.php';
    }
}
