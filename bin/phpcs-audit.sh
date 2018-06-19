#!/bin/bash

# Run this command from the repo root.
# The path argument you pass will be relative to the repo root.

# Example usage:
# bash bin/phpcs-audit.sh
# bash bin/phpcs-audit.sh classes/fields
# bash bin/phpcs-audit.sh classes/fields/text.php

# Generate custom report files like report-full-text.txt using second argument for affix:
# bash bin/phpcs-audit.sh classes/fields/text.php -text

default_path="."
file_path=${1:-$default_path}

default_file_affix=""
file_affix=${2:-$default_file_affix}

declare -a types=("full" "source")

echo Running PHPCS reports

for type in ${types[@]}
do
  echo Running PHPCS report: ${type}
  ./vendor/bin/phpcs --report="${type}" --report-file="phpcs-report-${type}${file_affix}.txt" ${file_path}
done;
