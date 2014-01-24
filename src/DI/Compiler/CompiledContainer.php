<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Compiler;

use DI\Compiler\Backend\Backend;
use DI\Container;
use DI\ContainerInterface;
use DI\Definition\DefinitionManager;
use DI\DependencyException;
use Exception;
use InvalidArgumentException;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

/**
 * Container where definitions are compiled to PHP code for optimal performances.
 */
class CompiledContainer extends Container
{
    /**
     * @var Compiler
     */
    protected $compiler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * @var DefinitionManager
     */
    protected $definitionManager;

    /**
     * @var ContainerInterface
     */
    private $wrapperContainer;

    /**
     * @param DefinitionManager             $definitionManager Definitions are used in case a compiled
     *                                                         definition doesn't exist.
     * @param LazyLoadingValueHolderFactory $proxyFactory
     * @param Compiler                      $compiler          Compiler.
     * @param Backend                       $backend           Compiler backend.
     * @param ContainerInterface            $wrapperContainer  If the container is wrapped by another container.
     */
    public function __construct(
        DefinitionManager $definitionManager,
        LazyLoadingValueHolderFactory $proxyFactory,
        Compiler $compiler,
        Backend $backend,
        ContainerInterface $wrapperContainer = null
    ) {
        parent::__construct($definitionManager, $proxyFactory, $wrapperContainer);

        $this->compiler = $compiler;
        $this->backend = $backend;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        // TODO that's some duplicated code, needs to be better!
        if (! is_string($name)) {
            throw new InvalidArgumentException("The name parameter must be of type string");
        }

        // Try to find the entry in the map
        if (array_key_exists($name, $this->singletonEntries)) {
            return $this->singletonEntries[$name];
        }

        // Entry not loaded, use the definitions
        if (! $this->backend->hasCompiledEntry($name)) {
            $definition = $this->definitionManager->getDefinition($name);

            // Compile
            $this->compiler->compileDefinition($definition);
        }

        $container = $this->wrapperContainer ?: $this;

        // Check if we are already getting this entry -> circular dependency
        if (isset($this->entriesBeingResolved[$name])) {
            throw new DependencyException("Circular dependency detected while trying to get entry '$name'");
        }
        $this->entriesBeingResolved[$name] = true;

        // Resolve the definition
        try {
            $value = $this->backend->readCompiledEntry($name, $container);
        } catch (Exception $exception) {
            unset($this->entriesBeingResolved[$name]);
            throw $exception;
        }

        unset($this->entriesBeingResolved[$name]);

        // If the entry is singleton, we store it to always return it without recomputing it.
        if ($value instanceof SharedEntry) {
            $value = $value->getValue();
            $this->singletonEntries[$name] = $value;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException("The name parameter must be of type string");
        }

        return array_key_exists($name, $this->singletonEntries)
            || $this->backend->hasCompiledEntry($name)
            || $this->definitionManager->getDefinition($name);
    }
}
