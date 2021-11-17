# Contribute to the Pods Framework

Community made patches, localizations, bug reports, and contributions are always welcome and are crucial to ensure that Pods Framework remains alive and strong.

When contributing please ensure you follow the guidelines below so that we can keep on top of things.

## Getting Started

* Submit a ticket for your issue, assuming one does not already exist.
  * https://github.com/pods-framework/pods/issues
  * Clearly describe the issue including steps to reproduce the bug.
  * Make sure you fill in the earliest version that you know has the issue as well as the version of WordPress you're using.

## Making Changes

* Fork the repository on GitHub
* Make the changes to your forked repository's code
  * Ensure you stick to the [WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/)
* Create a new branch, named according to our [git workflow](git-workflow.md)
* When committing, reference your issue (if present) and include a note about the fix
* Push the changes to the branch you created and submit a pull request against the corresponding branch according to our [git workflow](git-workflow.md)

At this point you're waiting on us to merge your pull request. We'll review all pull requests, and make suggestions and changes if necessary.

## Tribe Common

In Pods 2.8, we included the [Tribe Common](https://github.com/the-events-calendar/tribe-common) library which helps to power The Events Calendar and Event Tickets. It has many positive benefits as there are some great potential areas for reducing overall needs of custom code that has to be unique to each plugin.

### How we include Tribe Common

For Pods, we will take the "shipped" version of [Tribe Common](https://github.com/the-events-calendar/tribe-common) that comes within The Events Calendar or Event Tickets (whichever is newest at the time). This involves opening up the plugin's zip and pulling out the entire "common" folder.

This is the preferred way to handle dependencies. When using a composer requirement, certain dependencies like Tribe Common have their own dependencies, and some require additional build steps that Pods does not automate. Everything in the Pods GitHub repository is pre-built, meaning you can include it without running anything else. This is preferred as many users will download release ZIPs of certain release branches.

# Additional Resources
* [General GitHub documentation](https://help.github.com/)
* [GitHub pull request documentation](https://help.github.com/send-pull-requests/)
