#!/usr/bin/env bash

default_path=''
test_path=${1:-$default_path}

./vendor/bin/phpunit ${test_path}