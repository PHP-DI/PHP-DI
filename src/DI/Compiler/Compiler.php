<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Compiler;

use DI\Definition\Compiler\DefinitionCompiler;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\Source\ExplorableDefinitionSource;

/**
 * Creates compiled definitions for the compiled container.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Compiler
{
    /**
     * @var DefinitionSource
     */
    private $definitionSource;

    /**
     * @var DefinitionCompiler
     */
    private $definitionCompiler;

    public function __construct(ExplorableDefinitionSource $definitionSource, DefinitionCompiler $definitionCompiler)
    {
        $this->definitionSource = $definitionSource;
        $this->definitionCompiler = $definitionCompiler;
    }

    public function compile($file)
    {
        $names = $this->definitionSource->getAllDefinitionNames();

        $entries = [];
        foreach ($names as $name) {
            $definition = $this->definitionSource->getDefinition($name);
            $code = $this->definitionCompiler->compile($definition);

            $entries[$name] = $code;
        }

        $this->dump($entries, $file);
    }

    private function dump(array $entries, $file)
    {
        $dumpedEntries = '';
        foreach ($entries as $name => $code) {
            $code = <<<CODE
function () {
    $code
}
CODE;
            $dumpedEntries = "\t" . var_export($name, true) . ' => ' . $code . ',' . PHP_EOL;
        }

        $content = <<<PHP
<?php

return [
$dumpedEntries
];
PHP;

        file_put_contents($file, $content);
    }
}
