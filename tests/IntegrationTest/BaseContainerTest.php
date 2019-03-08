<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\CompiledContainer;
use DI\Container;
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

    /**
     * Assert that the given entry is compiled when we are testing the compiled container.
     */
    protected static function assertEntryIsCompiled(Container $container, string $entry)
    {
        if (!$container instanceof CompiledContainer) {
            return;
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $compiledEntries = $container::METHOD_MAPPING;
        self::assertArrayHasKey($entry, $compiledEntries, sprintf('Entry "%s" is not compiled, it should exist as key in associative array: %s', $entry, print_r($compiledEntries, true)));
    }

    /**
     * Assert that the given entry is not compiled when we are testing the compiled container.
     */
    protected static function assertEntryIsNotCompiled(Container $container, string $entry)
    {
        if (!$container instanceof CompiledContainer) {
            return;
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $compiledEntries = $container::METHOD_MAPPING;
        self::assertArrayNotHasKey($entry, $compiledEntries, "Entry $entry is compiled");
    }
}
