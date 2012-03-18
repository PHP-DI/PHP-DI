# PHP-DI - PHP dependency injection library
by Matthieu Napoli

* Project home [http://github.com/mnapoli/PHP-DI](http://github.com/mnapoli/PHP-DI)
* Documentation [http://github.com/mnapoli/PHP-DI/wiki](http://github.com/mnapoli/PHP-DI/wiki)

### Example

    class Class2 {
    }

Dependency can be automatically injected in a class using `Class2`:

    use DI\Annotations\Inject;

    class Class1 {
        /**
         * @Inject
         * @var Class2
         */
        private $class2;

        public function __construct() {
            \DI\DependencyManager::getInstance()->resolveDependencies($this);
        }
    }

### Requirements

* __PHP 5.3__ or higher