<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use Closure;
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

            // Closure definition
            if ($arrayDefinition instanceof Closure) {
                return new ClosureDefinition($name, $arrayDefinition);
            }

            // Value definition
            if (!is_array($arrayDefinition)) {
                return new ValueDefinition($name, $arrayDefinition);
            }

            // Validate array keys
            $validKeys = array('class', 'scope', 'lazy', 'constructor', 'properties', 'methods');
            $keys = array_keys($arrayDefinition);
            $unknownKeys = array_diff($keys, $validKeys);
            if (count($unknownKeys) > 0) {
                $firstKey = current($unknownKeys);
                throw new DefinitionException("Invalid key '$firstKey' in definition of entry '$name'; Valid keys are: "
                    . implode(', ', $validKeys));
            }

            // It's a class
            if (array_key_exists('class', $arrayDefinition)) {
                $className = $arrayDefinition['class'];
                $definition = new ClassDefinition($name, $className);
            } else {
                $definition = new ClassDefinition($name);
            }

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

            // Methods
            $this->readMethodInjections($definition, $arrayDefinition);
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
     * @throws DefinitionException
     */
    private function readPropertyInjections(ClassDefinition $definition, array $arrayDefinition)
    {
        if (array_key_exists('properties', $arrayDefinition)) {
            foreach ($arrayDefinition['properties'] as $propertyName => $arrayPropertyDefinition) {

                // Full definition: array
                if (is_array($arrayPropertyDefinition)) {
                    // Name
                    if (!array_key_exists('name', $arrayPropertyDefinition)) {
                        throw new DefinitionException("Key 'name' not found in array definition of "
                            . $definition->getName() . "::$propertyName");
                    }
                    $name = $arrayPropertyDefinition['name'];

                    $propertyInjection = new PropertyInjection($propertyName, $name);

                    // Lazy
                    if (array_key_exists('lazy', $arrayPropertyDefinition)) {
                        $propertyInjection->setLazy($arrayPropertyDefinition['lazy']);
                    }

                    $definition->addPropertyInjection($propertyInjection);
                }

                // Shortcut: string
                if (is_string($arrayPropertyDefinition)) {
                    $propertyInjection = new PropertyInjection($propertyName, $arrayPropertyDefinition);
                    $definition->addPropertyInjection($propertyInjection);
                }

            }
        }
    }

    /**
     * @param ClassDefinition $definition
     * @param array           $arrayDefinition
     * @throws DefinitionException
     */
    private function readMethodInjections(ClassDefinition $definition, array $arrayDefinition)
    {
        if (array_key_exists('methods', $arrayDefinition)) {
            if (!is_array($arrayDefinition['methods'])) {
                throw new DefinitionException("Key 'methods' for class " . $definition->getName()
                                                  . " should be an array");
            }

            foreach ($arrayDefinition['methods'] as $methodName => $arrayMethodDefinition) {
                $methodInjection = new MethodInjection($methodName);

                if (is_array($arrayMethodDefinition)) {
                    $this->readParameterInjections($definition, $methodInjection, $arrayMethodDefinition);
                } else {
                    // String: shortcut for 1 parameter method
                    $methodInjection->addParameterInjection(new ParameterInjection(0, $arrayMethodDefinition));
                }

                $definition->addMethodInjection($methodInjection);
            }
        }
    }

    /**
     * @param ClassDefinition $definition
     * @param MethodInjection $methodInjection
     * @param array           $arrayDefinition
     * @throws DefinitionException
     */
    private function readParameterInjections(ClassDefinition $definition, MethodInjection $methodInjection, array $arrayDefinition)
    {
        foreach ($arrayDefinition as $parameterName => $arrayParameterDefinition) {

            // Full definition: array
            if (is_array($arrayParameterDefinition)) {
                // Name
                if (!array_key_exists('name', $arrayParameterDefinition)) {
                    throw new DefinitionException("Key 'name' not found in array definition for parameter "
                        . "$parameterName of method "
                        . $definition->getName() . "::" . $methodInjection->getMethodName());
                }
                $name = $arrayParameterDefinition['name'];

                $parameterInjection = new ParameterInjection($parameterName, $name);

                $methodInjection->addParameterInjection($parameterInjection);
            }

            // Shortcut: string
            if (is_string($arrayParameterDefinition)) {
                $parameterInjection = new ParameterInjection($parameterName, $arrayParameterDefinition);

                $methodInjection->addParameterInjection($parameterInjection);
            }

        }
    }

}
