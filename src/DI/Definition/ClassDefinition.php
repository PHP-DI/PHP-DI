<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Definition\ClassInjection\MethodInjection;
use DI\Definition\ClassInjection\PropertyInjection;
use DI\Definition\Exception\DefinitionException;
use DI\Scope;

/**
 * Definition of a class for dependency injection
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ClassDefinition implements Definition
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
     * Constructor injection
     * @var MethodInjection|null
     */
    private $constructorInjection;

    /**
     * Property injections
     * @var PropertyInjection[]
     */
    private $propertyInjections = array();

    /**
     * Method injections indexed by the method name
     * @var MethodInjection[]
     */
    private $methodInjections = array();

    /**
     * @var Scope|null
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
     * @return bool True if the definition is an alias to another class
     */
    public function isAlias()
    {
        return $this->className !== null && $this->className !== $this->name;
    }

    /**
     * @return MethodInjection|null
     */
    public function getConstructorInjection()
    {
        return $this->constructorInjection;
    }

    /**
     * @param MethodInjection|null $constructorInjection
     */
    public function setConstructorInjection(MethodInjection $constructorInjection = null)
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
        return $this->methodInjections;
    }

    /**
     * @param MethodInjection $methodInjection
     */
    public function addMethodInjection(MethodInjection $methodInjection)
    {
        $this->methodInjections[$methodInjection->getMethodName()] = $methodInjection;
    }

    /**
     * @param Scope $scope
     */
    public function setScope(Scope $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return Scope
     */
    public function getScope()
    {
        return $this->scope ?: Scope::SINGLETON();
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
    public function merge(Definition $definition)
    {
        if (!$definition instanceof ClassDefinition) {
            throw new DefinitionException("DI definition conflict: there are 2 different definitions for '"
                . $definition->getName() . "' that are incompatible, they are not of the same type");
        }

        // The latter prevails
        if ($definition->className !== null) {
            $this->className = $definition->className;
        }
        if ($definition->scope !== null) {
            $this->scope = $definition->scope;
        }
        if ($definition->lazy !== null) {
            $this->lazy = $definition->lazy;
        }

        // Merge constructor injection
        if ($definition->getConstructorInjection() !== null) {
            if ($this->constructorInjection !== null) {
                // Merge
                $this->constructorInjection->merge($definition->getConstructorInjection());
            } else {
                // Set
                $this->constructorInjection = $definition->getConstructorInjection();
            }
        }

        // Merge property injections
        foreach ($definition->getPropertyInjections() as $propertyName => $propertyInjection) {
            if (array_key_exists($propertyName, $this->propertyInjections)) {
                // Merge
                $this->propertyInjections[$propertyName]->merge($propertyInjection);
            } else {
                // Add
                $this->propertyInjections[$propertyName] = $propertyInjection;
            }
        }

        // Merge method injections
        foreach ($definition->getMethodInjections() as $methodName => $methodInjection) {
            if (array_key_exists($methodName, $this->methodInjections)) {
                // Merge
                $this->methodInjections[$methodName]->merge($methodInjection);
            } else {
                // Add
                $this->methodInjections[$methodName] = $methodInjection;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isCacheable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function isMergeable()
    {
        return true;
    }
}
