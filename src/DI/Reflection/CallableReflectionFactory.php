<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Reflection;

/**
 * Create a reflection object from a callable.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class CallableReflectionFactory
{
    /**
     * @param callable $callable
     *
     * @return \ReflectionFunctionAbstract
     *
     * TODO Use the `callable` type-hint once support for PHP 5.4 and up.
     */
    public static function fromCallable($callable)
    {
        // Array callable
        if (is_array($callable)) {
            list($class, $method) = $callable;

            return new \ReflectionMethod($class, $method);
        }

        // Closure
        if ($callable instanceof \Closure) {
            return new \ReflectionFunction($callable);
        }

        // Callable object (i.e. implementing __invoke())
        if (is_object($callable) && method_exists($callable, '__invoke')) {
            return new \ReflectionMethod($callable, '__invoke');
        }

        // Callable class (i.e. implementing __invoke())
        if (is_string($callable) && class_exists($callable) && method_exists($callable, '__invoke')) {
            return new \ReflectionMethod($callable, '__invoke');
        }

        // Standard function
        return new \ReflectionFunction($callable);
    }
}
