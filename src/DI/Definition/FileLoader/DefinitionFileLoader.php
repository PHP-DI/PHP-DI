<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\FileLoader;

use DI\Definition\FileLoader\Exception\FileNotFoundException;
use DI\Definition\FileLoader\Exception\ParseException;

/**
 * DefinitionFileLoader is the abstract class used by all built-in loaders that are file based.
 *
 * @author Domenic Muskulus <domenic@muskulus.eu>
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
abstract class DefinitionFileLoader
{

    /**
     * @var string
     */
    protected $definitionFile;

    /**
     * @param string $fileName
     * @throws ParseException
     * @throws FileNotFoundException
     */
    public function __construct($fileName)
    {
        if (!file_exists($fileName)) {
            throw new FileNotFoundException("The definition file '$fileName' has not been found");
        } elseif (!is_readable($fileName)) {
            throw new ParseException("The definition file '$fileName' is not readable");
        }
        $this->definitionFile = $fileName;
    }

    /**
     * Loads the definitions from a definition file
     *
     * @param bool $validate Should the file and the definitions be validated
     * @throws ParseException
     * @return array
     */
    abstract public function load($validate = false);

}
