<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Resolver;

use DI\Definition\AliasDefinition;
use DI\Definition\Definition;
use Interop\Container\ContainerInterface;

/**
 * Resolves an alias definition to a value.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AliasDefinitionResolver implements DefinitionResolver
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
     * Resolve an alias definition to a value.
     *
     * This will return the entry the alias points to.
     *
     * @param AliasDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = array())
    {
        $this->assertIsAliasDefinition($definition);

        return $this->container->get($definition->getTargetEntryName());
    }

    /**
     * {@inheritdoc}
     */
    public function isResolvable(Definition $definition, array $parameters = array())
    {
        $this->assertIsAliasDefinition($definition);

        return true;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    private function assertIsAliasDefinition(Definition $definition)
    {
        if (!$definition instanceof AliasDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition resolver is only compatible with AliasDefinition objects, %s given',
                get_class($definition)
            ));
        }
    }
}
