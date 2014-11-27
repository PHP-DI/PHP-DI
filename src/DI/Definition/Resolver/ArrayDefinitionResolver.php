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
use DI\Definition\EntryReference;
use DI\DependencyException;
use Exception;
use Interop\Container\ContainerInterface;

/**
 * Resolves an array definition to a value.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayDefinitionResolver implements DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * The resolver needs a container.
     * This container will be used to get the entry to which the alias points to.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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

        $values = $this->resolveAliases($definition, $values);

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

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
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

    private function resolveAliases(ArrayDefinition $definition, array $values)
    {
        foreach ($values as $key => $value) {
            if ($value instanceof EntryReference) {
                $values[$key] = $this->resolveReference($value, $definition, $key);
            }
        }

        return $values;
    }

    private function resolveReference(EntryReference $reference, ArrayDefinition $definition, $key)
    {
        try {
            return $this->container->get($reference->getName());
        } catch (DependencyException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new DependencyException(sprintf(
                "Error while resolving '%s' in %s[%s]. %s",
                $reference->getName(),
                $definition->getName(),
                $key,
                $e->getMessage()
            ), 0, $e);
        }
    }
}
