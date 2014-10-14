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
use DI\Definition\EntryReference;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Exception\DefinitionException;
use Interop\Container\ContainerInterface;

/**
 * Resolves a environment variable definition to a value.
 *
 * @author James Harris <james.harris@icecave.com.au>
 */
class EnvironmentVariableDefinitionResolver implements DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var callable
     */
    private $variableReader;

    public function __construct(ContainerInterface $container, $variableReader = 'getenv')
    {
        $this->container = $container;
        $this->variableReader = $variableReader;
    }

    /**
     * Resolve an environment variable definition to a value.
     *
     * @param EnvironmentVariableDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = array())
    {
        $this->assertIsEnvironmentVariableDefinition($definition);

        $value = call_user_func($this->variableReader, $definition->getVariableName());

        if (false !== $value) {
            return $value;
        } elseif (!$definition->isOptional()) {
            throw new DefinitionException(sprintf(
                "The environment variable '%s' has not been defined",
                $definition->getVariableName()
            ));
        }

        $value = $definition->getDefaultValue();

        if ($value instanceof EntryReference) {
            return $this->container->get($value->getName());
        }

        return $value;
    }

    /**
     * @param EnvironmentVariableDefinition $definition
     *
     * {@inheritdoc}
     */
    public function isResolvable(Definition $definition, array $parameters = array())
    {
        $this->assertIsEnvironmentVariableDefinition($definition);

        return $definition->isOptional()
            || false !== call_user_func($this->variableReader, $definition->getVariableName());
    }

    private function assertIsEnvironmentVariableDefinition(Definition $definition)
    {
        if (!$definition instanceof EnvironmentVariableDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition resolver is only compatible with EnvironmentVariableDefinition objects, %s given',
                get_class($definition)
            ));
        }
    }
}
