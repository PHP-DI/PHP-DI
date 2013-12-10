<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\DefinitionResolver;

use DI\Definition\CallableDefinition;
use DI\Definition\Definition;
use Interop\DI\ReadableContainerInterface;

/**
 * Resolves a callable definition to a value.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class CallableDefinitionResolver implements DefinitionResolver
{
    /**
     * @var ReadableContainerInterface
     */
    private $container;

    /**
     * The resolver needs a container. This container will be passed to the callable as a parameter
     * so that the callable can access other entries of the container.
     *
     * @param ReadableContainerInterface $container
     */
    public function __construct(ReadableContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve a callable definition to a value.
     *
     * This will call the callable of the definition.
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition)
    {
        if (! $definition instanceof CallableDefinition) {
            throw new \InvalidArgumentException(
                sprintf(
                    'This definition resolver is only compatible with CallableDefinition objects, %s given',
                    get_class($definition)
                )
            );
        }

        $callable = $definition->getCallable();

        return $callable($this->container);
    }

    /**
     * @return ReadableContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
