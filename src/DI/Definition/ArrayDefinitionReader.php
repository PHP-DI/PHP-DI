<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Scope;

/**
 * Reads DI definitions from a PHP array
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayDefinitionReader implements DefinitionReader
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
        $definition = null;

        if (array_key_exists($name, $this->definitions)) {
            $arrayDefinition = $this->definitions[$name];

            // Value definition
            if (!is_array($arrayDefinition)) {
                return new ValueDefinition($name, $arrayDefinition);
            }

            // It's a class
            $definition = new ClassDefinition($name);

            // Class scope
            if (array_key_exists('scope', $arrayDefinition)) {
                $scope = $arrayDefinition['scope'];
                if (!$scope instanceof Scope) {
                    $scope = new Scope($scope);
                }
                $definition->setScope($scope);
            }

            // Lazy
            if (array_key_exists('lazy', $arrayDefinition)) {
                $definition->setLazy($arrayDefinition['lazy']);
            }

            // Properties
            $this->readPropertyInjections($definition, $arrayDefinition);
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
     * @param ClassDefinition $definition
     * @param array           $arrayDefinition
     */
    private function readPropertyInjections(ClassDefinition $definition, array $arrayDefinition)
    {
        if (array_key_exists('properties', $arrayDefinition)) {
            foreach ($arrayDefinition['properties'] as $propertyName => $arrayPropertyDefinition) {
                if (is_array($arrayPropertyDefinition)) {
                    // Name
                    if (!array_key_exists('name', $arrayPropertyDefinition)) {
                        throw new DefinitionException("Key 'name' not found in array definition of "
                            . $definition->getName() . "::" . $propertyName);
                    }
                    $name = $arrayPropertyDefinition['name'];

                    $propertyInjection = new PropertyInjection($propertyName, $name);

                    // Lazy
                    if (array_key_exists('lazy', $arrayPropertyDefinition)) {
                        $propertyInjection->setLazy($arrayPropertyDefinition['lazy']);
                    }

                    $definition->addPropertyInjection($propertyInjection);
                }
                if (is_string($arrayPropertyDefinition)) {
                    $propertyInjection = new PropertyInjection($propertyName, $arrayPropertyDefinition);
                    $definition->addPropertyInjection($propertyInjection);
                }
            }
        }
    }

}
