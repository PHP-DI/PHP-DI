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
use DI\Definition\Compiler\DefinitionCompiler;
use DI\Definition\Definition;

class Compiler
{
    /**
     * @var Backend
     */
    private $backend;

    /**
     * @var DefinitionCompiler[]
     */
    private $definitionCompilers;

    /**
     * @param Backend              $backend             Backend that will store compiled definitions.
     * @param DefinitionCompiler[] $definitionCompilers Array of compilers indexed by each type of definition.
     */
    public function __construct(Backend $backend, array $definitionCompilers)
    {
        $this->backend = $backend;
        $this->definitionCompilers = $definitionCompilers;
    }

    /**
     * Compiles a set of definitions.
     *
     * @param Definition[] $definitions
     */
    public function compileDefinitions(array $definitions)
    {
        foreach ($definitions as $definition) {
            $this->compileDefinition($definition);
        }
    }

    /**
     * Compiles a definition.
     *
     * @param Definition $definition
     */
    public function compileDefinition(Definition $definition)
    {
        $compiler = $this->getDefinitionCompiler($definition);

        $code = $compiler->compile($definition);

        $this->backend->writeCompiledEntry($definition->getName(), $code);
    }

    /**
     * Returns a compiler capable of handling the given definition.
     *
     * @param Definition $definition
     * @throws \RuntimeException No definition resolver was found for this type of definition.
     * @return DefinitionCompiler
     */
    private function getDefinitionCompiler(Definition $definition)
    {
        $type = get_class($definition);

        if (! isset($this->definitionCompilers[$type])) {
            throw new \RuntimeException("No definition compiler was configured for definition of type $type");
        }

        return $this->definitionCompilers[$type];
    }
}
