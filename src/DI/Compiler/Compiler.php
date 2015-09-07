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

    public function __construct(DefinitionSource $definitionSource, DefinitionCompiler $definitionCompiler)
    {
        $this->definitionSource = $definitionSource;
        $this->definitionCompiler = $definitionCompiler;
    }

    public function compile($file)
    {
        $definitions = $this->definitionSource->getDefinitions();

        $entries = [];
        foreach ($definitions as $definition) {
            $code = $this->definitionCompiler->compile($definition);

            $entries[$definition->getName()] = $code;
        }

        $this->dump($entries, $file);
    }

    private function dump(array $entries, $file)
    {
        $dumpedEntries = '';
        foreach ($entries as $name => $code) {
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
