#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$ROOT"

bash "$ROOT/tests/bin/fix-linux-test-volume-perms.sh"
export PPCARTCOMP_TEST_PLUGINS_DIR
PPCARTCOMP_TEST_PLUGINS_DIR="$(bash "$ROOT/tests/bin/link-test-plugins.sh")"

# RSS cap. PHP memory_limit is Zend-only; a Codeception child hit 38–48 GiB RSS
# on 2026-09-06 and froze the session. Override with TEST_MEMORY_MAX; disable
# with TEST_MEMORY_UNCAP=1. Fallback when the user bus is missing (typical CI).
max="${TEST_MEMORY_MAX:-8G}"
if [[ -z "${TEST_MEMORY_UNCAP:-}" ]] && command -v systemd-run >/dev/null 2>&1 \
  && busctl --user status >/dev/null 2>&1; then
  exec systemd-run --user --scope --quiet --collect --same-dir \
    -p MemoryMax="$max" \
    -p MemorySwapMax=0 \
    -- vendor/bin/codecept run Integration "$@"
fi

exec vendor/bin/codecept run Integration "$@"
