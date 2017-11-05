<?php

namespace DI;

use Psr\Container\ContainerInterface;
use stdClass;

/**
 * @BeforeMethods({"initContainer"})
 */
class ContainerBench
{
    /**
     * @var ContainerInterface | FactoryInterface
     */
    private $container;

    public function initContainer()
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            stdClass::class => function (ContainerInterface $container) {
                return new stdClass();
            },
        ]);

        $extraDefinitionsToSimulateRealisticContainer = array();
        for ($i = 0; $i < 1000; $i++) {
            $extraDefinitionsToSimulateRealisticContainer[stdClass::class . $i] = function (ContainerInterface $container) {
                return new stdClass();
            };
        }
        $builder->addDefinitions($extraDefinitionsToSimulateRealisticContainer);
        $this->container = $builder->build();
    }

    /**
     * @Revs(10000)
     */
    public function benchGetDummyClass()
    {
        $value = $this->container->get(stdClass::class);
    }

    /**
     * @Revs(10000)
     */
    public function benchCheckAndGetDummyClass()
    {
        $value = $this->container->has(stdClass::class) ? $this->container->get(stdClass::class) : null;
    }

    /**
     * @Revs(10000)
     */
    public function benchMakeDummyClass()
    {
        $value = $this->container->make(stdClass::class);
    }


    /**
     * @Revs(10000)
     */
    public function benchCheckAndMakeDummyClass()
    {
        $value = $this->container->has(stdClass::class) ? $this->container->make(stdClass::class) : null;
    }
}
