#!/usr/bin/env bash


echo "Removeding xdebug from PHP"
phpenv config-rm xdebug.ini

# @todo: TRAVIS_PHP_VERSION = PHPEnv
# https://github.com/travis-ci/travis-ci/issues/780 

if [[ ${TRAVIS_PHP_VERSION:0:3} == "5.3" ]]; 
	then 
		composer require --dev --no-update "phpunit/phpunit:4.8"
		echo "PHP: 5.3 / PHPUnit 4.8"
elif  [[ ${TRAVIS_PHP_VERSION:0:3} == "5.6" ]]; 
	then 
		composer require --dev --no-update "phpunit/phpunit:5.*"  
		echo "PHP: 5.6 / PHPUnit 5"
elif  [[ ${TRAVIS_PHP_VERSION:0:3} == "7.0" ]]; 
	then 
		composer require --dev --no-update "phpunit/phpunit:6.*"
		echo "PHP: 7.0 / PHPUnit 6"
elif  [[ ${TRAVIS_PHP_VERSION:0:3} == "7.1" ]]; 
	then 
		composer require --dev --no-update "phpunit/phpunit:6.*"
		echo "PHP: 7.1 / PHPUnit 6"
		
elif  [[ ${TRAVIS_PHP_VERSION:0:3} == "7.2" ]]; 
	then 
		composer require --dev --no-update "phpunit/phpunit:6.*"
		echo "PHP: 7.2 / PHPUnit 6"
else 
	Echo "Cannot determine PHP version, therefore using PHPUnit as specified in composer"
fi

echo "Composer: update"
composer self-update
echo "Composer: installing dependancies"
composer install --no-interaction --prefer-source
