<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Definition\ClassDefinition;
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
    const WILDCARD = '*';
    /**
     * Matches anything except "\"
     */
    const WILDCARD_PATTERN = '([^\\\\]+)';

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
        $definition = $this->findDefinition($name);

        if ($definition === null) {
            // Not found, we use the chain or return null
            if ($this->chainedSource) {
                return $this->chainedSource->getDefinition($name, $parentDefinition);
            }
            return null;
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

    /**
     * @param string $name
     * @return Definition|null
     */
    private function findDefinition($name)
    {
        // Look for the definition by name
        if (array_key_exists($name, $this->definitions)) {
            return $this->castDefinition($this->definitions[$name], $name);
        }

        // Look if there are wildcards definitions
        foreach ($this->definitions as $key => $definition) {
            if (strpos($key, self::WILDCARD) === false) {
                continue;
            }

            // Turn the pattern into a regex
            $key = addslashes($key);
            $key = '#' . str_replace(self::WILDCARD, self::WILDCARD_PATTERN, $key) . '#';
            if (preg_match($key, $name, $matches) === 1) {
                $definition = $this->castDefinition($definition, $name);

                // For a class definition, we replace * in the class name with the matches
                // *Interface -> *Impl => FooInterface -> FooImpl
                if ($definition instanceof ClassDefinition) {
                    array_shift($matches);
                    $definition->setClassName(
                        $this->replaceWildcards($definition->getClassName(), $matches)
                    );
                }

                return $definition;
            }
        }

        return null;
    }

    /**
     * @param mixed  $definition
     * @param string $name
     * @return Definition
     */
    private function castDefinition($definition, $name)
    {
        if ($definition instanceof DefinitionHelper) {
            $definition = $definition->getDefinition($name);
        }
        if (! $definition instanceof Definition) {
            $definition = new ValueDefinition($name, $definition);
        }

        return $definition;
    }

    /**
     * Replaces all the wildcards in the string with the given replacements.
     * @param string   $string
     * @param string[] $replacements
     * @return string
     */
    private function replaceWildcards($string, array $replacements)
    {
        foreach ($replacements as $replacement) {
            $pos = strpos($string, self::WILDCARD);
            if ($pos !== false) {
                $string = substr_replace($string, $replacement, $pos, 1);
            }
        }

        return $string;
    }
}
