<?php

declare(strict_types=1);

namespace DI;

// based on https://github.com/symfony/symfony/pull/21708
/**
 * A ServiceSubscriber exposes its dependencies via the static {@link getSubscribedServices} method.
 * >>> Suggested as a lightweight alternative for heavyweight proxies from ocramius/proxy-manager.
 *
 * The getSubscribedServices method returns an array of service types required by such instances,
 * optionally keyed by the service names used internally.
 *
 * The injected service locators SHOULD NOT allow access to any other services not specified by the method.
 *
 * It is expected that ServiceSubscriber instances consume PSR-11-based service locators internally.
 * This interface does not dictate any injection method for these service locators, although constructor
 * injection is recommended.
 */
interface ServiceSubscriberInterface
{
    /**
     * Lazy instantiate heavy dependencies on-demand
     * Returns an array of service types required by such instances, optionally keyed by the service names used internally.
     *
     *  * ['logger' => Psr\Log\LoggerInterface::class] means the objects use the "logger" name
     *    internally to fetch a service which must implement Psr\Log\LoggerInterface.
     *  * ['Psr\Log\LoggerInterface'] is a shortcut for
     *  * ['Psr\Log\LoggerInterface' => 'Psr\Log\LoggerInterface']
     *
     * @return array The required service types, optionally keyed by service names
     */
    public static function getSubscribedServices() : array;
}
