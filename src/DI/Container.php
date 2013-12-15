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
use DI\Definition\Definition;
use DI\Definition\DefinitionManager;
use DI\Definition\ValueDefinition;
use DI\DefinitionHelper\DefinitionHelper;
use DI\Definition\Resolver\AliasDefinitionResolver;
use DI\Definition\Resolver\CallableDefinitionResolver;
use DI\Definition\Resolver\ClassDefinitionResolver;
use DI\Definition\Resolver\DefinitionResolver;
use DI\Definition\Resolver\ValueDefinitionResolver;
use Exception;
use InvalidArgumentException;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

/**
 * Dependency Injection Container.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Container implements ContainerInterface
{
    /**
     * Map of entries with Singleton scope
     * @var array
     */
    private $resolvedEntries = array();

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
     * @param ContainerInterface            $wrapperContainer  If the container is wrapped by another container.
     */
    public function __construct(
        DefinitionManager $definitionManager,
        LazyLoadingValueHolderFactory $proxyFactory,
        ContainerInterface $wrapperContainer = null
    ) {
        $this->definitionManager = $definitionManager;

        // Definition resolvers
        $wrapperContainer = $wrapperContainer ?: $this;
        $this->definitionResolvers = array(
            'DI\Definition\ValueDefinition'    => new ValueDefinitionResolver(),
            'DI\Definition\CallableDefinition' => new CallableDefinitionResolver($wrapperContainer),
            'DI\Definition\AliasDefinition'    => new AliasDefinitionResolver($wrapperContainer),
            'DI\Definition\ClassDefinition'    => new ClassDefinitionResolver($wrapperContainer, $proxyFactory),
        );

        // Auto-register the container
        $this->resolvedEntries[get_class($this)] = $this;
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

        // Try to find the entry in the map
        if (array_key_exists($name, $this->resolvedEntries)) {
            return $this->resolvedEntries[$name];
        }

        // Entry not loaded, use the definitions
        $definition = $this->definitionManager->getDefinition($name);

        if (! $definition) {
            throw new NotFoundException("No entry or class found for '$name'");
        }

        $definitionResolver = $this->getDefinitionResolver($definition);

        // Check if we are already getting this entry -> circular dependency
        if (isset($this->entriesBeingResolved[$name])) {
            throw new DependencyException("Circular dependency detected while trying to get entry '$name'");
        }
        $this->entriesBeingResolved[$name] = true;

        // Resolve the definition
        try {
            $value = $definitionResolver->resolve($definition);
        } catch (Exception $exception) {
            unset($this->entriesBeingResolved[$name]);
            throw $exception;
        }

        unset($this->entriesBeingResolved[$name]);

        // If the entry is singleton, we store it to always return it without recomputing it.
        if ($definition->getScope() == Scope::SINGLETON()) {
            $this->resolvedEntries[$name] = $value;
        }

        return $value;
    }

    /**
     * Test if the container can provide something for the given name
     *
     * @param string $name Entry name or a class name
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function has($name)
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException("The name parameter must be of type string");
        }

        return array_key_exists($name, $this->resolvedEntries) || $this->definitionManager->getDefinition($name);
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
     * Define an object or a value in the container
     *
     * @param string                 $name  Entry name
     * @param mixed|DefinitionHelper $value Value, use definition helpers to define objects
     */
    public function set($name, $value)
    {
        // Clear existing entry if it exists
        if (array_key_exists($name, $this->resolvedEntries)) {
            unset($this->resolvedEntries[$name]);
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
     * Returns a resolver capable of handling the given definition.
     *
     * @param Definition $definition
     * @throws \RuntimeException No definition resolver was found for this type of definition.
     * @return \DI\Definition\Resolver\DefinitionResolver
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
