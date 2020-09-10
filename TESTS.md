# Pods Tests

## Setup

Before you run tests, you'll want to ensure you run the composer install:

```
composer install
```

Now you'll want to ensure you have a local WP/DB set up. Once you confirm you have that done, you can setup your .env.local file. Copy the `.env.example` file to `.env.local` and fill in your local paths and DB connection information.

Copy the `codeception.example.yml` file to `codeception.yml`, and you're all set to run tests.


## Running Tests

Running tests is pretty simple, you just run the codeception command `codecept` and which suite to run:

```
vendor/bin/codecept run wpunit -vvv
```

## Testing Traversal (find/field/display)

Running traversal tests requires use a different codeception test suite `wpunit-traversal` which makes use of special configuration files (see tests/codeception/_data/traversal-*.json). These tests have to be run apart from the other tests to prevent pollution of their configurations or data in the much more cleaner `wpunit` tests:

```
vendor/bin/codecept run wpunit-traversal -vvv
```

## Testing NPM

To run the JS tests, you'll want to first follow instructions to [setup nvm/npm](https://gist.github.com/d2s/372b5943bce17b964a79).

Once you have that setup, you can run the npm install:

```
npm install
```

Now you can run the tests:

```
npm run jest-tests
```
