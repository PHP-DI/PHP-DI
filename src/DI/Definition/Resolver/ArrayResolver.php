<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Resolver;

use DI\Definition\ArrayDefinition;
use DI\Definition\Definition;
use DI\Definition\Helper\DefinitionHelper;
use DI\DependencyException;
use Exception;

/**
 * Resolves an array definition to a value.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayResolver implements DefinitionResolver
{
    /**
     * @var DefinitionResolver
     */
    private $definitionResolver;

    /**
     * @param DefinitionResolver $definitionResolver Used to resolve nested definitions.
     */
    public function __construct(DefinitionResolver $definitionResolver)
    {
        $this->definitionResolver = $definitionResolver;
    }

    /**
     * Resolve an array definition to a value.
     *
     * An array definition can contain simple values or references to other entries.
     *
     * @param ArrayDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = array())
    {
        $this->assertIsArrayDefinition($definition);

        $values = $definition->getValues();

        $values = $this->resolveNestedDefinitions($definition, $values);

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function isResolvable(Definition $definition, array $parameters = array())
    {
        $this->assertIsArrayDefinition($definition);

        return true;
    }

    private function assertIsArrayDefinition(Definition $definition)
    {
        if (!$definition instanceof ArrayDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition resolver is only compatible with ArrayDefinition objects, %s given',
                get_class($definition)
            ));
        }
    }

    private function resolveNestedDefinitions(ArrayDefinition $definition, array $values)
    {
        foreach ($values as $key => $value) {
            if ($value instanceof DefinitionHelper) {
                $values[$key] = $this->resolveDefinition($value, $definition, $key);
            }
        }

        return $values;
    }

    private function resolveDefinition(DefinitionHelper $value, ArrayDefinition $definition, $key)
    {
        try {
            return $this->definitionResolver->resolve($value->getDefinition(''));
        } catch (DependencyException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new DependencyException(sprintf(
                "Error while resolving %s[%s]. %s",
                $definition->getName(),
                $key,
                $e->getMessage()
            ), 0, $e);
        }
    }
}
