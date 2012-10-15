## Requirements

* __PHP 5.3__ or higher

## Installation

### Install and use with Composer

The easiest way is to install PHP-DI with [Composer](http://getcomposer.org/doc/00-intro.md).

First, create a file named `composer.json` in your project root:

```json
{
    "require": {
        "mnapoli/php-di": "1.0.*",
    }
}
```

**Current stable version of PHP-DI is 1.0**

Then, run the following commands:

```bash
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
```

Then you have nothing to do, PHP-DI will be autoloaded by Composer
(if you use Composer's autoloading system, which I highly recommend).

#### Zend Framework

Are you using Zend Framework? Check out the official ZF quickstart with
Dependency Injection already configured: [zf-quickstart-di](https://github.com/mnapoli/zf-quickstart-di).

```bash
$ git clone git://github.com/mnapoli/zf-quickstart-di.git
```

## The configuration file

**The [[configuration file]] is optional**, PHP-DI will work with default behavior without it.

* Read more about the [[configuration file]]
