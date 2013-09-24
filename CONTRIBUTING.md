# Contributing

[![Build Status](https://travis-ci.org/mnapoli/PHP-DI.png?branch=master)](https://travis-ci.org/mnapoli/PHP-DI) [![Coverage Status](https://coveralls.io/repos/mnapoli/PHP-DI/badge.png?branch=master)](https://coveralls.io/r/mnapoli/PHP-DI?branch=master)

PHP-DI is license under the MIT License.


## Set up

* Check out the sources using git or download them

```bash
$ git clone https://github.com/mnapoli/PHP-DI.git
```

* Install the libraries using composer:

```bash
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
```

If you are running Windows or are having trouble, read [the official documentation](http://getcomposer.org/doc/00-intro.md#installation).

## Run the tests

The tests are run with [PHPUnit](http://www.phpunit.de/manual/current/en/installation.html):

```bash
$ phpunit
```


## To do

- Add tests: pick up uncovered situations in the [code coverage report](https://coveralls.io/r/mnapoli/PHP-DI)
- Resolve issues: [issue list](https://github.com/mnapoli/PHP-DI/issues)
- Improve documentation
- …


## Coding style

The code follows PSR0, PSR1 and [PSR2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md).

Also, do not hesitate to add your name to the author list of a class in the docblock if you improve it.
