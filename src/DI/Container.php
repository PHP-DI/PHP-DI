<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use DI\Definition\ClassDefinition;
use DI\Definition\Definition;
use DI\Definition\FactoryDefinition;
use DI\Definition\InstanceDefinition;
use DI\Definition\Resolver\ResolverDispatcher;
use DI\Definition\Source\CachedDefinitionSource;
use DI\Definition\Source\CallableDefinitionSource;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\Source\MutableDefinitionSource;
use DI\Definition\Source\ReflectionDefinitionSource;
use DI\Definition\Helper\DefinitionHelper;
use DI\Definition\Resolver\DefinitionResolver;
use DI\Proxy\ProxyFactory;
use Exception;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;

/**
 * Dependency Injection Container.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Container implements ContainerInterface, FactoryInterface, InvokerInterface
{
    /**
     * Map of entries with Singleton scope that are already resolved.
     * @var array
     */
    private $singletonEntries = array();

    /**
     * @var DefinitionSource
     */
    private $definitionSource;

    /**
     * @var DefinitionResolver
     */
    private $definitionResolver;

    /**
     * Array of entries being resolved. Used to avoid circular dependencies and infinite loops.
     * @var array
     */
    private $entriesBeingResolved = array();

    /**
     * @var CallableDefinitionSource
     */
    private $callableDefinitionSource;

    /**
     * Use the ContainerBuilder to ease constructing the Container.
     *
     * @see ContainerBuilder
     *
     * @param DefinitionSource   $definitionSource
     * @param ProxyFactory       $proxyFactory
     * @param ContainerInterface $wrapperContainer If the container is wrapped by another container.
     */
    public function __construct(
        DefinitionSource $definitionSource,
        ProxyFactory $proxyFactory,
        ContainerInterface $wrapperContainer = null
    ) {
        $wrapperContainer = $wrapperContainer ?: $this;

        $this->definitionSource = $definitionSource;
        $this->definitionResolver = ResolverDispatcher::createDefault($wrapperContainer, $proxyFactory);
        $this->callableDefinitionSource = new ReflectionDefinitionSource();

        // Auto-register the container
        $this->singletonEntries['DI\Container'] = $this;
        $this->singletonEntries['DI\ContainerInterface'] = $this;
        $this->singletonEntries['DI\FactoryInterface'] = $this;
        $this->singletonEntries['DI\InvokerInterface'] = $this;
    }

    /**
     * Returns an entry of the container by its name.
     *
     * @param string $name Entry name or a class name.
     *
     * @throws InvalidArgumentException The name parameter must be of type string.
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     * @return mixed
     */
    public function get($name)
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException(sprintf(
                'The name parameter must be of type string, %s given',
                is_object($name) ? get_class($name) : gettype($name)
            ));
        }

        // Try to find the entry in the singleton map
        if (array_key_exists($name, $this->singletonEntries)) {
            return $this->singletonEntries[$name];
        }

        $definition = $this->definitionSource->getDefinition($name);
        if (! $definition) {
            throw new NotFoundException("No entry or class found for '$name'");
        }

        $value = $this->resolveDefinition($definition);

        // If the entry is singleton, we store it to always return it without recomputing it
        if ($definition->getScope() == Scope::SINGLETON()) {
            $this->singletonEntries[$name] = $value;
        }

        return $value;
    }

    /**
     * Build an entry of the container by its name.
     *
     * This method behave like get() except it forces the scope to "prototype",
     * which means the definition of the entry will be re-evaluated each time.
     * For example, if the entry is a class, then a new instance will be created each time.
     *
     * This method makes the container behave like a factory.
     *
     * @param string $name       Entry name or a class name.
     * @param array  $parameters Optional parameters to use to build the entry. Use this to force specific parameters
     *                           to specific values. Parameters not defined in this array will be resolved using
     *                           the container.
     *
     * @throws InvalidArgumentException The name parameter must be of type string.
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     * @return mixed
     */
    public function make($name, array $parameters = array())
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException(sprintf(
                'The name parameter must be of type string, %s given',
                is_object($name) ? get_class($name) : gettype($name)
            ));
        }

        $definition = $this->definitionSource->getDefinition($name);
        if (! $definition) {
            // Try to find the entry in the singleton map
            if (array_key_exists($name, $this->singletonEntries)) {
                return $this->singletonEntries[$name];
            }

            throw new NotFoundException("No entry or class found for '$name'");
        }

        return $this->resolveDefinition($definition, $parameters);
    }

    /**
     * Test if the container can provide something for the given name.
     *
     * @param string $name Entry name or a class name.
     *
     * @throws InvalidArgumentException The name parameter must be of type string.
     * @return bool
     */
    public function has($name)
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException(sprintf(
                'The name parameter must be of type string, %s given',
                is_object($name) ? get_class($name) : gettype($name)
            ));
        }

        if (array_key_exists($name, $this->singletonEntries)) {
            return true;
        }

        $definition = $this->definitionSource->getDefinition($name);
        if ($definition === null) {
            return false;
        }

        return $this->definitionResolver->isResolvable($definition);
    }

    /**
     * Inject all dependencies on an existing instance
     *
     * @param object $instance Object to perform injection upon
     * @throws InvalidArgumentException
     * @throws DependencyException Error while injecting dependencies
     * @return object $instance Returns the same instance
     */
    public function injectOn($instance)
    {
        $classDefinition = $this->definitionSource->getDefinition(get_class($instance));
        if (! $classDefinition instanceof ClassDefinition) {
            return $instance;
        }

        $definition = new InstanceDefinition($instance, $classDefinition);

        $this->definitionResolver->resolve($definition);

        return $instance;
    }

    /**
     * Call the given function using the given parameters.
     *
     * Missing parameters will be resolved from the container.
     *
     * @param callable $callable   Function to call.
     * @param array    $parameters Parameters to use. Must be an array indexed by the parameter names.
     *
     * @return mixed Result of the function.
     */
    public function call($callable, array $parameters = array())
    {
        $definition = $this->callableDefinitionSource->getCallableDefinition($callable);

        return $this->definitionResolver->resolve($definition, $parameters);
    }

    /**
     * Define an object or a value in the container.
     *
     * @param string                 $name  Entry name
     * @param mixed|DefinitionHelper $value Value, use definition helpers to define objects
     */
    public function set($name, $value)
    {
        if ($value instanceof DefinitionHelper) {
            $value = $value->getDefinition($name);
        } elseif ($value instanceof \Closure) {
            $value = new FactoryDefinition($name, $value);
        }

        if ($value instanceof Definition) {
            $this->setDefinition($name, $value);
        } else {
            $this->singletonEntries[$name] = $value;
        }
    }

    /**
     * Resolves a definition.
     *
     * Checks for circular dependencies while resolving the definition.
     *
     * @param Definition $definition
     * @param array      $parameters
     *
     * @throws DependencyException Error while resolving the entry.
     * @return mixed
     */
    private function resolveDefinition(Definition $definition, array $parameters = array())
    {
        $entryName = $definition->getName();

        // Check if we are already getting this entry -> circular dependency
        if (isset($this->entriesBeingResolved[$entryName])) {
            throw new DependencyException("Circular dependency detected while trying to resolve entry '$entryName'");
        }
        $this->entriesBeingResolved[$entryName] = true;

        // Resolve the definition
        try {
            $value = $this->definitionResolver->resolve($definition, $parameters);
        } catch (Exception $exception) {
            unset($this->entriesBeingResolved[$entryName]);
            throw $exception;
        }

        unset($this->entriesBeingResolved[$entryName]);

        return $value;
    }

    private function setDefinition($name, Definition $definition)
    {
        if ($this->definitionSource instanceof CachedDefinitionSource) {
            throw new \LogicException('You cannot set a definition at runtime on a container that has a cache configured. Doing so would risk caching the definition for the next execution, where it might be different. You can either put your definitions in a file, remove the cache or ->set() a raw value directly (PHP object, string, int, ...) instead of a PHP-DI definition.');
        }

        if (! $this->definitionSource instanceof MutableDefinitionSource) {
            // This can happen if you instantiate the container yourself
            throw new \LogicException('The container has not been initialized correctly');
        }

        // Clear existing entry if it exists
        if (array_key_exists($name, $this->singletonEntries)) {
            unset($this->singletonEntries[$name]);
        }

        $this->definitionSource->addDefinition($definition);
    }
}
