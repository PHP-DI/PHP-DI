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
use DI\Definition\ValueDefinition;
use DI\DefinitionHelper\DefinitionHelper;

/**
 * Reads DI definitions from a PHP array
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayDefinitionSource implements DefinitionSource
{
    /**
     * DI definitions in a PHP array
     * @var array
     */
    private $definitions = array();

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        if (!array_key_exists($name, $this->definitions)) {
            if (class_exists($name)) {
                $definition = new ClassDefinition($name);
                $this->mergeWithParents($name, $definition);
                return $definition;
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

        // If it's a class, merge definitions from parent classes and interfaces
        if ($definition instanceof ClassDefinition) {
            $this->mergeWithParents($name, $definition);
        }

        return $definition;
    }

    /**
     * @param array $definitions DI definitions in a PHP array
     */
    public function addDefinitions(array $definitions)
    {
        // The newly added data prevails
        // "for keys that exist in both arrays, the elements from the left-hand array will be used"
        $this->definitions = $definitions + $this->definitions;
    }

    /**
     * Merge a class definition which the definitions of its parent classes and its interfaces
     *
     * @param string          $name
     * @param ClassDefinition $definition
     */
    private function mergeWithParents($name, ClassDefinition $definition)
    {
        $className = $definition->getClassName();
        if (!class_exists($className)) {
            return;
        }

        // Parent class
        $parentClass = get_parent_class($className);

        // Avoids loops (if AbstractClass1 is an alias to Class1, which extends AbstractClass1)
        if ($parentClass && $parentClass != $name) {
            $parentDefinition = $this->getDefinition($parentClass);
            if ($parentDefinition instanceof ClassDefinition) {
                $definition->merge($this->getDefinition($parentClass));
            }
        }

        // Interfaces
        $interfaces = class_implements($className);

        if (is_array($interfaces)) {
            foreach ($interfaces as $interfaceName) {
                // Avoids loops (if Interface1 is an alias to Class1, which implements Interface1)
                if ($interfaceName == $name) {
                    continue;
                }
                $interfaceDefinition = $this->getDefinition($interfaceName);
                if ($interfaceDefinition instanceof ClassDefinition) {
                    $definition->merge($interfaceDefinition);
                }
            }
        }
    }
}
