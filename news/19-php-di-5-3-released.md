---
layout: blogpost
title: PHP-DI 5.3 released
author: Matthieu Napoli
date: May 22, 2016
---

I am happy to announce that PHP-DI version 5.3 has been released. The changes in this new version are:

- release of the [2.0 version](https://github.com/PHP-DI/Symfony-Bridge/releases/tag/2.0.0) of the Symfony bridge
- PHP 5.5 or above is now required
- a lot of documentation improvements by 9 different contributors
- [#389](https://github.com/PHP-DI/PHP-DI/pull/389): exception message improvement by [@mopahle](https://github.com/mopahle)
- compatibility with ProxyManager 1.x and 2.x by [@holtkamp](https://github.com/holtkamp) and [@mnapoli](https://github.com/mnapoli) (issues and pull requests: [#359](https://github.com/PHP-DI/PHP-DI/issues/359), [#411](https://github.com/PHP-DI/PHP-DI/issues/411), [#414](https://github.com/PHP-DI/PHP-DI/pull/414), [#412](https://github.com/PHP-DI/PHP-DI/pull/412))

## Symfony bridge 2.0

The Symfony bridge has been working well for 2 years but it had one main flaw: entries of the Symfony container (`services.yml` for example) couldn't reference PHP-DI entries.

With the release of [v2.0.0](https://github.com/PHP-DI/Symfony-Bridge/releases/tag/2.0.0) this is now solved: PHP-DI entries and Symfony entries can reference each others without limitations.

The documentation has been updated to reflect this: [Symfony bridge documentation](../doc/frameworks/symfony2.md).

## PHP 5.5 requirement

Following the example of Doctrine (which dropped support for PHP 5.3 in v2.5) or Laravel (which dropped support for PHP 5.4 in v5.1), PHP-DI 5.3 stops supporting PHP 5.4 in a minor version. Users using PHP 5.4 should not be affected as long as they use Composer (the only supported method for installing PHP-DI): the new version will not be installed automatically.

## Wrapping up

Let's finish on a "Thank you" to [all 12 contributors involved in this release](https://github.com/PHP-DI/PHP-DI/issues?utf8=%E2%9C%93&q=milestone%3A5.3+is%3Aclosed+).
