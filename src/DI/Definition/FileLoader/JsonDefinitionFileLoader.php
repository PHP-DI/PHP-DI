<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\FileLoader;

use DI\Definition\FileLoader\Exception\ParseException;

/**
 * JsonDefinitionFileLoader loads JSON files definitions.
 *
 * @author Domenic Muskulus <domenic@muskulus.eu>
 */
class JsonDefinitionFileLoader extends DefinitionFileLoader
{

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        if (($definitions = json_decode(file_get_contents($this->definitionFile), true)) === null) {
            throw new ParseException("The file '$this->definitionFile' contains invalid JSON");
        }

        return $definitions;
    }

}
