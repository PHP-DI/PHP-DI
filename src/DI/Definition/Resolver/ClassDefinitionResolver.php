<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Resolver;

use DI\Definition\ClassDefinition;
use DI\Definition\Definition;
use DI\Definition\EntryReference;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\ClassDefinition\MethodInjection;
use DI\Definition\ClassDefinition\PropertyInjection;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Interop\Container\ContainerInterface;
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
    public function resolve(Definition $definition, array $parameters = array())
    {
        if (! $definition instanceof ClassDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition resolver is only compatible with ClassDefinition objects, %s given',
                get_class($definition)
            ));
        }

        // Lazy?
        if ($definition->isLazy()) {
            return $this->createProxy($definition, $parameters);
        }

        return $this->createInstance($definition, $parameters);
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
            $this->injectMethodsAndProperties($classDefinition, $instance, $classDefinition);
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
     * @param array           $parameters
     *
     * @return object Proxy instance
     */
    private function createProxy(ClassDefinition $definition, array $parameters)
    {
        // waiting for PHP 5.4+ support
        $resolver = $this;

        /** @noinspection PhpUnusedParameterInspection */
        $proxy = $this->proxyFactory->createProxy(
            $definition->getClassName(),
            function (& $wrappedObject, $proxy, $method, $parameters, & $initializer) use ($resolver, $definition, $parameters) {
                $wrappedObject = $resolver->createInstance($definition, $parameters);
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
     * @param array           $parameters      Optional parameters to use to create the instance.
     *
     * @throws DefinitionException
     * @throws DependencyException
     * @return object
     *
     * @todo Make private once PHP-DI supports PHP > 5.4 only
     */
    public function createInstance(ClassDefinition $classDefinition, array $parameters)
    {
        if (! class_exists($classDefinition->getClassName()) && ! interface_exists($classDefinition->getClassName())) {
            throw DefinitionException::create($classDefinition, sprintf(
                "the class %s doesn't exist",
                $classDefinition->getClassName()
            ));
        }

        $classReflection = new ReflectionClass($classDefinition->getClassName());

        if (!$classReflection->isInstantiable()) {
            throw DefinitionException::create($classDefinition, sprintf(
                "%s is not instantiable",
                $classDefinition->getClassName()
            ));
        }

        $constructorInjection = $classDefinition->getConstructorInjection();

        try {
            $instance = $this->injectConstructor(
                $classDefinition,
                $classReflection,
                $constructorInjection,
                $parameters
            );
            $this->injectMethodsAndProperties($classDefinition, $instance, $classDefinition);
        } catch (NotFoundException $e) {
            throw new DependencyException(sprintf(
                "Error while injecting dependencies into %s: %s",
                $classReflection->getName(),
                $e->getMessage()
            ), 0, $e);
        }

        return $instance;
    }

    /**
     * Creates an instance and injects dependencies through the constructor.
     *
     * @param ClassDefinition      $definition
     * @param ReflectionClass      $classReflection
     * @param MethodInjection|null $constructorInjection
     * @param array                $parameters           Force so parameters to specific values.
     *
     * @throws DefinitionException
     * @return object
     */
    private function injectConstructor(
        ClassDefinition $definition,
        ReflectionClass $classReflection,
        MethodInjection $constructorInjection = null,
        array $parameters = array()
    ) {
        $args = $this->prepareMethodParameters(
            $definition,
            $constructorInjection,
            $classReflection->getConstructor(),
            $parameters
        );

        return $classReflection->newInstanceArgs($args);
    }

    /**
     * @param ClassDefinition $definition
     * @param object          $instance
     * @param ClassDefinition $classDefinition
     */
    private function injectMethodsAndProperties(
        ClassDefinition $definition,
        $instance,
        ClassDefinition $classDefinition
    ) {
        // Property injections
        foreach ($classDefinition->getPropertyInjections() as $propertyInjection) {
            $this->injectProperty($instance, $propertyInjection);
        }
        // Method injections
        foreach ($classDefinition->getMethodInjections() as $methodInjection) {
            $this->injectMethod($definition, $instance, $methodInjection);
        }
    }

    /**
     * Inject dependencies through methods.
     *
     * @param ClassDefinition $definition
     * @param object          $object Object to inject dependencies into
     * @param MethodInjection $methodInjection
     *
     * @throws DependencyException
     * @throws DefinitionException
     */
    private function injectMethod(ClassDefinition $definition, $object, MethodInjection $methodInjection)
    {
        $methodReflection = new ReflectionMethod($object, $methodInjection->getMethodName());

        $args = $this->prepareMethodParameters($definition, $methodInjection, $methodReflection);

        $methodReflection->invokeArgs($object, $args);
    }

    /**
     * Create the parameter array to call a method.
     *
     * @param ClassDefinition  $definition
     * @param MethodInjection  $methodInjection
     * @param ReflectionMethod $methodReflection
     * @param array            $parameters       Force some parameters to specific values.
     *
     * @throws DefinitionException A parameter has no defined or guessable value.
     * @return array Array of parameters to use to call the method.
     */
    private function prepareMethodParameters(
        ClassDefinition $definition,
        MethodInjection $methodInjection = null,
        ReflectionMethod $methodReflection = null,
        array $parameters = array()
    ) {
        if (! $methodReflection) {
            return array();
        }

        $args = array();

        foreach ($methodReflection->getParameters() as $index => $parameter) {
            if (array_key_exists($parameter->getName(), $parameters)) {
                // Look in the $parameters array
                $value = $parameters[$parameter->getName()];
            } elseif ($methodInjection && $methodInjection->hasParameter($index)) {
                // Look in the definition
                $value = $methodInjection->getParameter($index);
            } else {
                // If the parameter is optional and wasn't specified, we take its default value
                if ($parameter->isOptional()) {
                    $args[] = $this->getParameterDefaultValue($parameter, $methodReflection);
                    continue;
                }

                throw DefinitionException::create($definition, sprintf(
                    "The parameter '%s' of %s::%s has no value defined or guessable",
                    $parameter->getName(),
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

        if (! $property->isPublic()) {
            $property->setAccessible(true);
        }
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
