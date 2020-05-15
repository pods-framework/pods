#!/usr/bin/env bash

git_clone_required_plugins(){
	plugins_folder="${WP_ROOT_FOLDER}/wp-content/plugins"

	cd ${plugins_folder}

	declare -a required_plugins=(`echo ${REQUIRED_PLUGIN_REPOS}`);

	for plugin_repo in "${required_plugins[@]}"; do
		# travis-ci.com
		# plugin_repo_url="git@github.com:${plugin_repo}.git"

		# travis-ci.org
		plugin_repo_url="git://github.com/${plugin_repo}.git"

		plugin_slug="$(basename ${plugin_repo})"

	  	if [[ -n "$(git ls-remote --heads ${plugin_repo_url} ${TRAVIS_PULL_REQUEST_BRANCH})" ]]; then
			branch="${TRAVIS_PULL_REQUEST_BRANCH}";
	  	elif [[ -n "$(git ls-remote --heads ${plugin_repo_url} ${TRAVIS_BRANCH})" ]]; then
			branch="${TRAVIS_BRANCH}";
	  	else
			branch="master";
	  	fi;

		echo "Cloning branch ${branch} for plugin ${plugin_slug}";

	  	git clone --single-branch --branch ${branch} ${plugin_repo_url} ${plugin_slug};

		cd ${plugin_slug};

	  	if [[ -f ".gitmodules" ]]; then
			echo "Setting up submodules for plugin ${plugin_slug}";

			# Tweak git to correctly work with submodules.
			sed -i 's/git@github.com:/git:\/\/github.com\//' .gitmodules

			# Setup submodules.
			git submodule update --recursive --init;
	  	fi;

		echo "Installing composer for plugin ${plugin_slug}";

		# Install composer on plugin.
		composer update --prefer-dist --no-dev;

		# Install composer on common if it exists.
	  	if [[ -d "common" ]]; then
			cd common;

			echo "Installing common composer for plugin ${plugin_slug}";

			composer update --prefer-dist --no-dev;
	  	fi;

	  	cd ${plugins_folder}
	done
}