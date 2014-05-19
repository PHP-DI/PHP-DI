<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Invoker implements InvokerInterface
{
    public function call($callable, array $parameters = array())
    {
        $function = new \ReflectionFunction($callable);

        $callParameters = array();

        foreach ($function->getParameters() as $reflectionParameter) {
            $parameterType = $reflectionParameter->getClass();

            // In the parameter array, by name
            if (array_key_exists($reflectionParameter->getName(), $parameters)) {
                $callParameters[] = array_key_exists($reflectionParameter->getName(), $parameters);
                continue;
            }

            // Try to get the entry from the container
            if ($parameterType && $this->container->has($parameterType->getName())) {
                $callParameters[] = $this->container->get($parameterType->getName());
                continue;
            }

            // Fallback to the default value if it exists
            if ($reflectionParameter->isDefaultValueAvailable()) {
                $callParameters[] = $reflectionParameter->getDefaultValue();
                continue;
            }

            throw new \RuntimeException(sprintf(
                'Unguessable parameter "%s" for function %s()',
                $reflectionParameter->getName(),
                $function->getName()
            ));
        }

        return $callParameters;
    }
}
