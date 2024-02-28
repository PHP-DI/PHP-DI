<?php

declare(strict_types=1);

namespace DI\Definition\Resolver;

use DI\Definition\DefinitionInterface;
use DI\Definition\Exception\InvalidDefinition;
use DI\DependencyException;

/**
 * Resolves a definition to a value.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 *
 * @template T of DefinitionInterface
 */
interface DefinitionResolverInterface
{
    /**
     * Resolve a definition to a value.
     *
     * @param DefinitionInterface $definition Object that defines how the value should be obtained.
     * @psalm-param T $definition
     * @param array      $parameters Optional parameters to use to build the entry.
     * @return mixed Value obtained from the definition.
     *
     * @throws InvalidDefinition If the definition cannot be resolved.
     * @throws DependencyException
     */
    public function resolve(DefinitionInterface $definition, array $parameters = []) : mixed;

    /**
     * Check if a definition can be resolved.
     *
     * @param DefinitionInterface $definition Object that defines how the value should be obtained.
     * @psalm-param T $definition
     * @param array      $parameters Optional parameters to use to build the entry.
     */
    public function isResolvable(DefinitionInterface $definition, array $parameters = []) : bool;
}
