<?php

declare(strict_types=1);

namespace DI\Definition;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AutowireDefinition extends ObjectDefinition
{
    /**
     * @var bool|null
     */
    protected $useAnnotations;

    /**
     * Enable/disable reading annotations for this definition, regardless of a container configuration.
     * @param bool $flag
     */
    public function useAnnotations(bool $flag = true)
    {
        $this->useAnnotations = $flag;
    }

    /**
     * Returns boolean if the useAnnotation flag was explicitly set, otherwise null.
     * @return bool|null
     */
    public function isUsingAnnotations()
    {
        return $this->useAnnotations;
    }
}
