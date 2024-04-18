<?php

declare(strict_types=1);

namespace DI\Definition;

/**
 * Definition of an array containing values or references.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayDefinition implements Definition
{
    /** Entry name. */
    private string $name = '';

    public function __construct(
        private array $values,
    ) {
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getValues() : array
    {
        return $this->values;
    }

    public function replaceNestedDefinitions(callable $replacer) : void
    {
        $this->values = array_map($replacer, $this->values);
    }

    public function __toString() : string
    {
        $str = '[' . "\n";

        foreach ($this->values as $key => $value) {
            if (is_string($key)) {
                $key = "'" . $key . "'";
            }

            $str .= '    ' . $key . ' => ';

            if ($value instanceof Definition) {
                $str .= str_replace("\n", "\n" . '    ', (string) $value);
            } else {
                $str .= var_export($value, true);
            }

            $str .= ',' . "\n";
        }

        return $str . ']';
    }
}
