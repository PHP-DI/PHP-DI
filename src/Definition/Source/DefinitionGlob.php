<?php

declare(strict_types=1);

namespace DI\Definition\Source;

/**
 * Reads DI definitions from files matching glob pattern.
 *
 */
class DefinitionGlob extends SourceChain
{
    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * Glob pattern to files containing definitions
     * @var string|null
     */
    private $pattern;

    /**
     * @var Autowiring
     */
    private $autowiring;

    /**
     * @param string $pattern Glob pattern to files containing definitions
     */
    public function __construct($pattern)
    {
        // Lazy-loading to improve performances
        $this->pattern = $pattern;

        parent::__construct([]);
    }

    public function setAutowiring(Autowiring $autowiring)
    {
        $this->autowiring = $autowiring;
    }

    public function getDefinition(string $name, int $startIndex = 0)
    {
        $this->initialize();

        return parent::getDefinition($name, $startIndex);
    }

    public function getDefinitions() : array
    {
        $this->initialize();

        return parent::getDefinitions();
    }

    /**
     * Lazy-loading of the definitions.
     */
    private function initialize()
    {
        if ($this->initialized === true) {
            return;
        }

        $paths = glob($this->pattern, GLOB_BRACE);
        foreach ($paths as $path)
        {
            $this->sources[] = new DefinitionFile($path, $this->autowiring);
        }

        $this->initialized = true;
    }
}
