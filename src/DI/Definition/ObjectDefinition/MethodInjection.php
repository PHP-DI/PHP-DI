<?php

namespace DI\Definition\ObjectDefinition;

use DI\Definition\Definition;

/**
 * Describe an injection in an object method.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class MethodInjection implements Definition
{
    /**
     * @var string
     */
    private $methodName;

    /**
     * @var array
     */
    private $parameters = [];

    public function __construct(string $methodName, array $parameters = [])
    {
        $this->methodName = (string) $methodName;
        $this->parameters = $parameters;
    }

    public static function constructor(array $parameters = [])
    {
        return new self('__construct', $parameters);
    }

    public function getMethodName() : string
    {
        return $this->methodName;
    }

    public function getParameters() : array
    {
        return $this->parameters;
    }

    /**
     * Replace the parameters of the definition by a new array of parameters.
     */
    public function replaceParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function merge(MethodInjection $definition)
    {
        // In case of conflicts, the current definition prevails.
        $this->parameters = $this->parameters + $definition->parameters;
    }

    public function getName() : string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return sprintf('method(%s)', $this->methodName);
    }
}
