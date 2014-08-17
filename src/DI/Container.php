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
use DI\Definition\DefinitionManager;
use DI\Definition\Resolver\FunctionCallDefinitionResolver;
use DI\Definition\ValueDefinition;
use DI\Definition\Helper\DefinitionHelper;
use DI\Definition\Resolver\AliasDefinitionResolver;
use DI\Definition\Resolver\FactoryDefinitionResolver;
use DI\Definition\Resolver\ClassDefinitionResolver;
use DI\Definition\Resolver\DefinitionResolver;
use DI\Definition\Resolver\ValueDefinitionResolver;
use DI\Definition\Resolver\EnvironmentVariableDefinitionResolver;
use Exception;
use Interop\Container\ContainerInterface as ContainerInteropInterface;
use InvalidArgumentException;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

/**
 * Dependency Injection Container.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Container implements ContainerInteropInterface, ContainerInterface, FactoryInterface, InvokerInterface
{
    /**
     * Map of entries with Singleton scope that are already resolved.
     * @var array
     */
    private $singletonEntries = array();

    /**
     * @var DefinitionManager
     */
    private $definitionManager;

    /**
     * Map of definition resolvers, indexed by the classname of the definition it resolves.
     *
     * @var DefinitionResolver[]
     */
    private $definitionResolvers;

    /**
     * Array of entries being resolved. Used to avoid circular dependencies and infinite loops.
     * @var array
     */
    private $entriesBeingResolved = array();

    /**
     * Use the ContainerBuilder to ease constructing the Container.
     *
     * @see ContainerBuilder
     *
     * @param DefinitionManager             $definitionManager
     * @param LazyLoadingValueHolderFactory $proxyFactory
     * @param ContainerInteropInterface     $wrapperContainer If the container is wrapped by another container.
     */
    public function __construct(
        DefinitionManager $definitionManager,
        LazyLoadingValueHolderFactory $proxyFactory,
        ContainerInteropInterface $wrapperContainer = null
    ) {
        $this->definitionManager = $definitionManager;

        // Definition resolvers
        $wrapperContainer = $wrapperContainer ?: $this;
        $this->definitionResolvers = array(
            'DI\Definition\ValueDefinition'               => new ValueDefinitionResolver(),
            'DI\Definition\FactoryDefinition'             => new FactoryDefinitionResolver($wrapperContainer),
            'DI\Definition\AliasDefinition'               => new AliasDefinitionResolver($wrapperContainer),
            'DI\Definition\ClassDefinition'               => new ClassDefinitionResolver($wrapperContainer, $proxyFactory),
            'DI\Definition\FunctionCallDefinition'        => new FunctionCallDefinitionResolver($wrapperContainer),
            'DI\Definition\EnvironmentVariableDefinition' => new EnvironmentVariableDefinitionResolver($wrapperContainer),
        );

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

        $definition = $this->definitionManager->getDefinition($name);
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

        $definition = $this->definitionManager->getDefinition($name);
        if (! $definition) {
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

        $definition = $this->definitionManager->getDefinition($name);
        if ($definition === null) {
            return false;
        }

        $definitionResolver = $this->getDefinitionResolver($definition);

        return $definitionResolver->isResolvable($definition);
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
        $definition = $this->definitionManager->getDefinition(get_class($instance));
        $definitionResolver = $this->getDefinitionResolver($definition);

        // Check that the definition is a class definition
        if ($definition instanceof ClassDefinition && $definitionResolver instanceof ClassDefinitionResolver) {
            $definitionResolver->injectOnInstance($definition, $instance);
        }

        return $instance;
    }

    /**
     * Call the given function using the given parameters.
     *
     * Missing parameters will be resolved from the container.
     *
     * @param callable $callable   Function to call.
     * @param array    $parameters Parameters to use.
     *
     * @return mixed Result of the function.
     */
    public function call($callable, array $parameters = array())
    {
        $definition = $this->definitionManager->getCallableDefinition($callable);
        $resolver = $this->getDefinitionResolver($definition);

        return $resolver->resolve($definition, $parameters);
    }

    /**
     * Define an object or a value in the container
     *
     * @param string                 $name  Entry name
     * @param mixed|DefinitionHelper $value Value, use definition helpers to define objects
     */
    public function set($name, $value)
    {
        // Clear existing entry if it exists
        if (array_key_exists($name, $this->singletonEntries)) {
            unset($this->singletonEntries[$name]);
        }

        if ($value instanceof DefinitionHelper) {
            $definition = $value->getDefinition($name);
        } else {
            $definition = new ValueDefinition($name, $value);
        }

        $this->definitionManager->addDefinition($definition);
    }

    /**
     * @return DefinitionManager
     */
    public function getDefinitionManager()
    {
        return $this->definitionManager;
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

        $definitionResolver = $this->getDefinitionResolver($definition);

        // Check if we are already getting this entry -> circular dependency
        if (isset($this->entriesBeingResolved[$entryName])) {
            throw new DependencyException("Circular dependency detected while trying to resolve entry '$entryName'");
        }
        $this->entriesBeingResolved[$entryName] = true;

        // Resolve the definition
        try {
            $value = $definitionResolver->resolve($definition, $parameters);
        } catch (Exception $exception) {
            unset($this->entriesBeingResolved[$entryName]);
            throw $exception;
        }

        unset($this->entriesBeingResolved[$entryName]);

        return $value;
    }

    /**
     * Returns a resolver capable of handling the given definition.
     *
     * @param Definition $definition
     *
     * @throws \RuntimeException No definition resolver was found for this type of definition.
     * @return DefinitionResolver
     */
    private function getDefinitionResolver(Definition $definition)
    {
        $definitionType = get_class($definition);

        if (! isset($this->definitionResolvers[$definitionType])) {
            throw new \RuntimeException("No definition resolver was configured for definition of type $definitionType");
        }

        return $this->definitionResolvers[$definitionType];
    }
}
