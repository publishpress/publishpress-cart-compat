#!/usr/bin/env bash
# Linux Docker bind-mounts keep container UIDs. Mac Docker Desktop remaps them,
# so this is a no-op on Darwin. On Linux:
# - MariaDB runs as mysql (999); host-created db_test is uid 1000.
# - WPLoader on the host needs write access to wp_test (uid 33 / www-data).
# Do not recurse into the plugin bind-mount: it is the repo.
set -euo pipefail

if [[ "$(uname -s)" != "Linux" ]]; then
    exit 0
fi

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$ROOT"

if [[ ! -f .env ]]; then
    exit 0
fi

set -a
# shellcheck disable=SC1091
source .env
set +a

if ! docker info >/dev/null 2>&1; then
    exit 0
fi

WP_CONTAINER="${CONTAINER_NAME:-publishpress-cart-compat}_env_wp_test"
DB_CONTAINER="${CONTAINER_NAME:-publishpress-cart-compat}_env_db_test"
HOST_OWNER="$(id -u):$(id -g)"
PLUGIN_SLUG="${PLUGIN_SLUG:-publishpress-cart-compat}"

if ! docker ps -q --filter "name=${DB_CONTAINER}" | grep -q .; then
    exit 0
fi

if docker ps -q --filter "name=${WP_CONTAINER}" | grep -q .; then
    docker exec -u root "$WP_CONTAINER" sh -c "
        chown ${HOST_OWNER} /var/www/html
        for p in /var/www/html/*; do
            [ \"\$(basename \"\$p\")\" = wp-content ] && continue
            chown -R ${HOST_OWNER} \"\$p\"
        done
        if [ -d /var/www/html/wp-content ]; then
            chown ${HOST_OWNER} /var/www/html/wp-content
            for p in /var/www/html/wp-content/*; do
                [ \"\$(basename \"\$p\")\" = plugins ] && continue
                chown -R ${HOST_OWNER} \"\$p\"
            done
            if [ -d /var/www/html/wp-content/plugins ]; then
                chown ${HOST_OWNER} /var/www/html/wp-content/plugins
                for p in /var/www/html/wp-content/plugins/*; do
                    [ \"\$(basename \"\$p\")\" = ${PLUGIN_SLUG} ] && continue
                    [ \"\$(basename \"\$p\")\" = publishpress-cart ] && continue
                    chown -R ${HOST_OWNER} \"\$p\"
                done
            fi
        fi
    "
fi

# Must run last: the plugin mount includes this datadir.
docker exec -u root "$DB_CONTAINER" chown -R mysql:mysql /var/lib/mysql
