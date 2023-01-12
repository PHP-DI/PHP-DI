---
layout: blogpost
title: PHP-DI 7.0 released
author: Matthieu Napoli
date: January 12th 2023
---

I am very happy to announce that PHP-DI 7.0 has been released!

**If you have never heard of PHP-DI visit [the home page](../) first** to get an overview of what PHP-DI can do for you.

I'll make this one short and to the point. PHP-DI 7 is a maturation of v6 to support modern PHP versions and their new features:

- PHP 8.0 and greater is supported
- `@Inject` phpdoc annotations have been replaced by the native PHP attribute `#[Inject]`
- PSR-11 2.0 compatibility
- The codebase and the API exposed is now much more typed (thanks to the new PHP features)

If you are migrating from a 6.x version check out **the detailed [migration guide](../doc/migration/7.0.md)**.

The documentation shown on [php-di.org](http://php-di.org) is now for the 7.0 version, the 6.x documentation can be found [here](https://github.com/PHP-DI/PHP-DI/tree/6.4/doc).

If something isn't right in the package or the documentation, please [open an issue](https://github.com/PHP-DI/PHP-DI/issues/new) or a pull request.
