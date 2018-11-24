# Pods Tests

## Setup

Before you run tests, you'll want to ensure you run the composer install:

```
composer install
```

Now you'll want to ensure you have a local WP/DB set up. Once you confirm you have that done, you can setup your .env.local file. Copy the `.env.example` file to `.env.local` and fill in your local paths and DB connection information.

Copy the `codeception.example.yml` file to `codeception.yml`, and you're all set to run tests. 
 

## Running Tests

Running tests requires setting a couple of environmental variables in the command line. This ensures the right SQL file gets used and helps distinguish these tests from traversal tests (as described below).

It's better to set them on each run than to run into issues as you switch between the normal tests and the Traversal tests.

```
export PODS_LOAD_DATA='0'
export SQL_DUMP_FILE='dump.sql'
vendor/bin/codecept run wpunit --skip-group='pods-config-required' -vvv
```

You can string these commands together for an easy re-run:

```
export PODS_LOAD_DATA='0'; export SQL_DUMP_FILE='dump.sql'; vendor/bin/codecept run wpunit --skip-group='pods-config-required' -vvv
```

## Testing Traversal (find/field/display)

Running traversal tests requires use of a special SQL dump file that pollutes other tests. These tests have to be run on their own separately in order to ensure they run their fastest (which can still take a while).  

```
export PODS_LOAD_DATA='1'
export SQL_DUMP_FILE='dump-pods-testcase.sql'
vendor/bin/codecept run wpunit --group='pods-config-required' -vvv
```

You can string these commands together for an easy re-run:

```
export PODS_LOAD_DATA='1'; export SQL_DUMP_FILE='dump-pods-testcase.sql'; vendor/bin/codecept run wpunit --group='pods-config-required' -vvv
```

## Testing NPM

To run the JS tests, you'll want to first follow instructions to [setup nvm/npm](https://gist.github.com/d2s/372b5943bce17b964a79).

Once you have that setup, you can run the npm install:

```
npm install
```

Now you can run the tests:

```
npm run test-dfv
```