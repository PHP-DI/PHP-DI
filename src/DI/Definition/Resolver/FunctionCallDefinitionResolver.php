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
use DI\Definition\ClassDefinition;
use DI\Definition\Definition;
use DI\Definition\EntryReference;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\FunctionCallDefinition;
use Interop\Container\ContainerInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionParameter;

/**
 * Resolves a factory definition to a value.
 *
 * @since 4.2
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class FunctionCallDefinitionResolver implements DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * The resolver needs a container. This container will be used to fetch dependencies.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve a function call definition to a value.
     *
     * This will call the function and return its result.
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = array())
    {
        if (! $definition instanceof AbstractFunctionCallDefinition) {
            throw new \InvalidArgumentException(
                sprintf(
                    'This definition resolver is only compatible with AbstractFunctionCallDefinition objects, %s given',
                    get_class($definition)
                )
            );
        }

        $functionReflection = $definition->getReflection();

        $args = $this->prepareMethodParameters($definition, $functionReflection, $parameters);

        if ($functionReflection instanceof \ReflectionMethod) {
            $functionReflection->invokeArgs($object, $args);
        } else {
            /** @var $functionReflection ReflectionFunction */
            return $functionReflection->invokeArgs($args);
        }
    }

    /**
     * Create the parameter array to call a method.
     *
     * @param AbstractFunctionCallDefinition $definition
     * @param ReflectionFunctionAbstract     $functionReflection
     * @param array                          $parameters Force some parameters to specific values.
     *
     * @throws DefinitionException A parameter has no defined or guessable value.
     * @return array Array of parameters to use to call the method.
     */
    private function prepareMethodParameters(
        AbstractFunctionCallDefinition $definition,
        ReflectionFunctionAbstract $functionReflection,
        array $parameters = array()
    ) {
        $args = array();

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

                throw DefinitionException::create($definition, sprintf(
                    "The parameter '%s' of %s has no value defined or guessable",
                    $parameter->getName(),
                    $this->getFunctionName($functionReflection)
                ));
            }

            if ($value instanceof EntryReference) {
                $args[] = $this->container->get($value->getName());
            } else {
                $args[] = $value;
            }
        }

        return $args;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns the default value of a function parameter.
     *
     * @param ReflectionParameter        $parameter
     * @param ReflectionFunctionAbstract $function
     *
     * @throws DefinitionException Can't get default values from PHP internal classes and methods
     * @return mixed
     */
    private function getParameterDefaultValue(
        ReflectionParameter $parameter,
        ReflectionFunctionAbstract $function
    ) {
        try {
            return $parameter->getDefaultValue();
        } catch (ReflectionException $e) {
            throw new DefinitionException(sprintf(
                "The parameter '%s' of %s has no type defined or guessable. It has a default value, "
                . "but the default value can't be read through Reflection because it is a PHP internal class.",
                $parameter->getName(),
                $this->getFunctionName($function)
            ));
        }
    }

    private function getFunctionName(ReflectionFunctionAbstract $reflectionFunction)
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
