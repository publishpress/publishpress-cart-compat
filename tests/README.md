# Tests

## Composer suites

Codeception suites live under `tests/codeception/`.

```bash
# PHP unit tests (no WordPress)
composer test:unit

# PHP integration tests (WordPress + WPLoader; needs .env like Cart)
composer test:integration
```

Filter a single test:

```bash
composer test:unit Compat/StudiocartCompatibilityModeTest
composer test:integration Prefix/CptSlugMigrationControlTest
```

Unit bootstrap loads this plugin’s `includes/compat/` packages and sibling
Cart helpers from `../publishpress-cart` (`PPCART_PLUGIN_ROOT`).

Integration WPLoader activates **both** plugins (Cart sibling + this plugin).
Copy `.env.example` to `.env` and point `WP_TESTS_*` at this plugin’s
dev-workspace test install (not Cart’s — ports differ).

`composer test:integration` chowns Linux `wp_test` for WPLoader and symlinks
both plugin checkouts into `tests/codeception/Support/plugins` (`WP_PLUGIN_DIR`).
Cart must be checked out next to this repo as `../publishpress-cart`.

Catalog cases live in `docs/testing/cases/` (config: `.tests.config.yaml`).
Keep the same IT/UT ids as Cart when a leftover assertion moved here.
