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
use DI\Definition\Exception\DefinitionException;
use DI\Definition\ClassDefinition\PropertyInjection;
use DI\Definition\Helper\DefinitionHelper;
use DI\DependencyException;
use DI\Proxy\ProxyFactory;
use Exception;
use Interop\Container\Exception\NotFoundException;
use ReflectionClass;
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
     * @var ProxyFactory
     */
    private $proxyFactory;

    /**
     * @var ParameterResolver
     */
    private $parameterResolver;

    /**
     * @var DefinitionResolver
     */
    private $definitionResolver;

    /**
     * @param DefinitionResolver $definitionResolver Used to resolve nested definitions.
     * @param ProxyFactory       $proxyFactory       Used to create proxies for lazy injections.
     */
    public function __construct(
        DefinitionResolver $definitionResolver,
        ProxyFactory $proxyFactory
    ) {
        $this->definitionResolver = $definitionResolver;
        $this->proxyFactory = $proxyFactory;
        $this->parameterResolver = new ParameterResolver($definitionResolver);
    }

    /**
     * Resolve a class definition to a value.
     *
     * This will create a new instance of the class using the injections points defined.
     *
     * @param ClassDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = array())
    {
        $this->assertIsClassDefinition($definition);

        // Lazy?
        if ($definition->isLazy()) {
            return $this->createProxy($definition, $parameters);
        }

        return $this->createInstance($definition, $parameters);
    }

    /**
     * The definition is not resolvable if the class is not instantiable (interface or abstract)
     * or if the class doesn't exist.
     *
     * @param ClassDefinition $definition
     *
     * {@inheritdoc}
     */
    public function isResolvable(Definition $definition, array $parameters = array())
    {
        $this->assertIsClassDefinition($definition);

        if (! class_exists($definition->getClassName())) {
            return false;
        }

        $classReflection = new ReflectionClass($definition->getClassName());

        return $classReflection->isInstantiable();
    }

    /**
     * Returns a proxy instance
     *
     * @param ClassDefinition $definition
     * @param array           $parameters
     *
     * @return \ProxyManager\Proxy\LazyLoadingInterface Proxy instance
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
        $this->assertClassExists($classDefinition);

        $classReflection = new ReflectionClass($classDefinition->getClassName());

        $this->assertClassIsInstantiable($classDefinition, $classReflection);

        $constructorInjection = $classDefinition->getConstructorInjection();

        try {
            $args = $this->parameterResolver->resolveParameters(
                $constructorInjection,
                $classReflection->getConstructor(),
                $parameters
            );

            if (count($args) > 0) {
                $object = $classReflection->newInstanceArgs($args);
            } else {
                $object = $classReflection->newInstance();
            }

            $this->injectMethodsAndProperties($object, $classDefinition);
        } catch (NotFoundException $e) {
            throw new DependencyException(sprintf(
                "Error while injecting dependencies into %s: %s",
                $classReflection->getName(),
                $e->getMessage()
            ), 0, $e);
        } catch (DefinitionException $e) {
            throw DefinitionException::create($classDefinition, sprintf(
                "Entry %s cannot be resolved: %s",
                $classDefinition->getName(),
                $e->getMessage()
            ));
        }

        return $object;
    }

    protected function injectMethodsAndProperties($object, ClassDefinition $classDefinition)
    {
        // Property injections
        foreach ($classDefinition->getPropertyInjections() as $propertyInjection) {
            $this->injectProperty($object, $propertyInjection);
        }

        // Method injections
        foreach ($classDefinition->getMethodInjections() as $methodInjection) {
            $methodReflection = new \ReflectionMethod($object, $methodInjection->getMethodName());
            $args = $this->parameterResolver->resolveParameters($methodInjection, $methodReflection);

            $methodReflection->invokeArgs($object, $args);
        }
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

        if ($value instanceof DefinitionHelper) {
            /** @var Definition $nestedDefinition */
            $nestedDefinition = $value->getDefinition('');

            try {
                $value = $this->definitionResolver->resolve($nestedDefinition);
            } catch (DependencyException $e) {
                throw $e;
            } catch (Exception $e) {
                throw new DependencyException(sprintf(
                    "Error while injecting in %s::%s. %s",
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

    private function assertIsClassDefinition(Definition $definition)
    {
        if (!$definition instanceof ClassDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition resolver is only compatible with ClassDefinition objects, %s given',
                get_class($definition)
            ));
        }
    }

    private function assertClassExists(ClassDefinition $classDefinition)
    {
        if (!class_exists($classDefinition->getClassName()) && !interface_exists($classDefinition->getClassName())) {
            throw DefinitionException::create($classDefinition,
            sprintf(
                "Entry %s cannot be resolved: class %s doesn't exist",
                $classDefinition->getName(),
                $classDefinition->getClassName()
            ));
        }
    }

    private function assertClassIsInstantiable(ClassDefinition $classDefinition, ReflectionClass $classReflection)
    {
        if (!$classReflection->isInstantiable()) {
            throw DefinitionException::create($classDefinition,
            sprintf(
                "Entry %s cannot be resolved: class %s is not instantiable",
                $classDefinition->getName(),
                $classDefinition->getClassName()
            ));
        }
    }
}
