<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Compiler;

use DI\NotFoundException;
use Interop\Container\ContainerInterface;

/**
 * Container where definitions are compiled to PHP code for optimal performances.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class CompiledContainer implements ContainerInterface
{
    /**
     * Contains the container entries.
     *
     * @var array
     */
    private $entries = [];

    /**
     * Container that wraps this container. If none, points to $this.
     *
     * @var ContainerInterface
     */
    private $wrapperContainer;

    public function __construct($file, ContainerInterface $wrapperContainer = null)
    {
        if (! file_exists($file)) {
            throw new \Exception("The file '$file' doesn't exist, the container cannot be created");
        }

        $this->entries = include $file;

        if (! is_array($this->entries)) {
            throw new \Exception("The file '$file' doesn't return a PHP array: is it corrupted?");
        }

        $this->wrapperContainer = $wrapperContainer ?: $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (! isset($this->entries[$name])) {
            throw new NotFoundException("No entry or class found for '$name'");
        }

        $factory = $this->entries[$name];

        return $factory($this->wrapperContainer);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return isset($this->entries[$name]);
    }
}
