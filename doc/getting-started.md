# Getting started

## Requirements

* **PHP 5.3.0** or higher

## Installation

### Install with Composer

The easiest way is to install PHP-DI with [Composer](http://getcomposer.org/doc/00-intro.md).

First, create a file named `composer.json` in your project root:

```json
{
    "require": {
        "mnapoli/php-di": "2.1.*",
    }
}
```

Then, run the following commands:

```bash
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
```

PHP-DI classes will be autoloaded by Composer if you use Composer's autoloading system (which is highly recommended).

## Usage

* Read how you can [inject](doc/inject)
* Read how you can [configure](doc/configure)

## Zend Framework 1 integration

Are you using Zend Framework 1? Check out the ZF quickstart with Dependency Injection already configured:
[zf-quickstart-di](https://github.com/mnapoli/zf-quickstart-di).

**Short version**: just change the base class you use for your controllers.

```php
class IndexController extends \DI\Zend\Controller\Action
```

This base controller class will inject the dependencies automatically in your controller and in every dependency that is injected.
