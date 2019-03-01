<?php

namespace DI\Definition\Source\Cache;

use DI\Definition\Source\DefinitionSource;
use DI\Definition\Source\MutableDefinitionSource;

/**
 * A definition cache can decorate another definition source to improve lookup performance.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 * @author Benjamin Zikarsky <benjamin.zikarsky@jaumo.com>
 */
interface Cache extends DefinitionSource, MutableDefinitionSource
{
    /**
     * Create a Cache instance which decorates the given definition source.
     *
     * @param DefinitionSource $source
     * @return Cache
     */
    public static function create(DefinitionSource $source) : self;

    /**
     * Test that the Cache implementation is supported by the PHP runtime.
     *
     * This method should test for required extensions, etc.
     *
     * @return bool
     */
    public static function isSupported() : bool;
}
