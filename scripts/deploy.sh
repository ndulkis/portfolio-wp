#!/bin/sh
# Live deploy step. SpinupWP runs it after each git deploy (Git tab → Deployment script: sh scripts/deploy.sh),
# from the site's files directory (the web root, where this repo is checked out). Safe to re-run.
# Live-safe: never seeds content or sets test keys (that's setup.sh, local only).
set -e

# WordPress core isn't in git, so its version is pinned here: update core by changing this on an update branch.
WP_VERSION=7.1.5
[ "$(wp core version 2>/dev/null)" = "$WP_VERSION" ] || wp core download --version="$WP_VERSION" --skip-content --force
wp core is-installed || { echo "WordPress isn't installed yet: create wp-config.php and run wp core install once."; exit 1; }
wp core update-db

wp plugin activate --all   # Plugins live in git; activate whatever is there.
wp rewrite flush
wp cache flush
wp spinupwp cache purge-site 2>/dev/null || true   # SpinupWP's page cache, when its plugin is present.
