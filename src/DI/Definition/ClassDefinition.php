<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

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
     * @var Scope
     */
    private $scope;

    /**
     * @var bool
     */
    private $lazy = false;

    /**
     * @param string $name Class name
     * @param string $className
     */
    public function __construct($name, $className = null)
    {
        $this->name = (string) $name;
        $this->className = $className;
        // Default scope
        $this->scope = Scope::SINGLETON();
    }

    /**
     * @return string Entry name
     */
    public function getName()
    {
        return $this->name;
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
     * @param bool $lazy
     */
    public function setLazy($lazy)
    {
        $this->lazy = (bool) $lazy;
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
        return $this->scope;
    }

    /**
     * @return bool
     */
    public function isLazy()
    {
        return $this->lazy;
    }

}
