<?php

namespace DI\Definition;

use DI\Definition\Helper\DefinitionHelper;
use DI\Scope;

/**
 * Definition of an array containing values or references.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayDefinition implements Definition
{
    /**
     * Entry name.
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $values;

    /**
     * @param string $name Entry name
     */
    public function __construct(string $name, array $values)
    {
        $this->name = $name;
        $this->values = $values;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getScope() : string
    {
        return Scope::SINGLETON;
    }

    public function getValues() : array
    {
        return $this->values;
    }

    public function __toString()
    {
        $str = '[' . PHP_EOL;

        foreach ($this->values as $key => $value) {
            if (is_string($key)) {
                $key = "'" . $key . "'";
            }

            $str .= '    ' . $key . ' => ';

            if ($value instanceof DefinitionHelper) {
                $str .= str_replace(PHP_EOL, PHP_EOL . '    ', $value->getDefinition(''));
            } else {
                $str .= var_export($value, true);
            }

            $str .= ',' . PHP_EOL;
        }

        return $str . ']';
    }
}
