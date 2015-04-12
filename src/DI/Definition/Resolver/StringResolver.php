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
use DI\Definition\StringDefinition;
use DI\DependencyException;
use DI\NotFoundException;
use Interop\Container\ContainerInterface;

/**
 * Resolves a string expression.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class StringResolver implements DefinitionResolver
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
     * Resolve a value definition to a value.
     *
     * A value definition is simple, so this will just return the value of the ValueDefinition.
     *
     * @param StringDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = array())
    {
        $this->assertIsStringDefinition($definition);

        $expression = $definition->getExpression();

        // TODO Remove PHP 5.3 support
        $container = $this->container;

        $result = preg_replace_callback('#\{([^\{\}]+)\}#', function (array $matches) use ($container, $definition) {
            try {
                return $container->get($matches[1]);
            } catch (NotFoundException $e) {
                throw new DependencyException(sprintf(
                    "Error while parsing string expression for entry '%s': %s",
                    $definition->getName(),
                    $e->getMessage()
                ), 0, $e);
            }
        }, $expression);

        if ($result === null) {
            throw new \RuntimeException(sprintf('An unknown error occurred while parsing the string definition: \'%s\'', $expression));
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isResolvable(Definition $definition, array $parameters = array())
    {
        $this->assertIsStringDefinition($definition);

        return true;
    }

    private function assertIsStringDefinition(Definition $definition)
    {
        if (!$definition instanceof StringDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition resolver is only compatible with StringDefinition objects, %s given',
                get_class($definition)
            ));
        }
    }
}
