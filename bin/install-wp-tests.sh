#!/usr/bin/env bash

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
WP_MAINTENANCE_VERSION="4.3"

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=/tmp/wordpress/

# Placeholder for download agent
# @todo replace back to wget everywhere in this file
download() {  
	if [ `which curl` ]; 
	then  curl -s "$1" > "$2";  
	elif [ `which wget` ]; then  
	get -nv -O "$2" "$1"  
	fi  
}  

# Detect version tag
# Format: N.N.N
if [[ $WP_VERSION =~ [0-9]+\.[0-9]+(\.[0-9]+)? ]]; then  
	WP_TESTS_TAG="tags/$WP_VERSION"  
else  
	# http serves a single offer, whereas https serves multiple. we only want one  
	download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json  
	grep '[0-9]+\.[0-9]+(\.[0-9]+)?' /tmp/wp-latest.json  
	LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')  
	
	if [[ -z "$LATEST_VERSION" ]]; then  
		echo "Latest WordPress version could not be found"  
		exit 1  
	fi  

	WP_TESTS_TAG="tags/$LATEST_VERSION"  
fi  

set -ex

install_wp() {
	echo "Installing WordPress for Unit Tests"

	if [ $WP_CORE_DIR == '' ] && [ -z TRAVIS_JOB_ID ]; then
		echo "Removing existing core WordPress directory"

		rm -rf $WP_CORE_DIR
	fi

	echo "Downloading WordPress"

	mkdir -p $WP_CORE_DIR

	if [ $WP_VERSION == 'bleeding' ]; then
		local ARCHIVE_NAME="master"

		wget -nv -O /tmp/wordpress.zip https://github.com/WordPress/WordPress/archive/${ARCHIVE_NAME}.zip
		unzip /tmp/wordpress.zip -d /tmp/
		mv -f /tmp/WordPress-${ARCHIVE_NAME}/* /tmp/wordpress/
	elif [ $WP_VERSION == 'bleeding-maintenance' ]; then
		local ARCHIVE_NAME="$WP_MAINTENANCE_VERSION-branch"

		wget -nv -O /tmp/wordpress.zip https://github.com/WordPress/WordPress/archive/${ARCHIVE_NAME}.zip
		unzip /tmp/wordpress.zip -d /tmp/
		mv -f /tmp/WordPress-${ARCHIVE_NAME}/* /tmp/wordpress/
	elif [ $WP_VERSION == 'nightly' ]; then
		wget -nv -O /tmp/wordpress.zip https://wordpress.org/nightly-builds/wordpress-latest.zip
		unzip /tmp/wordpress.zip -d /tmp/
	else
		if [ $WP_VERSION == 'latest' ]; then
			local ARCHIVE_NAME="latest"
		else
			local ARCHIVE_NAME="wordpress-$WP_VERSION"
		fi

		wget -nv -O /tmp/wordpress.tar.gz https://wordpress.org/${ARCHIVE_NAME}.tar.gz
		tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C $WP_CORE_DIR

		wget -nv -O $WP_CORE_DIR/wp-content/db.php https://raw.github.com/markoheijnen/wp-mysqli/master/db.php
	fi

	echo "WordPress installed"
}

install_test_suite() {
	echo "Installing Tests Suite for Unit Tests"

	# portable in-place argument for both GNU sed and Mac OS X sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	# set up testing suite
	if [ $WP_TESTS_DIR == '' ] && [ -z TRAVIS_JOB_ID ]; then
		echo "Removing existing Tests Suite directory"

		rm -rf $WP_TESTS_DIR
	fi

	echo "Downloading Tests Suite"

	mkdir -p $WP_TESTS_DIR
	cd $WP_TESTS_DIR
	#svn co --quiet https://develop.svn.wordpress.org/trunk/tests/phpunit/includes/
	svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ $WP_TESTS_DIR/includes 

	wget -nv -O wp-tests-config.php http://develop.svn.wordpress.org/trunk/wp-tests-config-sample.php
	sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR':" wp-tests-config.php
	sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" wp-tests-config.php
	sed $ioption "s/yourusernamehere/$DB_USER/" wp-tests-config.php
	sed $ioption "s/yourpasswordhere/$DB_PASS/" wp-tests-config.php
	sed $ioption "s|localhost|${DB_HOST}|" wp-tests-config.php

	echo "Tests Suite installed"
}

install_db() {
	echo "Setting up Database for Unit Tests"

	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [[ "$DB_SOCK_OR_PORT" =~ ^[0-9]+$ ]] ; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	if [ -z TRAVIS_JOB_ID ]; then
		echo "Removing existing Database"

		# drop database
		mysql --user="$DB_USER" --password="$DB_PASS"$EXTRA -e "DROP DATABASE IF EXISTS $DB_NAME"
	fi

	echo "Creating Database"

	# create database
	mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA

	echo "Database set up"
}

install_wp
install_test_suite
install_db
