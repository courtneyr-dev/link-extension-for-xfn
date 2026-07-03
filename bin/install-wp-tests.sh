#!/usr/bin/env bash
#
# Install WordPress test suite for PHPUnit integration tests.
# Based on wp-cli/scaffold-command install-wp-tests.sh.
#
# Usage: bin/install-wp-tests.sh [db-name] [db-user] [db-pass] [db-host] [wp-version] [skip-db]
#

set -euo pipefail

DB_NAME=${1-wordpress_test}
DB_USER=${2-root}
DB_PASS=${3-''}
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
SKIP_DB_CREATE=${6-false}

TMPDIR=${TMPDIR-/tmp}
TMPDIR=$(echo "$TMPDIR" | sed -e "s/\/$//")
WP_TESTS_DIR=${WP_TESTS_DIR-$TMPDIR/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-$TMPDIR/wordpress}

download() {
	if [ "$(which curl)" ]; then
		if [ "$2" = "-" ]; then
			# "-" means stdout; "> -" would create a file literally named "-".
			curl -fsSL "$1"
		else
			curl -fsSL "$1" > "$2"
		fi
	elif [ "$(which wget)" ]; then
		wget -nv -O "$2" "$1"
	fi
}

wp_api_version_url() {
	if [ "$WP_VERSION" = "latest" ]; then
		echo "https://api.wordpress.org/core/version-check/1.7/"
	elif [ "$WP_VERSION" = "trunk" ] || [ "$WP_VERSION" = "nightly" ]; then
		echo ""
	else
		echo "https://api.wordpress.org/core/version-check/1.7/?version=$WP_VERSION"
	fi
}

resolve_wp_version() {
	local api_url
	api_url=$(wp_api_version_url)

	if [ -z "$api_url" ]; then
		WP_VERSION="trunk"
		return
	fi

	local version_json
	version_json=$(download "$api_url" -)

	if [ "$(echo "$version_json" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d['offers'][0]['current'])" 2>/dev/null)" != "" ]; then
		WP_VERSION=$(echo "$version_json" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d['offers'][0]['current'])")
	fi
}

install_wp() {
	if [ -d "$WP_CORE_DIR" ]; then
		return
	fi

	mkdir -p "$WP_CORE_DIR"

	local archive_url

	if [ "$WP_VERSION" = "trunk" ] || [ "$WP_VERSION" = "nightly" ]; then
		archive_url="https://wordpress.org/nightly-builds/wordpress-latest.zip"
	else
		if [ "$WP_VERSION" = "latest" ]; then
			resolve_wp_version
		fi
		archive_url="https://wordpress.org/wordpress-${WP_VERSION}.zip"
	fi

	download "$archive_url" /tmp/wordpress.zip

	# Extract to a staging dir: unzipping straight to /tmp collides with
	# WP_CORE_DIR when TMPDIR is unset (Linux CI), making cp copy the
	# tree onto itself and fail.
	local staging
	staging=$(mktemp -d)
	unzip -q /tmp/wordpress.zip -d "$staging"
	cp -a "$staging/wordpress/." "$WP_CORE_DIR"
	rm -rf "$staging" /tmp/wordpress.zip
}

install_test_suite() {
	# Portable way to get the directory of this script.
	local SCRIPT_DIR
	SCRIPT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)

	if [ ! -d "$WP_TESTS_DIR" ]; then
		mkdir -p "$WP_TESTS_DIR"

		# svn is gone from GitHub's runner images (removed 2024), so pull
		# the test suite from the wordpress-develop GitHub mirror instead.
		local develop_ref

		if [ "$WP_VERSION" = "trunk" ] || [ "$WP_VERSION" = "nightly" ]; then
			develop_ref="refs/heads/trunk"
		else
			if [ "$WP_VERSION" = "latest" ]; then
				resolve_wp_version
			fi

			# GitHub's wordpress-develop tags are full triplets (7.0.0),
			# unlike core zip names (7.0).
			local tag="$WP_VERSION"
			if [[ "$tag" =~ ^[0-9]+\.[0-9]+$ ]]; then
				tag="${tag}.0"
			fi
			develop_ref="refs/tags/${tag}"
		fi

		local suite_staging
		suite_staging=$(mktemp -d)
		download "https://github.com/WordPress/wordpress-develop/archive/${develop_ref}.tar.gz" "$suite_staging/develop.tar.gz"

		# GNU tar needs --wildcards for glob members; bsdtar globs by default.
		local wildcards=""
		if tar --version 2>/dev/null | grep -q GNU; then
			wildcards="--wildcards"
		fi
		tar -xzf "$suite_staging/develop.tar.gz" -C "$suite_staging" $wildcards --strip-components=3 \
			"*/tests/phpunit/includes" "*/tests/phpunit/data"
		mv "$suite_staging/includes" "$WP_TESTS_DIR/includes"
		mv "$suite_staging/data" "$WP_TESTS_DIR/data"
		rm -rf "$suite_staging"
	fi

	if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
		download "https://raw.githubusercontent.com/WordPress/wordpress-develop/trunk/wp-tests-config-sample.php" "$WP_TESTS_DIR/wp-tests-config.php"

		# Portable sed — macOS and Linux.
		if [ "$(uname -s)" = "Darwin" ]; then
			local ioption='-i.bak'
		else
			local ioption='-i'
		fi

		sed $ioption "s|dirname( __FILE__ ) . '/src/'|'${WP_CORE_DIR}/'|" "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR/wp-tests-config.php"

		rm -f "$WP_TESTS_DIR/wp-tests-config.php.bak"
	fi
}

install_db() {
	if [ "$SKIP_DB_CREATE" = "true" ]; then
		return 0
	fi

	local EXTRA=""

	if [ "$(echo "$DB_HOST" | grep ':')" ]; then
		local PARTS
		IFS=':' read -ra PARTS <<< "$DB_HOST"
		if [ "${PARTS[0]}" != "localhost" ] && [ "${PARTS[0]}" != "127.0.0.1" ]; then
			EXTRA=" --host=${PARTS[0]} --port=${PARTS[1]}"
		elif [[ "${PARTS[1]}" == /* ]]; then
			EXTRA=" --socket=${PARTS[1]}"
		else
			EXTRA=" --host=${PARTS[0]} --port=${PARTS[1]}"
		fi
	elif [ "$DB_HOST" != "localhost" ] && [ "$DB_HOST" != "127.0.0.1" ]; then
		EXTRA=" --host=$DB_HOST"
	fi

	# Create the database if it doesn't exist.
	if [ -n "$DB_PASS" ]; then
		local PASS_ARG="-p${DB_PASS}"
	else
		local PASS_ARG=""
	fi

	mysqladmin create "$DB_NAME" --user="$DB_USER" $PASS_ARG $EXTRA 2>/dev/null || true
}

install_wp
install_test_suite
install_db
