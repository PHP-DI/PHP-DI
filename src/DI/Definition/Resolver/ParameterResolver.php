<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Resolver;

use DI\Definition\AbstractFunctionCallDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\Helper\DefinitionHelper;

/**
 * Resolves parameters for a function call.
 *
 * @since 4.2
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ParameterResolver
{
    /**
     * @var DefinitionResolver
     */
    private $definitionResolver;

    /**
     * @param DefinitionResolver $definitionResolver Will be used to resolve nested definitions.
     */
    public function __construct(DefinitionResolver $definitionResolver)
    {
        $this->definitionResolver = $definitionResolver;
    }

    /**
     * @param AbstractFunctionCallDefinition $definition
     * @param \ReflectionFunctionAbstract    $functionReflection
     * @param array                          $parameters
     *
     * @throws DefinitionException A parameter has no value defined or guessable.
     * @return array Parameters to use to call the function.
     */
    public function resolveParameters(
        AbstractFunctionCallDefinition $definition = null,
        \ReflectionFunctionAbstract $functionReflection = null,
        array $parameters = array()
    ) {
        $args = array();

        if (! $functionReflection) {
            return $args;
        }

        foreach ($functionReflection->getParameters() as $index => $parameter) {
            if (array_key_exists($parameter->getName(), $parameters)) {
                // Look in the $parameters array
                $value = $parameters[$parameter->getName()];
            } elseif ($definition && $definition->hasParameter($index)) {
                // Look in the definition
                $value = $definition->getParameter($index);
            } else {
                // If the parameter is optional and wasn't specified, we take its default value
                if ($parameter->isOptional()) {
                    $args[] = $this->getParameterDefaultValue($parameter, $functionReflection);
                    continue;
                }

                throw new DefinitionException(sprintf(
                    "The parameter '%s' of %s has no value defined or guessable",
                    $parameter->getName(),
                    $this->getFunctionName($functionReflection)
                ));
            }

            if ($value instanceof DefinitionHelper) {
                $nestedDefinition = $value->getDefinition('');

                // If the container cannot produce the entry, we can use the default parameter value
                if (!$this->definitionResolver->isResolvable($nestedDefinition) && $parameter->isOptional()) {
                    $value = $this->getParameterDefaultValue($parameter, $functionReflection);
                } else {
                    $value = $this->definitionResolver->resolve($nestedDefinition);
                }
            }

            $args[] = $value;
        }

        return $args;
    }

    /**
     * Returns the default value of a function parameter.
     *
     * @param \ReflectionParameter        $parameter
     * @param \ReflectionFunctionAbstract $function
     *
     * @throws DefinitionException Can't get default values from PHP internal classes and functions
     * @return mixed
     */
    private function getParameterDefaultValue(
        \ReflectionParameter $parameter,
        \ReflectionFunctionAbstract $function
    ) {
        try {
            return $parameter->getDefaultValue();
        } catch (\ReflectionException $e) {
            throw new DefinitionException(sprintf(
                "The parameter '%s' of %s has no type defined or guessable. It has a default value, "
                . "but the default value can't be read through Reflection because it is a PHP internal class.",
                $parameter->getName(),
                $this->getFunctionName($function)
            ));
        }
    }

    private function getFunctionName(\ReflectionFunctionAbstract $reflectionFunction)
    {
        if ($reflectionFunction instanceof \ReflectionMethod) {
            return sprintf(
                '%s::%s',
                $reflectionFunction->getDeclaringClass()->getName(),
                $reflectionFunction->getName()
            );
        } elseif ($reflectionFunction->isClosure()) {
            return sprintf(
                'closure defined in %s at line %d',
                $reflectionFunction->getFileName(),
                $reflectionFunction->getStartLine()
            );
        }

        return $reflectionFunction->getName();
    }
}
