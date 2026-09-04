# PublishPress Cart Compatibility

StudioCart shims and data migration for PublishPress Cart.

This repository is the companion plugin (`publishpress-cart-compat`). It does not replace PublishPress Cart and is not a live StudioCart integration.

## Requirements

- PHP 7.4+
- WordPress 6.7+
- [PublishPress Cart](https://github.com/publishpress/publishpress-cart) (`publishpress-cart`)

## Layout

| Path | What lives here |
|------|-----------------|
| `publishpress-cart-compat.php` | Bootstrap, constants |

## Development

```bash
composer install
npm install && npm run build:admin   # migration wizard React assets
```

### Seed migration leftovers

Works on any site where WP-CLI can load active Cart + Cart Compatibility. Full guide: [docs/testing/seed-migration-data.md](docs/testing/seed-migration-data.md).

```bash
wp ppcartcomp seed-migration
wp --path=/path/to/wordpress ppcartcomp seed-migration --count=10
wp ppcartcomp seed-migration --skip-tables
```

From this repo (`composer` / `./bin` auto-detect `.web`, or ddev when `.ddev/` is up):

```bash
composer seed:migration
composer seed:migration -- --count=10
./bin/seed-migration --count=10
WP_PATH=/path/to/wordpress composer seed:migration
```

Optional: `ddev seed-migration` (same underlying WP-CLI command).

### Seed a leftover StudioCart store (tests only)

Dummy catalog, checkout pages, customers, and COD for local QA. Lives under `tests/store-fixtures/` and is loaded with WP-CLI `--require` — it is not shipped as plugin code.

```bash
composer seed:store
./bin/seed-store --reset
ddev seed-store
wp --path=.web --require=tests/store-fixtures/cli.php ppcartcomp seed-store
```

Seed customers log in as `sc_seed_customer_001` (password `studiocart`). `--clean` removes the previous seed. Default volume is a leftover month of store activity (14 products, 40 customers, 96 orders, 20 subscriptions). Use `--reset` when replacing an older seed.

### Snapshot the local WordPress database

Saves the DDEV database to `tmp/site-backup/`. Restore imports that dump as-is.

```bash
composer site:backup
composer site:restore
```
