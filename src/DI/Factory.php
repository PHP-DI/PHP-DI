<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use DI\Definition\ClassDefinition;
use DI\Definition\EntryReference;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\MethodInjection;
use DI\Definition\PropertyInjection;
use DI\Definition\UndefinedInjection;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use ReflectionParameter;

/**
 * Factory class, responsible of instantiating classes
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Factory implements FactoryInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     * @throws DependencyException
     * @throws DefinitionException
     */
    public function createInstance(ClassDefinition $classDefinition)
    {
        $classReflection = new ReflectionClass($classDefinition->getClassName());

        if (!$classReflection->isInstantiable()) {
            throw new DependencyException("$classReflection->name is not instantiable");
        }

        try {
            $instance = $this->injectConstructor($classReflection, $classDefinition->getConstructorInjection());
            $this->injectMethodsAndProperties($instance, $classDefinition);
        } catch (NotFoundException $e) {
            throw new DependencyException("Error while injecting dependencies into $classReflection->name: " . $e->getMessage(), 0, $e);
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     * @throws DependencyException
     * @throws DefinitionException
    */
    public function injectOnInstance(ClassDefinition $classDefinition, $instance)
    {
        try {
            $this->injectMethodsAndProperties($instance, $classDefinition);
        } catch (NotFoundException $e) {
            throw new DependencyException("Error while injecting dependencies into " . $classDefinition->getClassName() . ": " . $e->getMessage(), 0, $e);
        }

        return $instance;
    }

    /**
     * Creates an instance and inject dependencies through the constructor
     *
     * @param ReflectionClass      $classReflection
     * @param MethodInjection|null $constructorInjection
     *
     * @throws DefinitionException
     * @return object
     */
    private function injectConstructor(ReflectionClass $classReflection, MethodInjection $constructorInjection = null)
    {
        $args = $this->prepareMethodParameters($constructorInjection, $classReflection->getConstructor());

        return $classReflection->newInstanceArgs($args);
    }

    /**
     * @param object          $instance
     * @param ClassDefinition $classDefinition
     */
    private function injectMethodsAndProperties($instance, ClassDefinition $classDefinition)
    {
        // Property injections
        foreach ($classDefinition->getPropertyInjections() as $propertyInjection) {
            $this->injectProperty($instance, $propertyInjection);
        }
        // Method injections
        foreach ($classDefinition->getMethodInjections() as $methodInjection) {
            $this->injectMethod($instance, $methodInjection);
        }
    }

    /**
     * Inject dependencies through methods.
     *
     * @param object          $object Object to inject dependencies into
     * @param MethodInjection $methodInjection
     *
     * @throws DependencyException
     * @throws DefinitionException
     */
    private function injectMethod($object, MethodInjection $methodInjection)
    {
        $methodReflection = new ReflectionMethod($object, $methodInjection->getMethodName());

        $args = $this->prepareMethodParameters($methodInjection, $methodReflection);

        $methodReflection->invokeArgs($object, $args);
    }

    private function prepareMethodParameters(MethodInjection $methodInjection = null, ReflectionMethod $methodReflection = null)
    {
        if (!$methodReflection) {
            return array();
        }

        // Check the number of parameters match
        $nbRequiredParameters = $methodReflection->getNumberOfRequiredParameters();
        $parameterInjections = $methodInjection ? $methodInjection->getParameters() : array();
        if (count($parameterInjections) < $nbRequiredParameters) {
            $className = $methodReflection->getDeclaringClass()->getName();
            throw new DefinitionException("$className::{$methodReflection->name} takes $nbRequiredParameters"
                . " parameters, " . count($parameterInjections) . " defined or guessed");
        }

        // No parameters
        if (empty($parameterInjections)) {
            return array();
        }

        $reflectionParameters = $methodReflection->getParameters();

        $args = array();
        foreach ($parameterInjections as $index => $value) {
            if ($value instanceof UndefinedInjection) {
                // If the parameter is optional and wasn't specified, we take its default value
                if ($reflectionParameters[$index]->isOptional()) {
                    $args[] = $this->getParameterDefaultValue($reflectionParameters[$index], $methodReflection);
                    continue;
                }
                $className = $methodReflection->getDeclaringClass()->getName();
                throw new DefinitionException("The parameter '" . $reflectionParameters[$index]->getName()
                    . "' of $className::{$methodReflection->name} has no value defined or guessable");
            }

            if ($value instanceof EntryReference) {
                $args[] = $this->container->get($value->getName());
            } else {
                $args[] = $value;
            }
        }

        return $args;
    }

    /**
     * Inject dependencies into properties
     *
     * @param object            $object            Object to inject dependencies into
     * @param PropertyInjection $propertyInjection Property injection definition
     *
     * @throws DependencyException
     * @throws DefinitionException
     */
    private function injectProperty($object, PropertyInjection $propertyInjection)
    {
        $propertyName = $propertyInjection->getPropertyName();
        $property = new ReflectionProperty(get_class($object), $propertyName);

        $value = $propertyInjection->getValue();

        if ($value instanceof UndefinedInjection) {
            throw new DefinitionException("The property " . get_class($object) . "::"
                . $propertyInjection->getPropertyName() . " has no value defined or guessable");
        }

        if ($value instanceof EntryReference) {
            try {
                $value = $this->container->get($value->getName());
            } catch (DependencyException $e) {
                throw $e;
            } catch (Exception $e) {
                throw new DependencyException("Error while injecting '" . $value->getName() . "' in "
                    . get_class($object) . "::$propertyName. " . $e->getMessage(), 0, $e);
            }
        }

        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * Returns the default value of a function parameter
     * @param ReflectionParameter $reflectionParameter
     * @param ReflectionMethod    $reflectionMethod
     * @throws DefinitionException Can't get default values from PHP internal classes and methods
     * @return mixed
     */
    private function getParameterDefaultValue(ReflectionParameter $reflectionParameter, ReflectionMethod $reflectionMethod)
    {
        try {
            return $reflectionParameter->getDefaultValue();
        } catch (ReflectionException $e) {
            throw new DefinitionException("The parameter '{$reflectionParameter->getName()}'"
            . " of {$reflectionMethod->getDeclaringClass()->getName()}::{$reflectionMethod->getName()} has no type defined or guessable."
            . " It has a default value, but the default value can't be read through Reflection because it is a PHP internal class.");
        }
    }
}
