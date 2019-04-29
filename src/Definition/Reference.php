<?php

declare(strict_types=1);

namespace DI\Definition;

use DI\Definition\Exception\InvalidDefinition;
use DI\ServiceLocator;
use Psr\Container\ContainerInterface;

/**
 * Represents a reference to another entry.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Reference implements Definition, SelfResolvingDefinition
{
    public static $serviceLocatorClass = ServiceLocator::class;

    /**
     * Entry name.
     * @var string
     */
    private $name = '';

    /**
     * Name of the target entry.
     * @var string
     */
    private $targetEntryName;

    /**
     * @var string
     */
    private $requestingName;

    /**
     * @var bool
     */
    private $isServiceLocatorEntry;

    /**
     * @var ServiceLocatorDefinition
     */
    private $serviceLocatorDefinition;

    /**
     * @param string $targetEntryName Name of the target entry
     * @param string $requestingName name of an entry - holder of a definition requesting this entry
     */
    public function __construct(string $targetEntryName, $requestingName = null)
    {
        $this->targetEntryName = $targetEntryName;
        $this->requestingName = $requestingName;
        $this->isServiceLocatorEntry = $targetEntryName === self::$serviceLocatorClass;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getTargetEntryName() : string
    {
        return $this->targetEntryName;
    }

    // added

    /**
     * Returns the name of the entity requesting this entry.
     * @return string
     */
    public function getRequestingName() : string
    {
        return $this->requestingName;
    }

    public function isServiceLocatorEntry() : bool
    {
        return $this->isServiceLocatorEntry;
    }

    public function getServiceLocatorDefinition() : ServiceLocatorDefinition
    {
        if (!$this->isServiceLocatorEntry) {
            throw new InvalidDefinition('Invalid service locator definition');
        }
        if (!$this->serviceLocatorDefinition) {
            $this->serviceLocatorDefinition = new ServiceLocatorDefinition($this->getTargetEntryName(), $this->requestingName);
        }

        return $this->serviceLocatorDefinition;
    }

    public function resolve(ContainerInterface $container)
    {
        if ($this->isServiceLocatorEntry) {
            return $this->getServiceLocatorDefinition()->resolve($container);
        }

        return $container->get($this->getTargetEntryName());
    }

    public function isResolvable(ContainerInterface $container) : bool
    {
        if ($this->isServiceLocatorEntry) {
            return $this->getServiceLocatorDefinition()->isResolvable($container);
        }

        return $container->has($this->getTargetEntryName());
    }

    public function replaceNestedDefinitions(callable $replacer)
    {
        // no nested definitions
    }

    public function __toString()
    {
        return sprintf(
            'get(%s)',
            $this->targetEntryName
        );
    }
}
