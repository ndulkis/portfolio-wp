#!/bin/sh
# One-shot local setup inside the Lando appserver. Safe to re-run.
set -e
cd /app

[ -f wp-load.php ] || wp core download --skip-content --version="$(sed -n 's/^WP_VERSION=//p' scripts/deploy.sh)"   # Same core as live.
[ -f wp-config.php ] || wp config create \
  --dbname=wordpress --dbuser=wordpress --dbpass=wordpress --dbhost=database --skip-check

if ! wp core is-installed 2>/dev/null; then
  wp core install --url=https://portfolio-wp.lndo.site --title="Nathan Dulkis" \
    --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email
fi

# Cloudflare Turnstile (wp-content/mu-plugins/nd-turnstile.php): Cloudflare's public always-pass TEST keys, local only.
# The live site's wp-config.php gets the real keys from the Cloudflare dashboard (never commit them).
wp config has ND_TURNSTILE_SITE_KEY || wp config set ND_TURNSTILE_SITE_KEY 1x00000000000000000000AA
wp config has ND_TURNSTILE_SECRET || wp config set ND_TURNSTILE_SECRET 1x0000000000000000000000000000000AA

wp theme activate nd-dive
wp plugin activate --all   # Plugins live in git (wp-content/plugins); activate whatever is there.
wp rewrite structure '/%postname%/'
wp option update timezone_string America/Los_Angeles   # Pacific time, daylight saving included.
wp option update admin_email ndulkis@gmail.com        # Password resets, security and lockout notices.
wp option delete new_admin_email 2>/dev/null || true  # Skip WordPress's confirm-by-email step.
wp eval-file scripts/seed.php
wp rewrite flush

echo "Ready: https://portfolio-wp.lndo.site  (admin / admin — local only)"
