<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Compiler\Backend;

use DI\ContainerInterface;

/**
 * Backend where a compiler can store compiled definitions.
 */
interface Backend
{
    /**
     * Stores a compiled entry (PHP code).
     *
     * @param string $entryName Name of the entry being stored.
     * @param string $code      PHP code able to return an entry.
     */
    public function writeCompiledEntry($entryName, $code);

    /**
     * Tests if the backend has an entry. Use this before reading it.
     *
     * @param string $entryName Name of the entry to retrieve.
     *
     * @return bool True if the backend can return the entry, false otherwise.
     */
    public function hasCompiledEntry($entryName);

    /**
     * Reads a compiled entry and return it.
     *
     * @param string             $entryName Name of the entry to retrieve.
     * @param ContainerInterface $container Container used to retrieve dependencies of the entry.
     *
     * @return mixed Entry (value, object, ...)
     */
    public function readCompiledEntry($entryName, ContainerInterface $container);
}
