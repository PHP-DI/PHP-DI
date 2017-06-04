<?php

declare(strict_types=1);

namespace DI\Definition\Helper;

use DI\Definition\Definition;
use DI\Definition\StringDefinition;

/**
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class StringDefinitionHelper implements DefinitionHelper
{
    /**
     * @var string
     */
    private $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @param string $entryName Container entry name
     *
     * @return StringDefinition
     */
    public function getDefinition(string $entryName) : Definition
    {
        return new StringDefinition($entryName, $this->expression);
    }
}
