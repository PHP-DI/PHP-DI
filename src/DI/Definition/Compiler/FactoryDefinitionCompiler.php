<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Compiler;

use DI\Compiler\CompilationException;
use DI\Definition\Definition;
use DI\Definition\FactoryDefinition;
use Jeremeamia\SuperClosure\ClosureParser;

/**
 * Compiles a FactoryDefinition to PHP code.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class FactoryDefinitionCompiler implements DefinitionCompiler
{
    /**
     * @param FactoryDefinition $definition
     *
     * {@inheritdoc}
     */
    public function compile(Definition $definition)
    {
        $callable = $definition->getCallable();

        if (is_array($callable)) {
            $code = $this->compileArrayCallable($callable, $definition);
        } elseif ($callable instanceof \Closure) {
            $code = $this->compileClosure($callable, $definition);
        } else {
            throw new CompilationException(sprintf(
                "Unable to compile entry '%s', a factory must be a callable (closure or array)",
                $definition->getName()
            ));
        }

        // The factory function is called with the container as parameter
        $code .= PHP_EOL . 'return $factory($this);';

        return $code;
    }

    private function compileArrayCallable($callable, FactoryDefinition $definition)
    {
        list($class, $method) = $callable;

        if (! is_string($class)) {
            throw new CompilationException(sprintf(
                "The callable definition for entry '%s' must be a closure or an array of strings "
                . "(no object in the array)",
                $definition->getName()
            ));
        }

        return sprintf('$factory = array(%s, %s);', var_export($class, true), var_export($method, true));
    }

    private function compileClosure($closure, FactoryDefinition $definition)
    {
        // Uses jeremeamia/super_closure
        $closureParser = ClosureParser::fromClosure($closure);

        if (count($closureParser->getUsedVariables()) > 0) {
            throw new CompilationException(sprintf(
                "Unable to compile entry '%s' because the closure has a 'use (\$var)' statement,"
                . " you should rather use the container which is passed as the first parameter to the closure."
                . " Example: function (\\DI\\Container \$c) { ... }",
                $definition->getName()
            ));
        }

        return sprintf('$factory = %s', $closureParser->getCode());
    }
}
