<?php

declare(strict_types=1);

namespace DI\Definition\Helper;

use DI\Definition\AutowireDefinition;
use DI\Definition\Definition;

/**
 * Helps defining how to create an instance of a class using autowiring.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AutowireDefinitionHelper extends CreateDefinitionHelper
{
    const DEFINITION_CLASS = AutowireDefinition::class;

    protected $useAnnotations;

    /**
     * Defines a value for a specific argument of the constructor.
     *
     * This method is usually used together with annotations or autowiring, when a parameter
     * is not (or cannot be) type-hinted. Using this method instead of constructor() allows to
     * avoid defining all the parameters (letting them being resolved using annotations or autowiring)
     * and only define one.
     *
     * @param string|int $parameter Parameter name of position for which the value will be given.
     * @param mixed $value Value to give to this parameter.
     *
     * @return $this
     */
    public function constructorParameter($parameter, $value)
    {
        $this->constructor[$parameter] = $value;

        return $this;
    }

    /**
     * Defines a method to call and a value for a specific argument.
     *
     * This method is usually used together with annotations or autowiring, when a parameter
     * is not (or cannot be) type-hinted. Using this method instead of method() allows to
     * avoid defining all the parameters (letting them being resolved using annotations or
     * autowiring) and only define one.
     *
     * If multiple calls to the method have been configured already (e.g. in a previous definition)
     * then this method only overrides the parameter for the *first* call.
     *
     * @param string $method Name of the method to call.
     * @param string|int $parameter Parameter name of position for which the value will be given.
     * @param mixed $value Value to give to this parameter.
     *
     * @return $this
     */
    public function methodParameter(string $method, $parameter, $value)
    {
        // Special case for the constructor
        if ($method === '__construct') {
            $this->constructor[$parameter] = $value;

            return $this;
        }

        if (! isset($this->methods[$method])) {
            $this->methods[$method] = [0 => []];
        }

        $this->methods[$method][0][$parameter] = $value;

        return $this;
    }

    /**
     * Define if entry should use annotation reader for reading dependencies.
     * This is turned off by default if autowire() helper is used, and turned on if entry is not defined explicitly in the di config.
     * @param bool $useAnnotations
     * @return $this
     */
    public function useAnnotations(bool $useAnnotations = true)
    {
        $this->useAnnotations = $useAnnotations;

        return $this;
    }

    /**
     * @return AutowireDefinition
     */
    public function getDefinition(string $entryName) : Definition
    {
        /** @var AutowireDefinition $definition */
        $definition = parent::getDefinition($entryName);
        if ($this->useAnnotations !== null) {
            $definition->useAnnotations($this->useAnnotations);
        }

        return $definition;
    }
}
