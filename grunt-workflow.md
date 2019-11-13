# Grunt Workflow for Releases

## Setup your Installation to run a Grunt Release

* Install Node, NPM, and grunt-cli globally if not already installed.
    * https://github.com/joyent/node/wiki/installing-node.js-via-package-manager
* Install grunt-init globally if not already installed.
    * `npm install -g grunt-init grunt-cli`
* Install composer to usr/local/bin/composer if not already installed
* Switch to this git repository directory
* Install node modules
    * `npm install`

## Initial Release

* Work is put into `2.x` branch from various feature branches
* Create Pull Request from `2.x` branch into `master`
* Once verified passing all unit tests, and the release has changelog, merge PR

## Do a Grunt Preparation

* Fetch latest origin from GitHub (**THIS IS IMPORTANT**)
    * Ensure your `master` has pulled from the latest version, with no uncommitted changes
    * Ensure your Git repo has `master` currently checked out
    * TODO: We should improve this so it's done automatically in the script
* Set new version in `package.json` (it might be something like `2.6.4-a-1`, so set it to `2.6.4`)
    * TODO: Would be great if running `grunt release` would ask for the version number to save a step
* Run `grunt release`, which will run the following tasks:
    * Update branch name in README.md and init.php to `master`
    * Update version number in readme.txt (stable tag) and init.php
    * Commits branch and version number changes in files to GitHub with commit message as version number `Pods {version}`
    * Tags `master` as version number `{version}`
    * Pushes `master` and new tag (**NOTE** This is not currently happening right now, you need to manually push the master branch and the new tag)
    * GitHub Action will commit the tag/trunk to wordpress.org when the tag is published

## TLDR: What It Does

* Changes version number in all the places that is needed.
* Makes branch name changes in various places.
* Commits, pushes to GitHub `master`
* Tags `master` to `{version}`
* GitHub Action will take care of the rest as soon as the tag is published

## Preparing Next Release

* Checkout `2.x` and ensure you've pulled the latest changes. From Terminal, `git checkout 2.x`.
* Merge `master` branch into `2.x` to bring over the latest release changes. From Terminal, `git merge master`.
* Set new version in `package.json` to next incremental version with Alpha like `2.6.4-a-1`
* Run `grunt version_number` to update the version number in all related files
* Run `grunt branch_name_2x` to update the branch numbers in all files
* Commit changes to 2.x and push. From Terminal, `git commit -a -m "Prepping 2.x"` followed by `git push`. 
* TODO: We could do the 2.x checkout, master merge, prompt for new version number, run the other grunt tasks and commit/push to Git all in a new grunt task like `grunt prepare_next_release`
