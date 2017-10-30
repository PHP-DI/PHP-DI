# Performance tests

These tests are work in progress. They are mostly useful to profile with Blackfire and detect possible improvements.

```
composer install
make test-get
make test-...
```

Improvements to do:

- run through webserver with Nginx and php-fpm (so that opcache is used)
- use a VM (e.g. homestead or a vagrant box)
- add more tests
