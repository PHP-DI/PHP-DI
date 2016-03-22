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
use DI\DependencyException;
use DI\NotFoundException;
use Interop\Container\ContainerInterface;

/**
 * Resolves a string expression using the "dot" notation.
 *
 * @since 5.1
 * @author Michael Seidel <mvs@albami.de>
 */
class DotNotationResolver implements DefinitionResolver
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
     * Resolve a dotNotation definition to a value.
     *
     * @param DotNotationDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = [])
    {
        $expression = $definition->getExpression();
        $segments   = explode('.', $expression);

        try {
            $result = $this->container->get(array_shift($segments));
        } catch (NotFoundException $e) {
            throw new DependencyException(sprintf(
                "Error while parsing dotNotation expression for entry '%s': %s",
                $definition->getName(),
                $e->getMessage()
            ), 0, $e);
        }

        foreach ($segments as $segment) {
            if (!is_array($result) || !array_key_exists($segment, $result)) {
                throw new \RuntimeException(sprintf('An unknown error occurred while parsing the dotNotation definition: \'%s\'', $expression));
            }

            $result = $result[$segment];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isResolvable(Definition $definition, array $parameters = [])
    {
        return true;
    }
}
