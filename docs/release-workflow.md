# Release Workflow for Pods Framework

## Update the version

You have a couple of options here:

* Update the "version" in `package.json` and commit to the release branch (the below action will auto-run and set the versions everywhere else)
* Or you can manually run the [WordPress.org Update Versions action](https://github.com/pods-framework/pods/actions/workflows/wporg-update-versions.yml)

## Release

* Ensure all PRs are merged into `release/x.x.x` branch (example: `release/1.2.3`)
* Make a new milestone for the next release if not already existing
* Any remaining PR(s) or issue(s) that did not make the release should be moved to the next milestone
* Create Pull Request from `release/x.x.x` branch into `main`
* Once verified passing all automated tests, and the release has changelog, merge the PR
* [Create a new release](https://github.com/pods-framework/pods/releases/new) on GitHub
* Our [GitHub Action](https://github.com/pods-framework/pods/actions/workflows/wordpress-plugin-deploy.yml) will commit the tag and trunk to the WordPress.org SVN automatically from here!
* After release is finished, WordPress.org will send a release confirmation email to the plugin owner which must be confirmed directly using that link in the email. 
* Once the release is confirmed, verify the plugin appears updated at https://wordpress.org/plugins/pods/
* Confirm the plugin updates properly from any sites you'd like and that you see no major problems in normal functionality

## Preparing Next Release

* Create and checkout a new branch from `main` called `release/x.x.y` (example: `release/1.2.4`)
* Follow the [Update the version](#update-the-version) steps using the next incremental version with Alpha like `1.2.4-a-1`
* Commit and push changes directly into the release branch (or PR it if you do not have write access)
