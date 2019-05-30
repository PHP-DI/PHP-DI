<?php

declare(strict_types=1);

namespace DI;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ServiceLocator.
 *
 * Serving "lazy" dependencies for classes using ServiceSubscriberInterface.
 * Suggested as a lightweight alternative for heavyweight proxies from ocramius/proxy-manager
 */
class ServiceLocator implements ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $services = [];

    /**
     * Name of a class to which this service locator instance belongs to.
     * @var string|null
     */
    private $subscriber;

    /**
     * Constructor.
     * @param ContainerInterface $container
     * @param array $services
     * @param string|null $subscriber className of a ServiceSubscriber to which this service locator instance belongs to
     */
    public function __construct(ContainerInterface $container, array $services, string $subscriber = null)
    {
        $this->container = $container;
        $this->subscriber = $subscriber;
        $this->setServices($services);
    }

    /**
     * @param array $services
     */
    protected function setServices(array $services)
    {
        foreach ($services as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
            }
            $this->services[$key] = $value;
        }
    }

    /**
     * Get defined services.
     * @return array
     */
    public function getServices() : array
    {
        return $this->services;
    }

    /**
     * Get name of a class to which this service locator instance belongs to.
     * @return string
     */
    public function getSubscriber() : string
    {
        return $this->subscriber;
    }

    /**
     * Finds a service by its identifier.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if (!isset($this->services[$id])) {
            throw new NotFoundException("Service '$id' is not defined.");
        }

        return $this->container->get($this->services[$id]);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        if (!isset($this->services[$id])) {
            return false;
        }

        return $this->container->has($this->services[$id]);
    }
}
