<?php

declare(strict_types=1);

namespace DI\Definition\Resolver;

use DI\Definition\ArrayDefinition;
use DI\Definition\DefinitionInterface;
use DI\DependencyException;
use Exception;

/**
 * Resolves an array definition to a value.
 *
 * @template-implements DefinitionResolverInterface<ArrayDefinition>
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayResolver implements DefinitionResolverInterface
{
    /**
     * @param DefinitionResolverInterface $definitionResolver Used to resolve nested definitions.
     */
    public function __construct(
        private DefinitionResolverInterface $definitionResolver
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * Resolve an array definition to a value.
     *
     * An array definition can contain simple values or references to other entries.
     *
     * @param ArrayDefinition $definition
     */
    public function resolve(DefinitionInterface $definition, array $parameters = []) : array
    {
        $values = $definition->getValues();

        // Resolve nested definitions
        array_walk_recursive($values, function (& $value, $key) use ($definition) {
            if ($value instanceof DefinitionInterface) {
                $value = $this->resolveDefinition($value, $definition, $key);
            }
        });

        return $values;
    }

    public function isResolvable(DefinitionInterface $definition, array $parameters = []) : bool
    {
        return true;
    }

    /**
     * @throws DependencyException
     */
    private function resolveDefinition(DefinitionInterface $value, ArrayDefinition $definition, int|string $key) : mixed
    {
        try {
            return $this->definitionResolver->resolve($value);
        } catch (DependencyException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new DependencyException(sprintf(
                'Error while resolving %s[%s]. %s',
                $definition->getName(),
                $key,
                $e->getMessage()
            ), 0, $e);
        }
    }
}
