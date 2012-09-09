<?php

namespace DI\Factory;

use \DI\Factory\FactoryInterface;

/**
 * Singleton factory for instantiating dependencies
 */
class SingletonFactory implements FactoryInterface
{

	/**
	 * Array of the singleton instances, indexed by the classname
	 * @var array
	 */
	private $singletonsMap = array();

	/**
	 * Returns an instance of the class wanted
	 * @param string $classname Name of the class
	 * @throws \InvalidArgumentException
	 * @return object Instance created
	 */
	public function getInstance($classname) {
		if (!class_exists($classname) && !interface_exists($classname)) {
			throw new \InvalidArgumentException("The class or interface $classname doesn't exist.");
		}
		if (!array_key_exists($classname, $this->singletonsMap)) {
			$this->singletonsMap[$classname] = $this->newInstance($classname);
		}
		return $this->singletonsMap[$classname];
	}

	/**
	 * Create a new instance of the class
	 * @param string $classname Class to instantiate
	 * @return object the instance
	 * @throws \InvalidArgumentException If the class is not instantiable
	 */
	private function newInstance($classname) {
		$reflectionClass = new \ReflectionClass($classname);
		if (!$reflectionClass->isInstantiable()) {
			throw new \InvalidArgumentException("The class $classname is not instantiable.");
		}
		return new $classname();
	}

}
