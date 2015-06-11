## Do a Grunt Release:
* Install Node, NPM, and grunt-cli globally if not already installed.
    * https://github.com/joyent/node/wiki/installing-node.js-via-package-manager
* Install grunt-init globally if not already installed.
    * `npm install -g grunt-init`
* Install composer to usr/local/bin/composer if not already installed
* Switch to this directory
* Install node modules
    * `npm install`
    * BTW On OSX you generally need `sudo` to make this work.
* Merge dev branch to master.
* Set new version in `package.json`
* To make a new release (update version, tag, create zip, push all those changes to git origin)
    * Set a new version number in package.json
    * `grunt release`
    
## What It Does?
* Changes version number in all the places that is needed.
* Updates translations
* Makes a commit with version number.
* Tags the Git repo with new version number.
* Updates SVN trunk.
* Makes a new SVN tag.

## Future Tweaks we need to make
* Don't do a full SVN checkout, make use of partial checkouts (because of /tags/)