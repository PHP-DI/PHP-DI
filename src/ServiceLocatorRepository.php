<?php

declare(strict_types=1);

namespace DI;

use Psr\Container\ContainerInterface;

class ServiceLocatorRepository implements ContainerInterface
{
    /**
     * @var ServiceLocator[]
     */
    protected $locators = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Create or modify service locator.
     *
     * @param string $entry
     * @param array $services
     * @param bool $overwrite if service locator for an entry already exists, should its services be overwritten?
     * @return ServiceLocator
     */
    public function create(string $entry, array $services = [], $overwrite = false) : ServiceLocator
    {
        if (isset($this->locators[$entry]) && !$overwrite) {
            $services = $overwrite
                ? array_merge($this->locators[$entry]->getServices(), $services)
                : array_merge($services, $this->locators[$entry]->getServices());
        }

        $this->locators[$entry] = new ServiceLocator($this->container, $services, $entry);

        return $this->locators[$entry];
    }

    /**
     * Inject service locator on an ServiceSubscriber instance.
     * @param ServiceSubscriberInterface $instance
     * @param null $entry
     * @return $this
     */
    public function injectOn(ServiceSubscriberInterface $instance, $entry = null)
    {
        $entry = $entry ?? get_class($instance);
        $serviceLocator = $this->create($entry, $instance->getSubscribedServices());
        $instance->setServiceLocator($serviceLocator);

        return $this;
    }

    /**
     * Modify a single entry for a service locator.
     *
     * @param string $entry
     * @param string $serviceId
     * @param string|null $serviceEntry
     * @return $this
     */
    public function setService(string $entry, string $serviceId, string $serviceEntry = null)
    {
        $serviceEntry = $serviceEntry ?? $serviceId;
        $this->create($entry, [$serviceId => $serviceEntry], true);

        return $this;
    }

    /**
     * Get a service locator for an entry.
     * @param string $entry
     * @return ServiceLocator
     * @throws NotFoundException
     */
    public function get($entry) : ServiceLocator
    {
        if (!isset($this->locators[$entry])) {
            throw new NotFoundException("Service locator for entry '$entry' is not initialized.");
        }

        return $this->locators[$entry];
    }

    /**
     * @param string $entry
     * @return bool
     */
    public function has($entry)
    {
        return isset($this->locators[$entry]);
    }
}
