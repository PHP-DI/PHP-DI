<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Loader;

use DI\Loader\Exception\FileNotFoundException;
use DI\Loader\Exception\ParseException;

/**
 * DefinitionFileLoader is the abstract class used by all built-in loaders that are file based.
 *
 * @author Domenic Muskulus <domenic@muskulus.eu>
 */
abstract class DefinitionFileLoader
{
    /**
     * @var string
     */
    protected $definitionFile;

    /**
     * @var bool
     */
    protected $validateFile;

    /**
     * @param string $pathAndFilename
     * @param bool $validateFile
     * @throws Exception\ParseException
     * @throws Exception\FileNotFoundException
     */
    public function __construct($pathAndFilename, $validateFile = true)
    {
        if (!file_exists($pathAndFilename)) {
            throw new FileNotFoundException("The definition file '$pathAndFilename' has not been found.");
        } elseif (!is_readable($pathAndFilename)) {
           throw new ParseException("The definition file '$pathAndFilename' is not readable.");
        }
        $this->definitionFile = $pathAndFilename;
        $this->validateFile = $validateFile;
    }

    /**
     * Loads the definitions from a definition file
     *
     * @return array
     */
    abstract public function load();
}
