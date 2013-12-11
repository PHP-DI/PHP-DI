<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Definition\Definition;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\MergeableDefinition;
use DI\Definition\ValueDefinition;
use DI\DefinitionHelper\DefinitionHelper;

/**
 * Reads DI definitions from a PHP array, or a file returning a PHP array.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayDefinitionSource implements ChainableDefinitionSource
{
    /**
     * @var DefinitionSource
     */
    private $chainedSource;

    /**
     * @var bool
     */
    private $initialized;

    /**
     * File containing definitions, or null if the definitions are given as a PHP array.
     * @var string|null
     */
    private $file;

    /**
     * DI definitions in a PHP array
     * @var array
     */
    private $definitions = array();

    /**
     * @param string|null $file File in which the definitions are returned as an array.
     */
    public function __construct($file = null)
    {
        if (! $file) {
            $this->initialized = true;
            return;
        }

        // If we are given a file containing an array, we lazy-load it to improve performance
        $this->initialized = false;
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name, MergeableDefinition $parentDefinition = null)
    {
        $this->initialize();

        if (! array_key_exists($name, $this->definitions)) {
            // Not found, we use the chain or return null
            if ($this->chainedSource) {
                return $this->chainedSource->getDefinition($name, $parentDefinition);
            }
            return null;
        }

        $definition = $this->definitions[$name];

        if ($definition instanceof DefinitionHelper) {
            $definition = $definition->getDefinition($name);
        }

        if (! $definition instanceof Definition) {
            $definition = new ValueDefinition($name, $definition);
        }

        // If the definition we have is not mergeable, and we are supposed to merge, we ignore it
        if ($parentDefinition && (! $definition instanceof MergeableDefinition)) {
            return $parentDefinition;
        }

        // Merge with parent
        if ($parentDefinition) {
            $parentDefinition->merge($definition);
            $definition = $parentDefinition;
        }

        // Enrich definition in sub-source
        if ($this->chainedSource && $definition instanceof MergeableDefinition) {
            $this->chainedSource->getDefinition($name, $definition);
        }

        return $definition;
    }

    /**
     * @param array $definitions DI definitions in a PHP array indexed by the definition name.
     */
    public function addDefinitions(array $definitions)
    {
        // The newly added data prevails
        // "for keys that exist in both arrays, the elements from the left-hand array will be used"
        $this->definitions = $definitions + $this->definitions;
    }

    /**
     * @param Definition $definition
     */
    public function addDefinition(Definition $definition)
    {
        $this->definitions[$definition->getName()] = $definition;
    }

    /**
     * Lazy-loading of the definitions.
     * @throws DefinitionException
     */
    private function initialize()
    {
        if ($this->initialized === true) {
            return;
        }

        if (! is_readable($this->file)) {
            throw new DefinitionException("File {$this->file} doesn't exist or is not readable");
        }

        $definitions = require $this->file;

        if (! is_array($definitions)) {
            throw new DefinitionException("File {$this->file} should return an array of definitions");
        }

        $this->addDefinitions($definitions);

        $this->initialized = true;
    }

    /**
     * {@inheritdoc}
     */
    public function chain(DefinitionSource $source)
    {
        $this->chainedSource = $source;
    }
}
