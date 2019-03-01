<?php

namespace DI\Definition\Source\Cache;

use DI\Definition\Definition;
use const PHP_SAPI;

/**
 * Definition cache with uses apcu as a caching backend.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 * @author Benjamin Zikarsky <benjamin.zikarsky@jaumo.com>
 */
class ApcuCache extends AbstractCache
{
    /**
     * @var string
     */
    const CACHE_KEY = 'php-di.definitions.';

    /** {@inheritdoc} */
    protected function fetch(string $name)
    {
        return apcu_fetch(self::CACHE_KEY . $name);
    }

    /** {@inheritdoc} */
    protected function store(string $name, Definition $definition)
    {
        apcu_store(self::CACHE_KEY . $name, $definition);
    }

    /** {@inheritdoc} */
    public static function isSupported() : bool
    {
        return function_exists('apcu_fetch')
            && ini_get('apc.enabled')
            && ! ('cli' === PHP_SAPI && ! ini_get('apc.enable_cli'));
    }
}
