# Issue 629 — unique prefixes via Compatibility Mode

GitHub: [publishpress/publishpress-cart#629](https://github.com/publishpress/publishpress-cart/issues/629)
Related review ticket: [#617](https://github.com/publishpress/publishpress-cart/issues/617)

WordPress.org requires unique prefixes (≥4 characters). Target:

| Kind | Prefix | Example |
|------|--------|---------|
| Classes | `PPCart_` | `PPCart_Order` |
| Functions, hooks, options, constants, globals | `ppcart_` | `ppcart_format_price()`, `PPCART_VERSION` |
| Files, asset handles, CSS/BEM | `ppcart-` | `class-ppcart-order.php`, `.ppcart-settings` |

Not the reverted rebrand: **`PP_Cart_` / `pp_cart_` / `pp-cart-` are wrong** (underscore after `PP`). Do not reintroduce them.

## What “done” looks like

1. First-party PHP **outside** `includes/compat/studiocart-compatibility-mode/` uses only `PPCart_` / `ppcart_` / `ppcart-`.
2. StudioCart-era PHP names that third-party plugins still call live **only** in that package, registered when Compatibility Mode is on.
3. Canonical Cart never branches on `sc_cart_supports( 'studiocart-compatibility-mode' )` except inside the package.
4. Frozen persisted strings (leftover CPT rows, existing `_sc_*` keys) stay
   until the user approves a **data migration**. REST namespace is
   `publishpress-cart/v1` (slice 29 hard cutover; leftover `sc/v1` is
   unregistered in Free). Shortcode tags
   are planned as `ppcart_*` (slice 14; Compatibility Mode dual-registers
   shipped legacy tags). wp-admin page slugs are already approved for a
   canonical-only hard cutover. AJAX POST actions are canonical `ppcart_*` in
   first-party code; Compatibility Mode dual-registers shipped `sc_*` / `ncs_*`
   action values.
5. Text domain stays `publishpress-cart`. Plugin display name stays PublishPress Cart.
6. Public routing uses canonical `ppcart-*` query vars. Compatibility Mode may
   recognize legacy `sc-*` URLs only long enough to redirect safe public
   GET/HEAD requests to the same-site canonical URL.

Remaining work is tracked in the Cart [prefix-standardization.md](../../../publishpress-cart/docs/dev/prefix-standardization.md). One family per worker via leftover-slice. Remaining queue: Cart `prefix-leftover-orchestrator`. Keep Cart `docs/dev/pro-plugin-extraction.md` in sync so Pro can follow the same refactor.

## Slice workflow

One logical identifier family per change (one class cluster, or one function file, or one hook group). Do not mix a class rename with an unrelated option migration.

```
- [ ] Confirm the symbol is PHP (shimable) vs persisted (ask first)
- [ ] Rename canonical first-party uses to PPCart_ / ppcart_
- [ ] If it was a shipped StudioCart PHP API, add the shim in the compat package
- [ ] Grep that the old name is gone outside the package (and tests/docs as needed)
- [ ] composer test:unit (plus integration when class_alias or WP options change)
- [ ] Update docs/dev/prefix-standardization.md (check off the slice; update the Pro contract/log in the same file)
```

A Free prefix slice is not done until the Pro sections of
[prefix-standardization.md](../../../docs/dev/prefix-standardization.md)
match. See **Keep the Pro contract in sync** in that file.

### Shippable StudioCart PHP names (shim these)

Add or update entries in `maps/{set}.php` when the canonical symbol moves:

- Classes in `maps/legacy.php`: `Scrt*`, `SC_Bump_*`, bare `NCS_Helper` / `NCS_Stripe` / …
- After #629, canonical keys are `PPCart_*`; each map entry lists `NCS_Cart_*` **and**
  any StudioCart extras (`ScrtOrder`, `NCS_Helper`, …)
- Public functions/hooks documented as `studiocart_*` or long-shipped `sc_*` (later slices)

New shim files stay in the package:

- `functions/` — lazy-loaded wrappers `function sc_foo() { return ppcart_foo(...); }` (sharded under `functions/*.php`, loaded via `functions/index.php`)
- `hooks/` + `hooks.php` shim — bridges so listeners on `studiocart_*` / shipped `sc_*` still run

Load them from `maybe_register_*` on `PPCart_Studiocart_Compatibility_Mode`, not from `publishpress-cart.php` directly.

### Direct rename (no shim outside compat maps)

- Canonical first-party code uses `PPCart_*` / `ppcart_*` / `PPCART_*`
- `NCS_Cart_*` names exist only as Compatibility Mode aliases when the feature is on
- `ncs-cart-*` files, handles, BEM → `ppcart-*` (aligned filenames; see skill)
- Internal-only hooks that never appeared in StudioCart docs
- wp-admin pages and screen hooks: canonical `ppcart` / `ppcart-*` and
  `ppcart_page_*` only, with no redirects, dual registration, recognition, or
  Compatibility Mode bridge
- Public query vars and the download rewrite: canonical `ppcart-*` in core;
  legacy `sc-*` query-var registration and redirects live only in Compatibility
  Mode, which never replaces the canonical pretty-download rewrite
- Test fixtures, unless they assert a public StudioCart name

### Do not rename in the same slice (frozen / migration)

Stop and ask before changing stored or URL-visible strings:

- CPT leftover rows until Data migration
- Shortcode tags until slice 14 (`studiocart_*` / `studiocart-*` / `sc_*` →
  `ppcart_*`; see [prefix-standardization.md](../../../docs/dev/prefix-standardization.md#shortcodes-slice-14))
- Existing `_sc_*` option and post-meta **keys** already in databases
- AJAX request **field names** already in the wild (`sc-nonce`, `sc_related_product`, …)
- Roles/caps, `ncs_tax_rate` table name
- `namespace Studiocart;` integration classes (namespace already scopes them; WP.org may still want `PPCart\` — treat as its own slice)
- Divi module class names (`STOF_*`, `StudioCartOrderForm`) unless doing a dedicated Divi migration

Unprefixed options called out in #629 (`current_schedule_val`) **do** need a unique `ppcart_` key; that is a real option rename with a read-fallback or upgrade copy, not a StudioCart class alias.

## Grep guards (after each slice)

Run from the Free plugin root. Ignore the compatibility package when hunting leftover canonical-old prefixes:

```bash
# First-party code must not keep NCS_Cart / sc_ / ncs_ as the live API
rg 'NCS_Cart_|NCS_CART_|ncs_cart_|ncs-cart-' \
  --glob '!includes/compat/studiocart-compatibility-mode/**' \
  --glob '!vendor/**' --glob '!node_modules/**'

# Runtime constants must be PPCART_* outside Compatibility Mode
rg 'NCS_CART_|SC_STRIPE_CONNECT_|NCS_MINIMUM_|NCS_STYLESHEETPATH' \
  --glob '*.php' \
  --glob '!includes/compat/studiocart-compatibility-mode/**' \
  --glob '!tests/**' --glob '!vendor/**' --glob '!node_modules/**'
# or: composer test:hook-manifest  (includes tests/bin/constant-prefix-policy.php)

# Do not reintroduce the reverted PP_Cart_ family
rg 'PP_Cart_|pp_cart_|pp-cart-|_pp_cart_|PP_CART_' \
  --glob '!vendor/**' --glob '!node_modules/**'

# StudioCart PHP names belong only in the compat package (plus tests that assert them)
rg 'class Scrt|class SC_|function sc_|function studiocart_' \
  --glob '!includes/compat/studiocart-compatibility-mode/**' \
  --glob '!tests/**' --glob '!vendor/**'
```

Expect hits in frozen strings (CPT `'sc_product'`, meta `'_sc_*'`, shortcode tags). Those are not PHP identifier failures. PHP `function` / `class` / `do_action('sc_` in checkout or models **are** failures.

## Tests

- Touching gates or settings: `composer test:unit -- tests/codeception/Unit/Compat/StudiocartCompatibilityModeTest.php`
- Touching `class_alias`: also integration Compat test (IT-163 `@depends` IT-162)
- After a wide rename: `composer test:unit` then the suites in `.cursor/rules/running-tests.mdc` for the area

New cases: module `legacy-compat`, `prefix-compat-class`, `prefix-constants`,
`prefix-hooks`, `prefix-runtime-globals`, or `prefix-admin-screen-slugs`.
Admin-screen slug cases use `features: []`; Compatibility Mode cases use
`features: [ppcart-compatibility-mode]`.

Public query/rewrite coverage uses module `prefix-public-query-vars`
(UT-198 / IT-267). Core registers `ppcart-preview`, `ppcart-order`,
`ppcart-plan`, `ppcart-manage`, `ppcart-download`, and `%ppcart-download%`.
When enabled, Compatibility Mode maps all five legacy keys in one temporary
302 redirect, preserves unrelated arguments and scalar `0`, prefers existing
canonical values, ignores arrays, and refuses POST, admin, AJAX, and REST
redirects. Canonical pretty download paths resolve directly in all modes;
stale resolved `sc-download` vars may be canonicalized defensively.

AJAX/nonce coverage uses module `prefix-ajax-actions` (UT-199 / IT-268).
Core registers `wp_ajax_ppcart_*` and generates `ppcart_*` nonce actions.
Compatibility Mode dual-registers shipped `sc_*` / `ncs_*` POST actions with
matching auth/nopriv exposure and accepts mapped legacy nonce strings.

## Settings / feature IDs during the rename

Option `_ppcart_studiocart_compatibility_mode` and feature id `studiocart-compatibility-mode`.
Leftover `_sc_studiocart_compatibility_mode` is dual-read and copied on first enable check.
Keep excluding the leftover key from option storage maps so the canonical row stays a real `wp_options` key.

Runtime plugin identity is `ppcart`; the text domain and public plugin slug stay
`publishpress-cart`. The Compatibility section registers only on the `ppcart`
Settings API page. wp-admin menu and screen slugs use only the canonical
`ppcart` family after the hard cutover.

**wp-config:** `PPCART_STUDIOCART_COMPATIBILITY_MODE` overrides the checkbox when
defined. Test with `@runInSeparateProcess`.

The Compatibility Mode class is `PPCart_Studiocart_Compatibility_Mode`. The unreleased `NCS_Cart_Studiocart_Compatibility_Mode` name has no alias.

## Review narrative

When summarizing a #629 PR:

- Canonical identifiers are `PPCart_` / `ppcart_`.
- Remaining StudioCart/`sc`/`ncs` PHP names are the **StudioCart Compatibility Mode** feature, isolated under `includes/compat/studiocart-compatibility-mode/`, default off.
- Persisted StudioCart data shapes are unchanged unless the PR is explicitly a migration.
