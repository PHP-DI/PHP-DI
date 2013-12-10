<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\DefinitionResolver;

use DI\ContainerInterface;
use DI\Definition\ClassDefinition;
use DI\Definition\Definition;
use DI\Definition\EntryReference;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\ClassInjection\MethodInjection;
use DI\Definition\ClassInjection\PropertyInjection;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Resolves a class definition to a value.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ClassDefinitionResolver implements DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var LazyLoadingValueHolderFactory
     */
    private $proxyFactory;

    /**
     * The resolver needs a container.
     * This container will be used to get the entry to which the alias points to.
     *
     * @param ContainerInterface            $container
     * @param LazyLoadingValueHolderFactory $proxyFactory Used to create proxies for lazy injections.
     */
    public function __construct(ContainerInterface $container, LazyLoadingValueHolderFactory $proxyFactory)
    {
        $this->container = $container;
        $this->proxyFactory = $proxyFactory;
    }

    /**
     * Resolve a class definition to a value.
     *
     * This will create a new instance of the class using the injections points defined.
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition)
    {
        if (! $definition instanceof ClassDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition resolver is only compatible with ClassDefinition objects, %s given',
                get_class($definition)
            ));
        }

        // Lazy?
        if ($definition->isLazy()) {
            return $this->createProxy($definition);
        }

        return $this->createInstance($definition);
    }

    /**
     * Injects dependencies on an existing instance.
     *
     * @param ClassDefinition $classDefinition
     * @param object          $instance
     *
     * @throws DependencyException Error while injecting dependencies
     * @throws DefinitionException
     * @return object The instance
     */
    public function injectOnInstance(ClassDefinition $classDefinition, $instance)
    {
        try {
            $this->injectMethodsAndProperties($instance, $classDefinition);
        } catch (NotFoundException $e) {
            $message = sprintf(
                "Error while injecting dependencies into %s: %s",
                $classDefinition->getClassName(),
                $e->getMessage()
            );
            throw new DependencyException($message, 0, $e);
        }
    }

    /**
     * Returns a proxy instance
     *
     * @param ClassDefinition $definition
     *
     * @return object Proxy instance
     */
    private function createProxy(ClassDefinition $definition)
    {
        $resolver = $this;

        /** @noinspection PhpUnusedParameterInspection */
        $proxy = $this->proxyFactory->createProxy(
            $definition->getClassName(),
            function (& $wrappedObject, $proxy, $method, $parameters, & $initializer) use ($resolver, $definition) {
                $wrappedObject = $resolver->createInstance($definition);
                $initializer = null; // turning off further lazy initialization
                return true;
            }
        );

        return $proxy;
    }

    /**
     * Creates an instance of the class and injects dependencies..
     *
     * @param ClassDefinition $classDefinition
     *
     * @throws DependencyException
     * @return object
     *
     * @todo Make private once PHP-DI supports PHP 5.4+
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
            $message = sprintf(
                "Error while injecting dependencies into %s: %s",
                $classReflection->getName(),
                $e->getMessage()
            );
            throw new DependencyException($message, 0, $e);
        }

        return $instance;
    }

    /**
     * Creates an instance and injects dependencies through the constructor.
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

    private function prepareMethodParameters(
        MethodInjection $methodInjection = null,
        ReflectionMethod $methodReflection = null
    ) {
        if (!$methodReflection) {
            return array();
        }

        // Check the number of parameters match
        $nbRequiredParameters = $methodReflection->getNumberOfRequiredParameters();
        $parameterInjections = $methodInjection ? $methodInjection->getParameters() : array();
        if (count($parameterInjections) < $nbRequiredParameters) {
            throw new DefinitionException(sprintf(
                "%s::%s takes %d parameters, %d defined or guessed",
                $methodReflection->getDeclaringClass()->getName(),
                $methodReflection->getName(),
                $nbRequiredParameters,
                count($parameterInjections)
            ));
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
                throw new DefinitionException(sprintf(
                    "The parameter '%s' of %s::%s has no value defined or guessable",
                    $reflectionParameters[$index]->getName(),
                    $methodReflection->getDeclaringClass()->getName(),
                    $methodReflection->getName()
                ));
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
     * Inject dependencies into properties.
     *
     * @param object            $object            Object to inject dependencies into
     * @param \DI\Definition\ClassInjection\PropertyInjection $propertyInjection Property injection definition
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
            throw new DefinitionException(sprintf(
                "The property %s::%s has no value defined or guessable",
                get_class($object),
                $propertyInjection->getPropertyName()
            ));
        }

        if ($value instanceof EntryReference) {
            try {
                $value = $this->container->get($value->getName());
            } catch (DependencyException $e) {
                throw $e;
            } catch (Exception $e) {
                throw new DependencyException(sprintf(
                    "Error while injecting '%s' in %s::%s. %s",
                    $value->getName(),
                    get_class($object),
                    $propertyName,
                    $e->getMessage()
                ), 0, $e);
            }
        }

        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * Returns the default value of a function parameter.
     *
     * @param ReflectionParameter $reflectionParameter
     * @param ReflectionMethod    $reflectionMethod
     *
     * @throws DefinitionException Can't get default values from PHP internal classes and methods
     * @return mixed
     */
    private function getParameterDefaultValue(
        ReflectionParameter $reflectionParameter,
        ReflectionMethod $reflectionMethod
    ) {
        try {
            return $reflectionParameter->getDefaultValue();
        } catch (ReflectionException $e) {
            throw new DefinitionException(sprintf(
                "The parameter '%s' of %s::%s has no type defined or guessable. It has a default value, "
                . "but the default value can't be read through Reflection because it is a PHP internal class.",
                $reflectionParameter->getName(),
                $reflectionMethod->getDeclaringClass()->getName(),
                $reflectionMethod->getName()
            ));
        }
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
