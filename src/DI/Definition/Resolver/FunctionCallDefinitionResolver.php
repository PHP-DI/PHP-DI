<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Resolver;

use DI\Definition\Definition;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\FunctionCallDefinition;
use DI\Reflection\CallableReflectionFactory;
use Interop\Container\ContainerInterface;

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
     * @var ParameterResolver
     */
    private $parameterResolver;

    /**
     * The resolver needs a container. This container will be used to fetch dependencies.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->parameterResolver = new ParameterResolver($container);
    }

    /**
     * Resolve a function call definition to a value.
     *
     * This will call the function and return its result.
     *
     * @param FunctionCallDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = array())
    {
        $this->assertIsFunctionCallDefinition($definition);

        $callable = $definition->getCallable();

        $functionReflection = CallableReflectionFactory::fromCallable($callable);

        try {
            $args = $this->parameterResolver->resolveParameters($definition, $functionReflection, $parameters);
        } catch (DefinitionException $e) {
            throw DefinitionException::create($definition, $e->getMessage());
        }

        if ($functionReflection instanceof \ReflectionFunction) {
            return $functionReflection->invokeArgs($args);
        }

        /** @var \ReflectionMethod $functionReflection */
        if ($functionReflection->isStatic()) {
            // Static method
            $object = null;
        } elseif (is_object($callable)) {
            // Callable object
            $object = $callable;
        } elseif (is_string($callable)) {
            // Callable class (need to be instantiated)
            $object = $this->container->get($callable);
        } elseif (is_string($callable[0])) {
            // Class method
            $object = $this->container->get($callable[0]);
        } else {
            // Object method
            $object = $callable[0];
        }

        return $functionReflection->invokeArgs($object, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function isResolvable(Definition $definition, array $parameters = array())
    {
        $this->assertIsFunctionCallDefinition($definition);

        return true;
    }

    private function assertIsFunctionCallDefinition(Definition $definition)
    {
        if (!$definition instanceof FunctionCallDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition resolver is only compatible with FunctionCallDefinition objects, %s given',
                get_class($definition)
            ));
        }
    }
}
