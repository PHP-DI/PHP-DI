<?php

declare(strict_types=1);

namespace DI\Definition\Source;

use DI\Definition\AutowireDefinition;
use DI\Definition\Exception\DefinitionException;

/**
 * Implementation used when autowiring is completely disabled.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class NoAutowiring implements Autowiring
{
    public function autowire(string $name, AutowireDefinition $definition = null)
    {
        throw new DefinitionException(sprintf(
            'Cannot autowire entry "%s" because autowiring is disabled',
            $name
        ));
    }
}
