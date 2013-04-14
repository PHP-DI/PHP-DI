<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\FileLoader;

/**
 * Loads definition from an array in a PHP file
 *
 * @author Domenic Muskulus <domenic@muskulus.eu>
 */
class ArrayDefinitionFileLoader extends DefinitionFileLoader
{

    /**
     * {@inheritdoc}
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
