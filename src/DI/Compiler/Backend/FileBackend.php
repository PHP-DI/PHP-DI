<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Compiler\Backend;

use Closure;
use DI\NotFoundException;
use Interop\Container\ContainerInterface;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

/**
 * Stores compiled definitions to PHP files.
 */
class FileBackend implements Backend, ContainerInterface
{
    /**
     * Directory in which to write and read the files.
     * @var string
     */
    private $path;

    /**
     * @var LazyLoadingValueHolderFactory
     */
    private $proxyFactory;

    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * @param string                        $path         Directory in which to write and read the files.
     *                                                    The process must have read and write access.
     * @param LazyLoadingValueHolderFactory $proxyFactory Used to create proxies for lazy injections.
     */
    public function __construct($path, LazyLoadingValueHolderFactory $proxyFactory)
    {
        if (! is_writable($path)) {
            throw new \InvalidArgumentException(sprintf(
                'The path %s is not writable, impossible to use it to store the compiled container',
                $path
            ));
        }

        $this->path = $path;
        $this->proxyFactory = $proxyFactory;
    }

    /**
     * Stores an entry to a PHP file, named after the entry name.
     *
     * {@inheritdoc}
     */
    public function writeCompiledEntry($entryName, $code)
    {
        $fileName = $this->getFileName($entryName);

        // Add open tag and empty line at the end
        $code = '<?php' . PHP_EOL . $code . PHP_EOL;

        // Write to the file using a lock for the write operation
        file_put_contents($fileName, $code, LOCK_EX);
    }

    /**
     * {@inheritdoc}
     */
    public function hasCompiledEntry($entryName)
    {
        $fileName = $this->getFileName($entryName);

        return file_exists($fileName);
    }

    /**
     * Reads an entry from a PHP file named after the entry name.
     *
     * Just include the file.
     *
     * {@inheritdoc}
     */
    public function readCompiledEntry($entryName, ContainerInterface $container)
    {
        $fileName = $this->getFileName($entryName);

        if (! file_exists($fileName)) {
            throw new NotFoundException("No entry or class found for '$entryName'");
        }

        // Set the container so that the included file can use $this->get and $this->has
        $this->container = $container;

        // Include the PHP file, which should return a value
        $entry = include $fileName;

        return $entry;
    }

    /**
     * @param string $entryName
     * @return string File name for the entry.
     */
    private function getFileName($entryName)
    {
        // Only allow specific chars, the rest is normalized to _
        $normalizedName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $entryName);

        return $this->path . DIRECTORY_SEPARATOR . $normalizedName . '.php';
    }

    /**
     * When including files, they are in the context of a container.
     *
     * That means they can do $this->get($name), so we have to implement the ContainerInterface.
     *
     * Do not use directly on this class, it doesn't make sense.
     *
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->container->get($name);
    }

    /**
     * When including files, they are in the context of a container.
     *
     * That means they can do $this->has($name), so we have to implement the ContainerInterface.
     *
     * Do not use directly on this class, it doesn't make sense.
     *
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->container->has($name);
    }

    /**
     * Creates and returns a proxy instance.
     *
     * @param string  $className
     * @param Closure $initializer
     *
     * @return object Proxy instance
     */
    private function createProxy($className, Closure $initializer)
    {
        return $this->proxyFactory->createProxy($className, $initializer);
    }
}
