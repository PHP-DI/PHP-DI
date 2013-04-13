<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Loader;

use DI\Loader\Exception\ParseException;
use DI\Scope;

/**
 * JsonDefinitionFileLoader loads JSON files definitions.
 *
 * @author Domenic Muskulus <domenic@muskulus.eu>
 */
class JsonDefinitionFileLoader extends DefinitionFileLoader
{
    /**
     * Loads definitions from a JSON file
     *
     * @throws Exception\ParseException
     * @return array The definition array
     */
    public function load()
    {
        if (($definitions = json_decode(file_get_contents($this->definitionFile), true)) === null) {
            throw new ParseException("The file '$this->definitionFile' contains invalid JSON.");
        }
        return $this->parseScope($definitions);
    }

    /**
     * @param array $definitions
     * @throws Exception\ParseException
     * @return array
     */
    private function parseScope($definitions)
    {
        foreach ($definitions as &$definition) {
            if (!empty($definition['scope'])) {
                try {
                    $definition['scope'] = new Scope($definition['scope']);
                } catch (\UnexpectedValueException $e) {
                    throw new ParseException(sprintf(
                        'The scope value "%s" is not in the set of valid scopes [%s]. (in %s)',
                        $definition['scope'],
                        implode(', ', Scope::toArray()),
                        basename($this->definitionFile)
                    ));
                }
            }
        }
        return $definitions;
    }
}