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
use DI\Definition\Exception\DefinitionException;
use DI\Definition\MethodInjection;
use DI\Definition\PropertyInjection;
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
    public function injectInstance(ClassDefinition $classDefinition, $instance)
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
        $constructorReflection = $classReflection->getConstructor();

        // No constructor
        if (!$constructorReflection) {
            return $classReflection->newInstance();
        }

        // Check the definition and the class parameter number match
        $nbRequiredParameters = $constructorReflection->getNumberOfRequiredParameters();
        $parameterInjections = $constructorInjection ? $constructorInjection->getParameterInjections() : array();
        if (count($parameterInjections) < $nbRequiredParameters) {
            throw new DefinitionException("The constructor of {$classReflection->name} takes "
                . "$nbRequiredParameters parameters, " . count($parameterInjections) . " defined or guessed");
        }

        // No parameters
        if (count($parameterInjections) === 0) {
            return $classReflection->newInstance();
        }

        $parameters = $this->getMethodReflectionParameters($constructorReflection);

        $args = array();
        foreach ($parameterInjections as $parameterInjection) {
            $entryName = $parameterInjection->getEntryName();

            if ($entryName === null) {
                // Check the container to see if it contains a simple value matching the parameter name
                try {
                    $parameterValue = $this->container->get($parameterInjection->getParameterName());
                    if ($parameterValue !== null) {
                        $args[] = $parameterValue;
                        continue;
                    }
                }
                catch (NotFoundException $e) {
                    // If the parameter is optional and wasn't specified, we take its default value
                    if ($parameters[$parameterInjection->getParameterName()]->isOptional()) {
                        $args[] = $this->getParameterDefaultValue($parameters[$parameterInjection->getParameterName()], $constructorReflection);
                        continue;
                    }
                }
                throw new DefinitionException("The parameter '" . $parameterInjection->getParameterName()
                    . "' of the constructor of '{$classReflection->name}' has no type defined or guessable");
            }

            if ($parameterInjection->isLazy()) {
                $args[] = $this->container->get($entryName, true);
            } else {
                $args[] = $this->container->get($entryName);
            }
        }

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
     * Inject dependencies through methods
     *
     * @param object          $object Object to inject dependencies into
     * @param MethodInjection $methodInjection
     *
     * @throws DependencyException
     * @throws DefinitionException
     */
    private function injectMethod($object, MethodInjection $methodInjection)
    {
        $methodName = $methodInjection->getMethodName();
        $classReflection = new ReflectionClass($object);
        $methodReflection = $classReflection->getMethod($methodName);

        // Check the definition and the class parameter number match
        $nbRequiredParameters = $methodReflection->getNumberOfRequiredParameters();
        $parameterInjections = $methodInjection ? $methodInjection->getParameterInjections() : array();
        if (count($parameterInjections) < $nbRequiredParameters) {
            throw new DefinitionException("{$classReflection->name}::$methodName takes "
                . "$nbRequiredParameters parameters, " . count($parameterInjections) . " defined or guessed");
        }

        // No parameters
        if (count($parameterInjections) === 0) {
            $methodReflection->invoke($object);
            return;
        }

        $parameters = $this->getMethodReflectionParameters($methodReflection);

        $args = array();
        foreach ($parameterInjections as $parameterInjection) {
            $entryName = $parameterInjection->getEntryName();

            if ($entryName === null) {
                // If the parameter is optional and wasn't specified, then we skip all next parameters
                if ($parameters[$parameterInjection->getParameterName()]->isOptional()) {
                    $args[] = $this->getParameterDefaultValue($parameters[$parameterInjection->getParameterName()], $methodReflection);
                    continue;
                }
                throw new DefinitionException("The parameter '" . $parameterInjection->getParameterName()
                    . "' of {$classReflection->name}::$methodName has no type defined or guessable");
            }

            if ($parameterInjection->isLazy()) {
                $args[] = $this->container->get($entryName, true);
            } else {
                $args[] = $this->container->get($entryName);
            }
        }

        $methodReflection->invokeArgs($object, $args);
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

        $entryName = $propertyInjection->getEntryName();
        if ($entryName === null) {
            throw new DefinitionException(get_class($object) . "::$propertyName has no type defined or guessable");
        }

        // Get the dependency
        try {
            $value = $this->container->get($propertyInjection->getEntryName(), $propertyInjection->isLazy());
        } catch (DependencyException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new DependencyException("Error while injecting '" . $propertyInjection->getEntryName() . "' in "
                . get_class($object) . "::$propertyName. " . $e->getMessage(), 0, $e);
        }

        // Allow access to protected and private properties
        $property->setAccessible(true);

        // Inject the dependency
        $property->setValue($object, $value);
    }

    /**
     * Returns the ReflectionParameter of a method indexed by the parameters names
     * @param ReflectionMethod $reflectionMethod
     * @return ReflectionParameter[]
     */
    private function getMethodReflectionParameters(ReflectionMethod $reflectionMethod)
    {
        $parameters = $reflectionMethod->getParameters();

        $keys = array_map(
            function (ReflectionParameter $parameter) {
                return $parameter->getName();
            },
            $parameters
        );

        return array_combine($keys, $parameters);
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
