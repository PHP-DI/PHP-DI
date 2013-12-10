<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Definition\ClassDefinition;
use DI\Definition\Definition;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\ValueDefinition;
use DI\DefinitionHelper\DefinitionHelper;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Reads DI definitions from a PHP array, or a file returning a PHP array.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayDefinitionSource implements DefinitionSource, ChainableDefinitionSource, ClassDefinitionSource
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
    public function getDefinition($name)
    {
        $this->initialize();

        if (! array_key_exists($name, $this->definitions)) {
            // Not found, we use the chain or return null
            if ($this->chainedSource) {
                return $this->chainedSource->getDefinition($name);
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

        if ($definition instanceof ClassDefinition) {
            // TODO merge properties and methods with sub-sources
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyInjection($entryName, ReflectionProperty $property)
    {
        $definition = $this->getDefinition($entryName);

        if ($definition && $definition instanceof ClassDefinition) {
            return $definition->getPropertyInjection($property->getName());
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodInjection($entryName, ReflectionMethod $method)
    {
        $definition = $this->getDefinition($entryName);

        if ($definition && $definition instanceof ClassDefinition) {
            return $definition->getMethodInjection($method->getName());
        }

        return null;
    }

    /**
     * @param array $definitions DI definitions in a PHP array.
     */
    public function addDefinitions(array $definitions)
    {
        // The newly added data prevails
        // "for keys that exist in both arrays, the elements from the left-hand array will be used"
        $this->definitions = $definitions + $this->definitions;
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
