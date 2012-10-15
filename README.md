# PHP-DI - PHP dependency injection with annotations

[![Build Status](https://secure.travis-ci.org/mnapoli/PHP-DI.png)](http://travis-ci.org/mnapoli/PHP-DI)

* *Project home* [http://mnapoli.github.com/PHP-DI/](http://mnapoli.github.com/PHP-DI/)
``
### Introduction

The aim of this library is to make [Dependency Injection]
(http://en.wikipedia.org/wiki/Dependency_injection)
as simple as possible with PHP.

Unlike Zend\DI, Symfony Service Container or Pimple, PHP-DI:

* can be used by a monkey
* is not limited to Services (_anything_ can be injected)
* uses annotations for code-readability and ease of use

Read more here on the [project home](http://mnapoli.github.com/PHP-DI/).


#### Features

* Uses annotations for simplicity, readability and auto-completion in your IDE
* `@Inject` annotation to inject a dependency
* Optional lazy-loading of dependencies (`@Inject(lazy=true)`)
* `@Value` annotation to inject a configuration value
* Class aliases (interface-implementation mapping)
* Easy installation with [Composer](http://getcomposer.org/doc/00-intro.md)


### Installation

Read the [Getting started](https://github.com/mnapoli/PHP-DI/wiki/Getting-started) guide.


### Projects using PHP-DI

Public projects using PHP-DI:
* [phpBeanstalkdAdmin](http://mnapoli.github.com/phpBeanstalkdAdmin/)


### Contribute

* Read the wiki: [Contribute](https://github.com/mnapoli/PHP-DI/wiki/Contribute)
