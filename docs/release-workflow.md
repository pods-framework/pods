# Release Workflow for Pods Framework

## Prerequisites

* Composer
* Node
* NPM
* grunt-cli

## NPM Install

Next you'll install the packages for this repo using: `npm install`

## Version Changes

* Set new version in `package.json` (it might be something like `1.2.3-a-1`, so set it to `1.2.3`)
* Run the `version_number` task using `npm run version_number`
* _TODO: Would be great if running `npm run version_number` would ask for the version number to save a step_

## Release

* Ensure all PRs are merged into `release/x.x.x` branch (example: `release/1.2.3`)
* Make a new milestone for the next release if not already existing
* Any remaining PRs or issues that did not make the release should be moved to the next milestone
* Create Pull Request from `release/x.x.x` branch into `main`
* Once verified passing all automated tests, and the release has changelog, merge the PR
* [Create a new release](https://github.com/pods-framework/pods/releases/new) on GitHub
* Our [GitHub Action](https://github.com/pods-framework/pods/blob/release/main/.github/workflows/wordpress-plugin-deploy.yml) will commit the tag and trunk to the WordPress.org SVN automatically from here!
* After release is finished, verify the plugin appears updated at https://wordpress.org/plugins/pods/
* Confirm the plugin updates properly from any sites you'd like and that you see no major problems in normal functionality

## Preparing Next Release

* Create and checkout a new branch from `main` called `release/x.x.y` (example: `release/1.2.4`)
* Follow the [Version Changes](#version-changes) steps for the next incremental version with Alpha like `1.2.4-a-1`
* Commit and push changes directly into the release branch (or PR it if you do not have write access)
