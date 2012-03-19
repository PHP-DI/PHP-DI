<?php

namespace DI;

/**
 * Factory for instanciating dependencies
 */
class Factory implements FactoryInterface {

    /**
     * Strategy for creating instances: singleton (one shared instance)
     */
    const STRATEGY_SINGLETON = 1;

    /**
     * Strategy for creating instances: new instance every time
     */
    const STRATEGY_NEW = 2;


    /**
     * The default strategy to use
     * @var int
     */
    private $defaultStrategy = self::STRATEGY_SINGLETON;

    /**
     * Array of the singleton instances, indexed by the classname
     * @var array
     */
    private $singletonsMap = array();


    /**
     * Returns an instance of the class wanted
     * @param string $classname Name of the class
     * @return Object instance created
     */
    public function getInstance($classname) {
        switch ($this->getDefaultStrategy()) {

            // Single instance
            case self::STRATEGY_SINGLETON:
                if (! array_key_exists($classname, $this->singletonsMap)) {
                    $this->singletonsMap[$classname] = new $classname();
                }
                return $this->singletonsMap[$classname];

            // New instance
            case self::STRATEGY_NEW:
            default:
                return new $classname();
        }
    }

    /**
     * @return int the default strategy to use
     */
    public function getDefaultStrategy() {
        return $this->defaultStrategy;
    }

    /**
     * @param int $strategy the default strategy to use
     */
    public function setDefaultStrategy($strategy) {
        $this->defaultStrategy = $strategy;
    }

}
