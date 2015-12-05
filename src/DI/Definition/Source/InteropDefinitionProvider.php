<?php

namespace DI\Definition\Source;

use DI\Definition\ObjectDefinition;
use Interop\Container\Definition\DefinitionInterface;
use Interop\Container\Definition\DefinitionProviderInterface;

/**
 * Reads DI definitions from a PHP array.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InteropDefinitionProvider implements DefinitionSource
{
    /**
     * @var DefinitionProviderInterface
     */
    private $definitionProvider;

    /**
     * @var DefinitionInterface[]|null
     */
    private $definitions;

    public function __construct(DefinitionProviderInterface $definitionProvider)
    {
        $this->definitionProvider = $definitionProvider;
    }

    public function getDefinition($name)
    {
        if ($this->definitions === null) {
            $this->definitions = $this->definitionProvider->getDefinitions();
        }

        if (isset($this->definitions[$name])) {
            return $this->definitions[$name];
        }

        return null;
    }
}
