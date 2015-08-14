<?php

namespace DI\Definition;

use DI\DependencyException;
use DI\Scope;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\NotFoundException;

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
     * @param string $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return Scope::SINGLETON;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    public function resolve(ContainerInterface $container)
    {
        $expression = $this->expression;

        $result = preg_replace_callback('#\{([^\{\}]+)\}#', function (array $matches) use ($container, $expression) {
            try {
                return $container->get($matches[1]);
            } catch (NotFoundException $e) {
                throw new DependencyException(sprintf(
                    "Error while parsing string expression '%s': %s",
                    $expression,
                    $e->getMessage()
                ), 0, $e);
            }
        }, $expression);

        if ($result === null) {
            throw new \RuntimeException(sprintf('An unknown error occurred while parsing the string definition: \'%s\'', $expression));
        }

        return $result;
    }

    public function isResolvable(ContainerInterface $container)
    {
        return true;
    }

    public function __toString()
    {
        return $this->expression;
    }
}
