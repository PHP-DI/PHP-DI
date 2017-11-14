<?php
declare(strict_types=1);

namespace DI\Discovery;

use Composer\Autoload\ClassLoader;

/**
 * Represents a list of class names.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class KnownClasses
{
    /**
     * @var callable
     */
    private $classListProvider;

    /**
     * List of class names cached to avoid computing it twice. Null if not initialized yet.
     *
     * @var array|null
     */
    private $classList;

    /**
     * You are not encouraged to use the constructor.
     *
     * You should instead use the static methods of this class if they do what you need.
     *
     * @param callable $classListProvider Must return the list of classes.
     */
    public function __construct(callable $classListProvider)
    {
        $this->classListProvider = $classListProvider;
    }

    /**
     * Get the class list from Composer's autoloader's class map.
     *
     * Usage example in a classic PHP application:
     *
     *     $autoloader = require_once __DIR__ . '/vendor/autoload.php';
     *     [...]
     *     $classList = KnownClasses::fromComposerAutoloader($autoloader);
     */
    public static function fromComposerClassmap(ClassLoader $autoloader) : KnownClasses
    {
        return new static(function () use ($autoloader) : array {
            return array_keys($autoloader->getClassMap());
        });
    }

    /**
     * @param string[] $classList The array must be a list of class names (strings).
     */
    public static function fromArray(array $classList) : KnownClasses
    {
        return new static(function () use ($classList) : array {
            return $classList;
        });
    }

    /**
     * Return the list of class names as an array of strings.
     *
     * @return string[]
     */
    public function getList() : array
    {
        if ($this->classList === null) {
            $this->classList = (array) ($this->classListProvider)();
        }

        return $this->classList;
    }
}
