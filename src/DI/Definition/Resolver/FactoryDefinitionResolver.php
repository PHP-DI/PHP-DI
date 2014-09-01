<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Resolver;

use DI\Definition\FactoryDefinition;
use DI\Definition\Definition;
use Interop\Container\ContainerInterface;

/**
 * Resolves a factory definition to a value.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class FactoryDefinitionResolver implements DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * The resolver needs a container. This container will be passed to the factory as a parameter
     * so that the factory can access other entries of the container.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve a factory definition to a value.
     *
     * This will call the callable of the definition.
     *
     * @param FactoryDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = array())
    {
        $this->assertIsFactoryDefinition($definition);

        $callable = $definition->getCallable();

        return $callable($this->container);
    }

    /**
     * {@inheritdoc}
     */
    public function isResolvable(Definition $definition, array $parameters = array())
    {
        $this->assertIsFactoryDefinition($definition);

        return true;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    private function assertIsFactoryDefinition(Definition $definition)
    {
        if (!$definition instanceof FactoryDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition resolver is only compatible with FactoryDefinition objects, %s given',
                get_class($definition)
            ));
        }
    }
}
