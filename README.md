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

Works on any site where WP-CLI can load active Cart + Cart Compatibility:

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
