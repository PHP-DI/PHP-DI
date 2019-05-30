<?php

declare(strict_types=1);

namespace DI;

use Psr\Container\ContainerInterface;

class ServiceLocatorRepository implements ContainerInterface
{
    /**
     * @var ServiceLocator[]
     */
    private $locators = [];

    /**
     * Overrides for ServiceLocators.
     * @var array
     */
    private $overrides = [];

    /**
     * @var ContainerInterface
     */
    private $container;

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
     * @return ServiceLocator
     */
    public function create(string $entry, array $services = []) : ServiceLocator
    {
        if (isset($this->overrides[$entry])) {
            $services = array_merge($services, $this->overrides[$entry]);
        }
        if (!isset($this->locators[$entry])) {
            $this->locators[$entry] = new ServiceLocator($this->container, $services, $entry);
        } else {
            // the service locator cannot be re-created - the existing locator may be returned only if expected services are identical
            // compare passed services and those in the already created ServiceLocator
            $locatorServices = $this->locators[$entry]->getServices();
            foreach ($services as $key => $value) {
                if (is_numeric($key)) {
                    $key = $value;
                }
                if (!array_key_exists($key, $locatorServices) || $locatorServices[$key] !== $value) {
                    throw new \LogicException(sprintf(
                        "ServiceLocator for '%s' cannot be recreated with different services.",
                        $entry
                    ));
                }
            }
        }

        return $this->locators[$entry];
    }

    /**
     * Override a single service for a service locator.
     * This can be only used before the service locator for the given entry is created.
     *
     * @param string $entry
     * @param string $serviceId
     * @param string|null $serviceEntry
     * @return $this
     */
    public function override(string $entry, string $serviceId, string $serviceEntry = null)
    {
        if (isset($this->locators[$entry])) {
            throw new \LogicException(sprintf(
                "Service '%s' for '%s' cannot be overridden - ServiceLocator is already created.",
                $serviceId,
                $entry
            ));
        }

        $serviceEntry = $serviceEntry ?? $serviceId;
        $this->overrides[$entry][$serviceId] = $serviceEntry;

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
