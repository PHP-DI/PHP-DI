<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Definition\Definition;
use DI\Definition\MergeableDefinition;
use DI\Definition\ValueDefinition;
use DI\Definition\Helper\DefinitionHelper;

/**
 * Reads DI definitions from a PHP array.
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
     * DI definitions in a PHP array
     * @var array
     */
    private $definitions = array();

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name, MergeableDefinition $parentDefinition = null)
    {
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
            $definition = $parentDefinition->merge($definition);
        }

        // Enrich definition in sub-source
        if ($this->chainedSource && $definition instanceof MergeableDefinition) {
            $definition = $this->chainedSource->getDefinition($name, $definition);
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
     * {@inheritdoc}
     */
    public function chain(DefinitionSource $source)
    {
        $this->chainedSource = $source;
    }
}
