<?php

namespace DI\Definition\Helper;

use DI\Definition\Definition;
use DI\Definition\ValueDefinition;

/**
 * Helps defining a value.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ValueDefinitionHelper implements DefinitionHelper
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param string $entryName Container entry name
     * @return ValueDefinition
     */
    public function getDefinition(string $entryName) : Definition
    {
        return new ValueDefinition($entryName, $this->value);
    }
}
