<?php

declare(strict_types=1);

namespace DI\Definition\Resolver;

use DI\Definition\DefinitionInterface;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Exception\InvalidDefinition;

/**
 * Resolves a environment variable definition to a value.
 *
 * @template-implements DefinitionResolverInterface<EnvironmentVariableDefinition>
 *
 * @author James Harris <james.harris@icecave.com.au>
 */
class EnvironmentVariableResolver implements DefinitionResolverInterface
{
    /** @var callable */
    private $variableReader;

    public function __construct(
        private DefinitionResolverInterface $definitionResolver,
                                            $variableReader = null
    ) {
        $this->variableReader = $variableReader ?? [$this, 'getEnvVariable'];
    }

    /**
     * Resolve an environment variable definition to a value.
     *
     * @param EnvironmentVariableDefinition $definition
     */
    public function resolve(DefinitionInterface $definition, array $parameters = []) : mixed
    {
        $value = call_user_func($this->variableReader, $definition->getVariableName());

        if (false !== $value) {
            return $value;
        }

        if (!$definition->isOptional()) {
            throw new InvalidDefinition(sprintf(
                "The environment variable '%s' has not been defined",
                $definition->getVariableName()
            ));
        }

        $value = $definition->getDefaultValue();

        // Nested definition
        if ($value instanceof DefinitionInterface) {
            return $this->definitionResolver->resolve($value);
        }

        return $value;
    }

    public function isResolvable(DefinitionInterface $definition, array $parameters = []) : bool
    {
        return true;
    }

    protected function getEnvVariable(string $variableName)
    {
        return $_ENV[$variableName] ?? $_SERVER[$variableName] ?? getenv($variableName);
    }
}
