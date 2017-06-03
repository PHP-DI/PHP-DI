<?php

namespace DI\Definition;

use DI\DependencyException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Definition of a string composed of other strings.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class StringDefinition implements Definition, SelfResolvingDefinition
{
    /**
     * Entry name.
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $expression;

    /**
     * @param string $name Entry name
     */
    public function __construct(string $name, string $expression)
    {
        $this->name = $name;
        $this->expression = $expression;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getExpression() : string
    {
        return $this->expression;
    }

    public function resolve(ContainerInterface $container) : string
    {
        $expression = $this->expression;

        $result = preg_replace_callback('#\{([^\{\}]+)\}#', function (array $matches) use ($container) {
            try {
                return $container->get($matches[1]);
            } catch (NotFoundExceptionInterface $e) {
                throw new DependencyException(sprintf(
                    "Error while parsing string expression for entry '%s': %s",
                    $this->getName(),
                    $e->getMessage()
                ), 0, $e);
            }
        }, $expression);

        if ($result === null) {
            throw new \RuntimeException(sprintf('An unknown error occurred while parsing the string definition: \'%s\'', $expression));
        }

        return $result;
    }

    public function isResolvable(ContainerInterface $container) : bool
    {
        return true;
    }

    public function __toString()
    {
        return $this->expression;
    }
}
