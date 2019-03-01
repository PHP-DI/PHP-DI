<?php

namespace DI\Definition\Source;

use DI\Definition\Source\Cache\ApcuCache;

/**
 * Decorator that caches another definition source.
 *
 * This class is deprecated in favour of `DI\Definition\Source\Cache\ApcuCache`.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 * @deprecated
 */
class SourceCache extends ApcuCache
{
    public function __construct(DefinitionSource $source)
    {
        parent::__construct($source);
    }
}
