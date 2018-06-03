#!/bin/bash

# Run this command from the repo root.
# The path argument you pass will be relative to the repo root.

# Example usage:
# bash bin/phpcs-autofix.sh
# bash bin/phpcs-autofix.sh classes/fields
# bash bin/phpcs-autofix.sh classes/fields/text.php

default_path="."
file_path=${1:-$default_path}

echo Running PHPCS autofix
./vendor/bin/phpcbf ${file_path}
