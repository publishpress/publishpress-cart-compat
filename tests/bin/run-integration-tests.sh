#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$ROOT"

bash "$ROOT/tests/bin/fix-linux-test-volume-perms.sh"
export PPCARTCOMP_TEST_PLUGINS_DIR
PPCARTCOMP_TEST_PLUGINS_DIR="$(bash "$ROOT/tests/bin/link-test-plugins.sh")"
exec vendor/bin/codecept run Integration "$@"
