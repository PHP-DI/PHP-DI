<?php

namespace DI\Definition\Resolver;

use DI\Definition\InstanceDefinition;
use DI\DependencyException;
use Interop\Container\Definition\DefinitionInterface;
use Interop\Container\Exception\NotFoundException;

/**
 * Injects dependencies on an existing instance.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InstanceInjector extends ObjectCreator
{
    /**
     * Injects dependencies on an existing instance.
     *
     * @param InstanceDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(DefinitionInterface $definition, array $parameters = [])
    {
        try {
            $this->injectMethodsAndProperties($definition->getInstance(), $definition->getObjectDefinition());
        } catch (NotFoundException $e) {
            $message = sprintf(
                'Error while injecting dependencies into %s: %s',
                get_class($definition->getInstance()),
                $e->getMessage()
            );
            throw new DependencyException($message, 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isResolvable(DefinitionInterface $definition, array $parameters = [])
    {
        return true;
    }
}
