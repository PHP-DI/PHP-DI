<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\ObjectDefinition\PropertyInjection;
use DI\Definition\Exception\DefinitionException;
use DI\Scope;

/**
 * Defines how an object can be instantiated.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ObjectDefinition implements Definition, CacheableDefinition, HasSubDefinition
{
    /**
     * Entry name (most of the time, same as $classname)
     * @var string
     */
    private $name;

    /**
     * Class name (if null, then the class name is $name)
     * @var string|null
     */
    private $className;

    /**
     * Constructor parameter injection
     * @var MethodInjection|null
     */
    private $constructorInjection;

    /**
     * Property injections
     * @var PropertyInjection[]
     */
    private $propertyInjections = array();

    /**
     * Method calls
     * @var MethodInjection[][]
     */
    private $methodInjections = array();

    /**
     * @var string|null
     */
    private $scope;

    /**
     * @var boolean|null
     */
    private $lazy;

    /**
     * @param string $name Class name
     * @param string $className
     */
    public function __construct($name, $className = null)
    {
        $this->name = (string) $name;
        $this->className = $className;
    }

    /**
     * @return string Entry name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return string Class name
     */
    public function getClassName()
    {
        if ($this->className !== null) {
            return $this->className;
        }
        return $this->name;
    }

    /**
     * @return MethodInjection|null
     */
    public function getConstructorInjection()
    {
        return $this->constructorInjection;
    }

    /**
     * @param MethodInjection $constructorInjection
     */
    public function setConstructorInjection(MethodInjection $constructorInjection)
    {
        $this->constructorInjection = $constructorInjection;
    }

    /**
     * @return PropertyInjection[] Property injections
     */
    public function getPropertyInjections()
    {
        return $this->propertyInjections;
    }

    /**
     * @param string $propertyName
     * @return PropertyInjection
     */
    public function getPropertyInjection($propertyName)
    {
        return isset($this->propertyInjections[$propertyName]) ? $this->propertyInjections[$propertyName] : null;
    }

    /**
     * @param PropertyInjection $propertyInjection
     */
    public function addPropertyInjection(PropertyInjection $propertyInjection)
    {
        $this->propertyInjections[$propertyInjection->getPropertyName()] = $propertyInjection;
    }

    /**
     * @return MethodInjection[] Method injections
     */
    public function getMethodInjections()
    {
        // Return array leafs
        $injections = array();
        array_walk_recursive($this->methodInjections, function ($injection) use (&$injections) {
            $injections[] = $injection;
        });;
        return $injections;
    }

    /**
     * @param MethodInjection $methodInjection
     */
    public function addMethodInjection(MethodInjection $methodInjection)
    {
        $method = $methodInjection->getMethodName();
        if (! isset($this->methodInjections[$method])) {
            $this->methodInjections[$method] = array();
        }
        $this->methodInjections[$method][] = $methodInjection;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->scope ?: Scope::SINGLETON;
    }

    /**
     * @param boolean|null $lazy
     */
    public function setLazy($lazy)
    {
        $this->lazy = $lazy;
    }

    /**
     * @return bool
     */
    public function isLazy()
    {
        if ($this->lazy !== null) {
            return $this->lazy;
        } else {
            // Default value
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubDefinitionName()
    {
        return $this->getClassName();
    }

    /**
     * {@inheritdoc}
     */
    public function setSubDefinition(Definition $definition)
    {
        if (! $definition instanceof ObjectDefinition) {
            throw new DefinitionException(sprintf(
                "Container entry '%s' extends entry '%s' which is not an object",
                $this->getName(),
                $definition->getName()
            ));
        }

        // The current prevails
        if ($this->className === null) {
            $this->className = $definition->className;
        }
        if ($this->scope === null) {
            $this->scope = $definition->scope;
        }
        if ($this->lazy === null) {
            $this->lazy = $definition->lazy;
        }

        // Merge constructor injection
        $this->mergeConstructorInjection($definition);

        // Merge property injections
        $this->mergePropertyInjections($definition);

        // Merge method injections
        $this->mergeMethodInjections($definition);
    }

    private function mergeConstructorInjection(ObjectDefinition $definition)
    {
        if ($definition->getConstructorInjection() !== null) {
            if ($this->constructorInjection !== null) {
                // Merge
                $this->constructorInjection->merge($definition->getConstructorInjection());
            } else {
                // Set
                $this->constructorInjection = $definition->getConstructorInjection();
            }
        }
    }

    private function mergePropertyInjections(ObjectDefinition $definition)
    {
        foreach ($definition->getPropertyInjections() as $propertyName => $propertyInjection) {
            if (! array_key_exists($propertyName, $this->propertyInjections)) {
                // Add
                $this->propertyInjections[$propertyName] = $propertyInjection;
            }
        }
    }

    private function mergeMethodInjections(ObjectDefinition $definition)
    {
        foreach ($definition->methodInjections as $methodName => $calls) {
            if (array_key_exists($methodName, $this->methodInjections)) {
                $this->mergeMethodCalls($calls, $methodName);
            } else {
                // Add
                $this->methodInjections[$methodName] = $calls;
            }
        }
    }

    private function mergeMethodCalls(array $calls, $methodName)
    {
        foreach ($calls as $index => $methodInjection) {
            // Merge
            if (array_key_exists($index, $this->methodInjections[$methodName])) {
                // Merge
                $this->methodInjections[$methodName][$index]->merge($methodInjection);
            } else {
                // Add
                $this->methodInjections[$methodName][$index] = $methodInjection;
            }
        }
    }
}
