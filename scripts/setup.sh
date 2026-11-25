#!/bin/sh
# One-shot local setup inside the Lando appserver. Safe to re-run.
set -e
cd /app

[ -f wp-load.php ] || wp core download --skip-content
[ -f wp-config.php ] || wp config create \
  --dbname=wordpress --dbuser=wordpress --dbpass=wordpress --dbhost=database --skip-check

if ! wp core is-installed 2>/dev/null; then
  wp core install --url=https://portfolio-wp.lndo.site --title="Nathan Dulkis" \
    --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email
fi

wp theme activate nd-dive
wp rewrite structure '/%postname%/'
wp eval-file scripts/seed.php
wp rewrite flush

echo "Ready: https://portfolio-wp.lndo.site  (admin / admin — local only)"
