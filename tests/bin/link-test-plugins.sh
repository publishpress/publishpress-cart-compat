#!/usr/bin/env bash
# Host-visible plugin trees for WPLoader. Docker bind-mounts the companion
# repo into wp_test/wp-content/plugins inside the container only; on the host
# that path is an empty placeholder, so Cart's early include cannot read
# publishpress-cart-compat from WP_PLUGIN_DIR. Point WP_PLUGIN_DIR at this
# directory instead (see Integration.suite.yml).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
PLUGINS_DIR="${PPCARTCOMP_TEST_PLUGINS_DIR:-$ROOT/tests/codeception/Support/plugins}"
CART_SIBLING="$(cd "$ROOT/../publishpress-cart" 2>/dev/null && pwd || true)"

mkdir -p "$PLUGINS_DIR"

if [[ -z "$CART_SIBLING" || ! -f "$CART_SIBLING/publishpress-cart.php" ]]; then
    echo "link-test-plugins: sibling Cart checkout not found at $ROOT/../publishpress-cart" >&2
    exit 1
fi

ln -sfn "$CART_SIBLING" "$PLUGINS_DIR/publishpress-cart"
ln -sfn "$ROOT" "$PLUGINS_DIR/publishpress-cart-compat"

printf '%s\n' "$PLUGINS_DIR"
