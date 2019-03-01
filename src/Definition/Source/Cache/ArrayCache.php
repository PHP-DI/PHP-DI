<?php

namespace DI\Definition\Source\Cache;

use DI\Definition\Definition;

/**
 * Definition cache with uses an in-memory array.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 * @author Benjamin Zikarsky <benjamin.zikarsky@jaumo.com>
 */
class ArrayCache extends AbstractCache
{
    /**
     * @var Definition[]
     */
    private $cache = [];

    /** {@inheritdoc} */
    protected function fetch(string $name)
    {
        return isset($this->cache[$name]) ?? false;
    }

    /** {@inheritdoc} */
    protected function store(string $name, Definition $definition)
    {
        $this->cache[$name] = $definition;
    }

    /** {@inheritdoc} */
    public static function isSupported() : bool
    {
        return true;
    }
}
