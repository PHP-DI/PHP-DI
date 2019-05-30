<?php

declare(strict_types=1);

namespace DI\Definition;

use DI\ServiceLocator;
use DI\ServiceLocatorRepository;
use DI\ServiceSubscriberException;
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
     * @return ServiceLocator
     * @throws ServiceSubscriberException
     */
    public function resolve(ContainerInterface $container)
    {
        if (!method_exists($this->requestingName, 'getSubscribedServices')) {
            throw new ServiceSubscriberException(sprintf('The class %s does not implement ServiceSubscriberInterface.', $this->requestingName));
        }

        /** @var ServiceLocatorRepository $repository */
        $repository = $container->get(self::$serviceLocatorRepositoryClass);
        $services = $this->requestingName::getSubscribedServices();

        return $repository->create($this->requestingName, $services);
    }

    /**
     * Check if a definition can be resolved.
     * @param ContainerInterface $container
     * @return bool
     */
    public function isResolvable(ContainerInterface $container) : bool
    {
        return method_exists($this->requestingName, 'getSubscribedServices');
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
            'get(%s) for \'%s\'',
            $this->name,
            $this->requestingName
        );
    }
}
