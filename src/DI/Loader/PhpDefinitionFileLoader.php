<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Loader;

/**
 * PhpDefinitionFileLoader loads PHP files definitions.
 *
 * @author Domenic Muskulus <domenic@muskulus.eu>
 */
class PhpDefinitionFileLoader extends DefinitionFileLoader
{
    /**
     * Loads definitions from a PHP file
     *
     * @return array The definition array
     * @throws Exception\ParseException
     */
    public function load()
    {
        $definitions = include $this->definitionFile;
        if (!is_array($definitions)) {
            throw new Exception\ParseException("The definition file '$this->definitionFile' doesn't return a PHP array");
        }
        return $definitions;
    }
}