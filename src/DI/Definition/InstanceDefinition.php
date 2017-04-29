<?php

namespace DI\Definition;

use DI\Scope;

/**
 * Defines injections on an existing class instance.
 *
 * @since  5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InstanceDefinition implements Definition
{
    /**
     * Instance on which to inject dependencies.
     *
     * @var object
     */
    private $instance;

    /**
     * @var ObjectDefinition
     */
    private $objectDefinition;

    /**
     * @param object $instance
     */
    public function __construct($instance, ObjectDefinition $objectDefinition)
    {
        $this->instance = $instance;
        $this->objectDefinition = $objectDefinition;
    }

    public function getName() : string
    {
        // Name are superfluous for instance definitions
        return '';
    }

    public function getScope() : string
    {
        return Scope::PROTOTYPE;
    }

    /**
     * @return object
     */
    public function getInstance()
    {
        return $this->instance;
    }

    public function getObjectDefinition() : ObjectDefinition
    {
        return $this->objectDefinition;
    }

    public function __toString()
    {
        return 'Instance';
    }
}
