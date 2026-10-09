# Seed data for Data migration testing

Two seeders plant leftover StudioCart-shaped rows so you can exercise **Cart → StudioCart → Data migration** without a real StudioCart import. Neither mutates data until you click **Start migration** in the wizard.

| Command | Purpose | Where it lives |
|---------|---------|----------------|
| `seed:store` | Full leftover store (catalog, customers, orders, options, user-meta) | `tests/store-fixtures/` (test-only; not shipped) |
| `seed:migration` | Minimal leftovers for every wizard step | Plugin WP-CLI (`ppcartcomp seed-migration`) |

Prefer **`seed:store`** for end-to-end QA. Use **`seed:migration`** when you only need sparse rows for each step.

## Prerequisites

- PublishPress Cart + PublishPress Cart Compatibility active
- WP-CLI (or DDEV with this repo’s `.ddev/`)
- Migration wizard built if you changed React: `npm run build:admin`

Optional safety net before a destructive reseed:

```bash
composer site:backup
```

## Full leftover store (`seed:store`)

Plants `sc_*` CPTs, `_sc_*` post/option/user meta, leftover shortcodes, COD settings, checkout pages, Elementor-built pages, and ~one month of dated orders.

```bash
composer seed:store
./bin/seed-store --reset
ddev seed-store --reset
```

Useful flags (pass after `--` with Composer, or to the bin/ddev command):

| Flag | Effect |
|------|--------|
| `--reset` | Clean previous seed, then seed again |
| `--clean` | Remove previous seed only |
| `--products=<n>` | Product count (default 14) |
| `--customers=<n>` | Customer count (default 40) |

Seed customers: `sc_seed_customer_001` … password `studiocart`.

What you can migrate after seeding:

- CPT / taxonomy / capability leftovers
- Role slugs
- Post-meta and **user-meta** (phone, address_1/2, city, state, zip, country, customer ids)
- Shortcode tags in posts and email options
- Shortcode tags in Elementor data (`seed-elementor-account`, `seed-elementor-checkout`: tags only in `_elementor_data`; activate Elementor to see them render)
- Option keys (`_sc_*` / `sc_*` / `ncs_*`)
- Custom tables when present

Then open **Cart → StudioCart → Data migration** and run **Start migration**.

## Minimal wizard leftovers (`seed:migration`)

Small fixed set for each wizard package (posts, taxonomies, meta, shortcodes, roles/caps, optional `ncs_*` tables). Resets migration state options first.

```bash
composer seed:migration
composer seed:migration -- --count=10
./bin/seed-migration --count=10 --skip-tables
ddev seed-migration
wp ppcartcomp seed-migration --count=10
```

| Flag | Effect |
|------|--------|
| `--count=<n>` | Leftover posts per CPT (default 3); meta scales with this |
| `--skip-tables` | Do not create leftover custom tables |

## After seeding

1. Confirm leftovers exist (e.g. wizard progress shows incomplete steps / remaining counts).
2. Run Data migration from the StudioCart screen (one general confirm; no silent renames on activate).
3. Re-seed with `--reset` (store) or re-run `seed:migration` when you need a clean leftover baseline again.

## Related

- [tests/README.md](../../tests/README.md) — suite runners + short seed notes
- [README.md](../../README.md) — install and seed command list
- Catalog shapes: `tests/store-fixtures/catalog.php` (UT-243)
