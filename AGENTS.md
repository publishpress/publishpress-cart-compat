# PublishPress Cart Compatibility

StudioCart shims and data migration for PublishPress Cart

Full rules: [docs/dev/ai/plugin-rules.md](docs/dev/ai/plugin-rules.md)

Optional research (private): https://github.com/publishpress/team-handbook/tree/development/docs/dev — if a sibling `team-handbook` checkout exists, prefer that. If unreachable, follow this file and `docs/dev/ai/plugin-rules.md`. 

Do not invent process. Do not clone the handbook.

## Hard rules

1. WordPress.org ships only the **Free plugin**. No **Pro code** in Free. **Extension points** (hooks, filters, interfaces) in Free are allowed.
2. The **Pro plugin** is delivered through **Package Server**, vendors Free via Composer, and must not run beside standalone Free.
3. Treat Free hooks, filters, internals, and data shapes the Pro plugin depends on as a public contract. Do not “clean up” unused-looking APIs. If unsure, say so.
4. Product issues live in the **Free plugin** GitHub repo. From Pro work: `Fixes publishpress/<free-repo>#N`, never bare `#N`.
5. PHP: obey **this** plugin’s declared minimum (`composer.json` / plugin header). No newer syntax without a fallback.
6. After behavior changes, run the tests this repo already has. Do not invent a full suite.
7. Do not search `lib/vendor/`, `vendor/`, or `node_modules/` for how this plugin works.

## This plugin

- Edition: Free companion (not Pro). Requires PublishPress Cart Free (or Pro-vendored Free).
- Sibling repo: `publishpress-cart`
- Layout: `publishpress-cart-compat.php` bootstrap; packages under `includes/`
- Cart live helpers (`ppcart_live_*`, `ppcart_meta_key`, …) stay `ppcart_*` (Cart public contract). This plugin’s own symbols use `ppcartcomp`.

## Prefixes

| Kind | Form | Example |
|------|------|---------|
| Classes | `PPCartComp_` | `PPCartComp_Studiocart_Compatibility_Mode` |
| Functions | `ppcartcomp_` | `ppcartcomp_activate` |
| Constants | `PPCARTCOMP_` | `PPCARTCOMP_VERSION` |
| Files | `class-ppcartcomp-{kebab}.php` | `class-ppcartcomp-studiocart-compatibility-mode.php` |
| Text domain / slug | `publishpress-cart-compat` | (unchanged) |

Do not use `PPCART_COMPAT_`, `PPCart_Compat_`, or `ppcart_compat_`.

## Layout

| Path | What lives here |
|------|-----------------|
| `publishpress-cart-compat.php` | Bootstrap, `PPCARTCOMP_*` constants |

Do not search `lib/vendor/`, `vendor/`, or `node_modules/` (see `.cursorignore`).

## Tests

| Suite | Where | Run |
|-------|--------|-----|
| Unit | `tests/codeception/Unit/` | `composer test:unit` |
| Integration | `tests/codeception/Integration/` | `composer test:integration` |
| Regression | `tests/playwright/regression/` | `composer test:regression` |
| Admin e2e | `tests/playwright/admin/` | `composer test:admin` |
| Smoke | `tests/playwright/smoke/` | see `tests/README.md` |

Dev seed for Data migration leftovers: `wp ppcartcomp seed-migration` (also `composer seed:migration` / `./bin/seed-migration`).

Test-only leftover StudioCart store: `composer seed:store` / `./bin/seed-store` / `ddev seed-store` (loads `tests/store-fixtures/`, not plugin `includes/`).

## Conventions

- Prefixes: `PPCartComp_`, `ppcartcomp_`, `PPCARTCOMP_`. Files: `class-ppcartcomp-*.php`.
- New PHP files: one concern, under 500 lines. Move code; don't rewrite public APIs.
- Test catalog + Playwright/Codeception: `.cursor/skills/test-authoring/`.
- Feature records: `.cursor/skills/plugin-features/`.
