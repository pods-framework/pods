## Setup your Installation to run a Grunt Release
* Install Node, NPM, and grunt-cli globally if not already installed.
    * https://github.com/joyent/node/wiki/installing-node.js-via-package-manager
* Install grunt-init globally if not already installed.
    * `npm install -g grunt-init`
* Install composer to usr/local/bin/composer if not already installed
* Switch to this git repositor directory 
* Install node modules
    * `npm install`
    * BTW On OSX you generally need `sudo` to make this work.

## Do a Grunt Release
* Merge 2.x branch to master.
* Set new version in `package.json`
* To make a new release (update version, tag, create zip, push all those changes to git origin)
    * Set a new version number in `package.json`
    * Run `grunt release`
    
## What It Does?
* Changes version number in all the places that is needed.
* Updates translations
* Makes a commit with version number.
* Tags the Git repo with new version number.
* Updates SVN trunk.
* Makes a new SVN tag.

## Preparing Next Release
* Merge Master into 2.x
* Set New Version in `package.json` to next incremental version with Alpha (ie, 2.6.x-a-1)
* Run `grunt version_number` to update the version number in all related files
* Run `grunt branch_name_2x` to update the branch numbers in all files
* Commit changes to 2.x and push

## Future Tweaks we need to make
* Don't do a full SVN checkout, make use of partial checkouts (because of /tags/)
