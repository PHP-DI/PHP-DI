<?php

namespace DI\Application;

use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\Cache;
use Puli\Discovery\Api\Binding\Binding;
use Puli\Discovery\Api\Discovery;
use Puli\Discovery\Binding\ResourceBinding;
use Puli\Repository\Api\ResourceRepository;
use Puli\Repository\Resource\FileResource;

/**
 * Application kernel.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Kernel
{
    /**
     * Name of the binding for PHP-DI configuration files in Puli.
     *
     * @see http://docs.puli.io/en/latest/discovery/introduction.html
     */
    const PULI_BINDING_NAME = 'php-di/configuration';

    /**
     * Configure and create a container using all configuration files registered under
     * the `php-di/configuration` binding type in Puli.
     *
     * @return Container
     */
    public function createContainer()
    {
        if (!defined('PULI_FACTORY_CLASS')) {
            throw new \RuntimeException('Puli is not installed');
        }

        // Create Puli objects
        $factoryClass = PULI_FACTORY_CLASS;
        $factory = new $factoryClass();
        /** @var ResourceRepository $repository */
        $repository = $factory->createRepository();
        /** @var Discovery $discovery */
        $discovery = $factory->createDiscovery($repository);

        $containerBuilder = new ContainerBuilder();

        $cache = $this->getContainerCache();
        if ($cache) {
            $containerBuilder->setDefinitionCache($cache);
        }

        // Discover and load all configuration files registered under `php-di/configuration` in Puli
        $bindings = $discovery->findBindings(self::PULI_BINDING_NAME);
        $bindings = array_filter($bindings, function (Binding $binding) {
            return $binding instanceof ResourceBinding;
        });
        /** @var ResourceBinding[] $bindings */
        foreach ($bindings as $binding) {
            foreach ($binding->getResources() as $resource) {
                if (!$resource instanceof FileResource) {
                    throw new \RuntimeException(sprintf('Cannot load "%s": only file resources are supported', $resource->getName()));
                }
                $containerBuilder->addDefinitions($resource->getFilesystemPath());
            }
        }

        // Puli objects
        $containerBuilder->addDefinitions([
            'Puli\Repository\Api\ResourceRepository' => $repository,
            'Puli\Discovery\Api\Discovery' => $discovery,
        ]);

        $this->configureContainerBuilder($containerBuilder);

        return $containerBuilder->build();
    }

    /**
     * Override this method to configure the cache to use for container definitions.
     *
     * @return Cache|null
     */
    protected function getContainerCache()
    {
        return null;
    }

    /**
     * Override this method to customize the container builder before it is used.
     */
    protected function configureContainerBuilder(ContainerBuilder $containerBuilder)
    {
    }
}
