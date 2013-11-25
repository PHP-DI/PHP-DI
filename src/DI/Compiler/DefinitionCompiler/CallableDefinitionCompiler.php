<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Compiler\DefinitionCompiler;

use DI\Compiler\CompilationException;
use DI\Definition\CallableDefinition;
use DI\Definition\Definition;
use Jeremeamia\SuperClosure\ClosureParser;

/**
 * Compiles a CallableDefinition to PHP code.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class CallableDefinitionCompiler implements DefinitionCompiler
{
    /**
     * {@inheritdoc}
     */
    public function compile(Definition $definition)
    {
        if (! $definition instanceof CallableDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition compiler is only compatible with CallableDefinition objects, %s given',
                get_class($definition)
            ));
        }

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

    private function compileArrayCallable($callable, CallableDefinition $definition)
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

    private function compileClosure($closure, CallableDefinition $definition)
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
