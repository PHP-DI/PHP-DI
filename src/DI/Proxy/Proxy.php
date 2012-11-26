<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Proxy;

/**
 * Proxy class
 *
 * @author mnapoli
 */
class Proxy
{

	/**
	 * Callback that will return the instance
	 * @var callable
	 */
	private $beanInstanceLoader;

	/**
	 * Instance of the bean that is proxied
	 *
	 * Lazy-loaded, i.e. the instance will be loaded only when the proxy is called
	 */
	private $beanInstance;


	/**
	 * Define the callback that will return the instance
	 * @param callable $instanceLoader The function to call to retrieve an instance
	 */
	public function __construct($instanceLoader) {
		$this->beanInstanceLoader = $instanceLoader;
	}

	/**
	 * Load the instance of the bean (used for lazy-loading)
	 */
	private function __loadInstance() {
		if ($this->beanInstance == null) {
			$instanceLoader = $this->beanInstanceLoader;
			$this->beanInstance = $instanceLoader();
		}
	}


	/*********  Proxy methods  *********/

	/**
	 * Magic method to catch calls to methods of the bean
	 * @param string $name Name of the method called
	 * @param array  $arguments Parameters passed to the method
	 * @return mixed
	 */
	public function __call($name, array $arguments) {
		if ($this->beanInstance == null) {
			$this->__loadInstance();
		}
		return call_user_func_array(array($this->beanInstance, $name), $arguments);
	}

	/**
	 * Magic method to catch calls to static methods of the bean
	 * @param string $name Name of the method called
	 * @param array  $arguments Parameters passed to the method
	 * @throws ProxyException
	 * @return mixed
	 */
	public static function __callStatic($name, array $arguments) {
		// This should never happen because static method calls should be on the class name,
		// not the object
		throw new ProxyException("Unexpected static call on Proxy. Did you do a static call on an object?");
	}

	/**
	 * Magic method to catch write-access to properties
	 * @param string $name Name of the property
	 * @param mixed $value Value to set to the property
	 * @return void
	 */
	public function __set($name, $value) {
		if ($this->beanInstance == null) {
			$this->__loadInstance();
		}
		$this->beanInstance->$name = $value;
	}

	/**
	 * Magic method to catch read-access to properties
	 * @param string $name Name of the property
	 * @return mixed
	 */
	public function __get($name) {
		if ($this->beanInstance == null) {
			$this->__loadInstance();
		}
		return $this->beanInstance->$name;
	}

	/**
	 * Magic method to catch isset calls on properties
	 * @param string $name Name of the property
	 * @return mixed
	 */
	public function __isset($name) {
		if ($this->beanInstance == null) {
			$this->__loadInstance();
		}
		return isset($this->beanInstance->$name);
	}

	/**
	 * Magic method to catch unset calls on properties
	 * @param string $name Name of the property
	 * @return void
	 */
	public function __unset($name) {
		if ($this->beanInstance == null) {
			$this->__loadInstance();
		}
		unset($this->beanInstance->$name);
	}

	/**
	 * The __invoke() method is called when a script tries to call an object as a function
	 * @return mixed
	 * @see http://www.php.net/manual/en/language.oop5.magic.php#object.invoke
	 */
	public function __invoke() {
		if ($this->beanInstance == null) {
			$this->__loadInstance();
		}
		$beanInstance = $this->beanInstance;
		return $beanInstance();
	}

	/**
	 * This static method is called for classes exported by var_export()
	 * @param array $array Array containing exported properties in the form array('property' => value, ...)
	 * @throws ProxyException
	 * @return mixed Returns an instance of this class
	 * @see http://www.php.net/manual/en/language.oop5.magic.php#object.set-state
	 */
	public static function __set_state(array $array) {
		throw new ProxyException("Proxy classes can't be exported");
	}

	/**
	 * Method called when the object is destroyed
	 */
	function __destruct() {
		// Nothing to do
	}

	/**
	 * Method called when the object is cloned
	 */
	public function __clone() {
		if ($this->beanInstance == null) {
			$this->__loadInstance();
		}
		$newInstance = clone $this->beanInstance;
		$this->beanInstance = $newInstance;
	}

	/**
	 * Convert the object to string
	 */
	public function __toString() {
		if ($this->beanInstance == null) {
			$this->__loadInstance();
		}
		return (string) $this->beanInstance;
	}

	/**
	 * Method called when the object is serialized
	 */
	public function __sleep() {
		throw new ProxyException("Proxy classes can't be serialized");
	}

	/**
	 * Method called when the object is unserialized
	 */
	public function __wakeup() {
		throw new ProxyException("Proxy classes can't be serialized");
	}

}
