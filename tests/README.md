# Pods Framework Unit Tests [![Build Status](https://secure.travis-ci.org/pods-framework/pods.png?branch=release/3.0)](http://travis-ci.org/pods-framework/pods) [![Coverage Status](https://coveralls.io/repos/pods-framework/pods/badge.png)](https://coveralls.io/r/pods-framework/pods) #


This folder contains all the tests for Pods Framework.

For more information on how to write PHPUnit Tests, see [PHPUnit's Website](https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html).

## Running locally via VVV ##

1. `cd /path/to/vvv/folder`
1. `vagrant ssh`
1. `cd /srv/www/path/to/pods/folder`
1. `composer install`
1. `./bin/vagrant-wp-tests.sh; ./vendor/bin/phpunit` (run this each time you want to test)

## Running locally (other servers) ##

1. `cd /path/to/pods/folder`
1. `composer install`
1. `./bin/install-wp-tests.sh wordpress_test root '' localhost latest; ./vendor/bin/phpunit` (run this each time you want to test)
