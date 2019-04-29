<?php

declare(strict_types=1);

namespace DI\Definition;

use DI\ServiceLocatorRepository;
use Psr\Container\ContainerInterface;

class ServiceLocatorDefinition implements Definition, SelfResolvingDefinition
{
    public static $serviceLocatorRepositoryClass = ServiceLocatorRepository::class;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $requestingName;

    /**
     * @param string $name           Entry name
     * @param string $requestingName name of an entry - holder of a definition requesting service locator
     */
    public function __construct($name, $requestingName)
    {
        $this->name = $name;
        $this->requestingName = $requestingName;
    }

    /**
     * Returns the name of the entry in the container.
     */
    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Returns the name of the holder of the definition requesting service locator.
     * @return string
     */
    public function getRequestingName() : string
    {
        return $this->requestingName;
    }

    /**
     * Resolve the definition and return the resulting value.
     *
     * @param ContainerInterface $container
     * @return mixed
     */
    public function resolve(ContainerInterface $container)
    {
        /** @var ServiceLocatorRepository $repository */
        $repository = $container->get(self::$serviceLocatorRepositoryClass);
        $services = $this->requestingName::getSubscribedServices();
        $serviceLocator = $repository->create($this->requestingName, $services);

        return $serviceLocator;
    }

    /**
     * Check if a definition can be resolved.
     * @param ContainerInterface $container
     * @return bool
     */
    public function isResolvable(ContainerInterface $container) : bool
    {
        return true;
    }

    public function replaceNestedDefinitions(callable $replacer)
    {
        // no nested definitions
    }

    /**
     * Definitions can be cast to string for debugging information.
     */
    public function __toString()
    {
        return sprintf(
            'get(%s)',
            $this->name
        );
    }
}
