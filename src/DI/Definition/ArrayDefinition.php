<?php

namespace DI\Definition;

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
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return Scope::SINGLETON;
    }

    /**
     * @return array
     */
    public function getValues()
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

            if ($value instanceof Definition) {
                $str .= str_replace(PHP_EOL, PHP_EOL . '    ', $value);
            } else {
                $str .= var_export($value, true);
            }

            $str .= ',' . PHP_EOL;
        }

        return $str . ']';
    }
}
