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
use ReflectionMethod;
use ReflectionProperty;

/**
 * Simple container of definitions, for definitions set on the fly.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InMemoryDefinitionSource implements DefinitionSource, ChainableDefinitionSource, ClassDefinitionSource
{
    /**
     * @var Definition[]
     */
    private $definitions = array();

    /**
     * @var DefinitionSource
     */
    private $chainedSource;

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        if (array_key_exists($name, $this->definitions)) {
            return $this->definitions[$name];
        }

        if ($this->chainedSource) {
            return $this->chainedSource->getDefinition($name);
        }

        return null;
    }

    /**
     * Add a definition
     *
     * @param Definition $definition
     */
    public function addDefinition(Definition $definition)
    {
        $this->definitions[$definition->getName()] = $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function chain(DefinitionSource $source)
    {
        $this->chainedSource = $source;
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
}
