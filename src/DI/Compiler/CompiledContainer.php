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
use DI\ContainerInterface;
use DI\Definition\DefinitionManager;
use InvalidArgumentException;

/**
 * Container where definitions are compiled to PHP code for optimal performances.
 */
class CompiledContainer implements ContainerInterface
{
    /**
     * @var Compiler
     */
    private $compiler;

    /**
     * @var Backend
     */
    private $backend;

    /**
     * @var DefinitionManager
     */
    private $definitionManager;

    /**
     * @param Compiler          $compiler          Compiler.
     * @param Backend           $backend           Compiler backend.
     * @param DefinitionManager $definitionManager Definitions are used in case a compiled definition doesn't exist.
     */
    public function __construct(Compiler $compiler, Backend $backend, DefinitionManager $definitionManager)
    {
        $this->compiler = $compiler;
        $this->backend = $backend;
        $this->definitionManager = $definitionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException("The name parameter must be of type string");
        }

        if (! $this->backend->hasCompiledEntry($name)) {
            $definition = $this->definitionManager->getDefinition($name);

            // Compile
            $this->compiler->compileDefinition($definition);
        }

        $this->backend->readCompiledEntry($name, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException("The name parameter must be of type string");
        }

        return $this->backend->hasCompiledEntry($name) || $this->definitionManager->getDefinition($name);
    }
}
