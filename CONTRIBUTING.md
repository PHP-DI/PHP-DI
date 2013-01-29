# Contributing

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

The tests are run with [PHPUnit](http://www.phpunit.de/manual/current/en/installation.html).

In order to run, you need to specify (in command line or by configuring your IDE) the configuration file:

```bash
$ phpunit -c phpunit.xml
```
