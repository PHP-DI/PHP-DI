<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Zend\Controller;

use DI\Container;

/**
 * Zend Controller base class automatically injecting dependencies
 */
abstract class Action extends \Zend_Controller_Action
{

	/**
	 * Class constructor
	 *
	 * Overriding the constructor to inject dependencies.
	 *
	 * Do not override this method, use {@link init()} instead.
	 *
	 * @param \Zend_Controller_Request_Abstract  $request
	 * @param \Zend_Controller_Response_Abstract $response
	 * @param array                             $invokeArgs Any additional invocation arguments
	 */
	public function __construct(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response,
								array $invokeArgs = array()
	) {
		// Dependency injection
		Container::getInstance()->injectAll($this);
		parent::__construct($request, $response, $invokeArgs);
	}

}
