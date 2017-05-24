<?php

namespace DI\Definition\Source;

use DI\Definition\CacheableDefinition;
use DI\Definition\Definition;

/**
 * Caches another definition source.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class CachedDefinitionSource implements DefinitionSource
{
    /**
     * @var string
     */
    const CACHE_KEY = 'php-di.definitions';

    /**
     * @var DefinitionSource
     */
    private $cachedSource;

    /**
     * Definitions loaded from the cache (or null if not loaded yet).
     *
     * @var Definition[]|null
     */
    private $cachedDefinitions;

    public function __construct(DefinitionSource $cachedSource)
    {
        $this->cachedSource = $cachedSource;
    }

    public function getDefinition(string $name)
    {
        if ($this->cachedDefinitions === null) {
            $this->cachedDefinitions = apcu_fetch(self::CACHE_KEY) ?: [];
        }

        // Look in cache
        $definition = $this->cachedDefinitions[$name] ?? false;

        if ($definition === false) {
            $definition = $this->cachedSource->getDefinition($name);

            // Update the cache
            if ($definition === null || ($definition instanceof CacheableDefinition)) {
                $this->cachedDefinitions[$name] = $definition;
                apcu_store(self::CACHE_KEY, $this->cachedDefinitions);
            }
        }

        return $definition;
    }

    public static function isSupported() : bool
    {
        return function_exists('apcu_fetch')
            && ini_get('apc.enabled')
            && !('cli' === PHP_SAPI && !ini_get('apc.enable_cli'));
    }
}
