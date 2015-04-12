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
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\Helper\DefinitionHelper;

/**
 * Resolves a environment variable definition to a value.
 *
 * @author James Harris <james.harris@icecave.com.au>
 */
class EnvironmentVariableResolver implements DefinitionResolver
{
    /**
     * @var DefinitionResolver
     */
    private $definitionResolver;

    /**
     * @var callable
     */
    private $variableReader;

    public function __construct(DefinitionResolver $definitionResolver, $variableReader = 'getenv')
    {
        $this->definitionResolver = $definitionResolver;
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

        // Nested definition
        if ($value instanceof DefinitionHelper) {
            return $this->definitionResolver->resolve($value->getDefinition(''));
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
