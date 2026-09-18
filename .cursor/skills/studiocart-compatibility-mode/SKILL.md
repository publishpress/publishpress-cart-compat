---
name: studiocart-compatibility-mode
description: >-
  Guides StudioCart Compatibility Mode — the merchant-toggleable PHP shim
  feature that keeps StudioCart legacy classes, functions, hooks, and
  constants available after Cart prefixes change. Use when working on
  WordPress.org prefix rename issue 629 (PPCart_ / ppcart_ / PPCART_),
  includes/compat/studiocart-compatibility-mode, class_alias / function /
  hook / constant bridges, sc_cart_supports studiocart-compatibility-mode,
  or deciding where StudioCart, NCS, sc, or ncs identifiers belong.
---

# StudioCart Compatibility Mode

Sellable, merchant-toggleable **compatibility layer**. Not a live StudioCart
integration. StudioCart will not run alongside Cart.

Canonical Cart is moving to unique prefixes for WordPress.org
([issue #629](https://github.com/publishpress/publishpress-cart/issues/629)).
This feature is the only home for leftover StudioCart PHP names so third-party
plugins and apps keep working.

**Issue #629 workflow:** [references/issue-629.md](references/issue-629.md)

Feature record: [docs/features/ppcart-compatibility-mode.md](../../../docs/features/ppcart-compatibility-mode.md)

## Decision (do not reopen)

| Rule | Detail |
|------|--------|
| It is a **feature** | Catalog id `ppcart-compatibility-mode`, edition **free**, Cart → StudioCart (`page=ppcart-studiocart`) |
| Default **off** | New stores do not load StudioCart PHP names |
| Package, not a loader | `includes/compat/studiocart-compatibility-mode/` loaded from `publishpress-cart.php` |
| **Do not** restore `includes/compat/legacy.php` | IT-161 asserts that file is gone |
| **Do not** put this in `includes/integrations/` | That folder is for live third-party products (Kit, Woo, …) |
| **Do not** put this in Pro `modules/extensions/` | Not a paid add-on |
| Canonical Cart never calls shims | Core uses `PPCart_*` / `ppcart_*` only (after #629) |
| Leave Pro `includes/compat/legacy.php` alone | Affiliate class aliases in **cart-pro**, unrelated |
| Runtime plugin identity | `ppcart`; text domain/public slug stay `publishpress-cart`; wp-admin slugs use the canonical `ppcart` family only |
| **No silent data migration** | Leftover option keys, post meta, CPT slugs, shortcode **content**, custom tables, roles, and related stored rows must not be copied, renamed, or deleted on plugin activate, boot, Compatibility Mode toggle, or read-through. Only merchant Data migration (explicit UI click) may mutate those rows. PHP shims and read-through bridges leave leftover storage in place. |

`docs/dev/prefix-standardization.md` is the official #629 map, leftover
tracker, and Pro contract (`PPCart_` / `ppcart_` / `ppcart-`). An earlier
pass made `NCS_Cart_*` / `sc_*` canonical; that is superseded. Do not revert
#629 work back to `NCS_Cart_*`.

## Package

```
includes/compat/studiocart-compatibility-mode/
  index.php                                          # require + init()
  class-ppcart-studiocart-compatibility-mode.php     # settings, gates, maybe_register_*
  aliases.php                                        # helpers + glob loader
  constants.php                                      # inbound/outbound PPCART_* bridges
  globals.php                                        # $ppcart_* <=> $sc_* / $scp aliases
  class-ppcart-studiocart-public-query-compatibility.php # gated legacy query-var redirects
  maps/{set}.php                                     # canonical => [ legacy, ... ]
  maps/constants.php                                 # not a class map; skipped by the glob
  maps/hooks.php                                     # not a class map; skipped by the glob
  options.php                                        # leftover row read-through + leftover _sc_* sensitivity
  maps/options.php                                   # not a class map; skipped by the glob
  maps/cpt-taxonomy.php                              # leftover FSE/rewrite; skipped by the glob
  functions/                                         # lazy-loaded sc_* / ncs_* wrappers (see index.php)
  hooks/                                             # hook bridge runtime (loaded via hooks.php shim)
  hooks.php                                          # thin shim → hooks/index.php
  cpt-taxonomy.php                                   # leftover FSE template + rewrite when on
  css/ppcart-compat-admin.css                        # leftover CPT/HTML selectors
  css/ppcart-compat-public.css                       # leftover storefront selectors
```

Bootstrap: `publishpress-cart.php` requires `index.php` only, **before**
defining canonical `PPCART_*` defaults, then calls `maybe_register_constants()`
again after those defaults. Unit bootstrap uses the same path.

Each class `maps/*.php` file returns `array<string, array<int, string>>`.
The class-alias glob skips `index.php`, `options.php`, `hooks.php`,
`constants.php`, `shortcodes.php`, `ajax.php`, `nonces.php`,
`request-fields.php`, `stripe-metadata.php`, `mollie-metadata.php`,
`razorpay-notes.php`, `square-catalog.php`, `cpt-taxonomy.php`,
`integration-repeater-fields.php`, `integration-service-ids.php`,
`invoice-format-values.php`, `tax-rate-inner-keys.php`, and
`theme-template-dirs.php`.

### Gates

Three layers (outer to inner):

1. **wp-config** (highest): `PPCART_STUDIOCART_COMPATIBILITY_MODE` — if defined,
   `(bool)` value overrides the Settings checkbox.
2. **Merchant**: option `_ppcart_compatibility_mode` (`1`/`0`), default `0`. Historical `_ppcart_studiocart_compatibility_mode` and `_sc_studiocart_compatibility_mode` rows are read-only Compat inputs; neither is copied, renamed, or deleted on enable checks.
3. **Developer/CI**: `ncs_cart_legacy_compat_enabled()` / `NCS_CART_LEGACY_COMPAT` /
   filter `ncs_cart_legacy_compat_enabled`. Suppresses individual `class_alias`
   calls and outbound constant aliases after the feature is on.

When `PPCART_STUDIOCART_COMPATIBILITY_MODE` is defined, the Settings checkbox is
**disabled** and shows a note.

`supports_feature` is on `ppcart_supports_feature` at **priority 20** so
Pro’s blanket `supports_feature() { return true; }` cannot force this on.

Register shims from `maybe_register_*` methods, same pattern as
`maybe_register_aliases()` (early `sc_before_load` plus late hooks for
classes loaded after bootstrap).

### What this feature owns vs what stays in core

| Owns (PHP shims, only when on) | Stays until an explicit Data migration (merchant UI) |
|--------------------------------|-----------------------------------------------------|
| Class names (`NCS_Cart_*`, `ScrtOrder`, `SC_*`, bare `NCS_*`, …) | CPT slugs (`sc_product`, `sc_order`, …) |
| Function names (`studiocart_*`, shipped `sc_*`) | Option keys (`_sc_*`, `sc_*`, `ncs_*`, transients) — read-through only; wizard **Option keys** step via `option-key-migration/` |
| Shortcode **tags** (`studiocart-form`, `sc_product`, …) dual-register when on | Stored leftover shortcode **content** (posts, email HTML) — `shortcode-tag-migration/` |
| Hook names third-party PHP listens to | Post meta keys — `meta-key-migration/` |
| PHP constants (`NCS_CART_*`, `SC_STRIPE_CONNECT_*`) | Custom tables, roles/caps, `{prefix}ncs_tax_rate`, on-disk log directory names |
| Legacy public `sc-*` query vars and redirects | Canonical `ppcart-*` query vars and rewrite |
| Legacy AJAX `sc_*` / `ncs_*` POST actions and nonce strings | Canonical `wp_ajax_ppcart_*` routes and `ppcart_*` nonce actions |

**Hard rule:** never copy, rename, or delete leftover DB rows from boot,
Compatibility Mode registration, or option/meta read-through. Call migration
helpers only from the Data migration UI (or tests that exercise that path).

Persisted strings cannot be “aliased” with `class_alias`. If #629 or WP.org
forces a stored-key rename, that is a **migration**, not a silent shim. Stop
and confirm with the user.

The Compatibility Mode toggle keeps historical rows as read-only Compat inputs
(UT-201 / IT-275); no normal request migrates its setting.

Admin menu/page slugs are an approved exception: slice 9 made a hard cutover
to `ppcart` / `ppcart-*` and `ppcart_page_*`, with no redirects, dual
registration, legacy recognition, or Compatibility Mode bridge.

Public URLs use a different approved strategy. Canonical core registers only
`ppcart-preview`, `ppcart-order`, `ppcart-plan`, `ppcart-manage`, and
`ppcart-download` plus `%ppcart-download%`. While Compatibility Mode is on,
this package recognizes the equivalent explicit `sc-*` query vars and issues
one same-site temporary 302 redirect to the canonical query keys. Existing
canonical values take precedence, unrelated arguments and scalar values
(including `0`) are preserved, and arrays are ignored. The redirect skips
admin, AJAX, REST, POST, and canonical-only requests. The canonical pretty
download rewrite is unchanged in all modes; stale resolved `sc-download` vars
may be canonicalized defensively without registering a legacy rewrite.

## Adding a shim

1. Rename the **canonical** symbol to `PPCart_*` / `ppcart_*` in first-party code.
2. Add the StudioCart name only inside this package (alias map, function wrapper,
   or hook bridge).
3. Load that file from `maybe_register_*` when `is_enabled()`.
4. Do not `function_exists` the canonical definition to “share” a short prefix
   (WP.org forbids that as a uniqueness strategy).
5. Tests: unit for the gate; integration for `class_alias` (cannot un-alias
   in-process — `@depends` like IT-162→IT-163). Catalog module `legacy-compat`,
   feature `studiocart-compatibility-mode`. See [test-authoring](../test-authoring/SKILL.md).

## Tests for every renamed symbol (#629)

Every rename of a **class, trait, function, global, option, or hook** must ship
an **integration** catalog case (`docs/testing/cases/integration/IT-*.md`) plus a
Codeception method.

**Classes (this pass):** with Compatibility Mode **on** and aliases registered:

- `class_exists( 'PPCart_X', false )`
- `class_exists( 'NCS_Cart_X', false )` (and StudioCart names where mapped)
- `new NCS_Cart_X()` is an instance of `PPCart_X`

With mode **off** (isolated process or before `maybe_register_aliases`, like
IT-162): legacy names are **not** `class_exists`. After #629, `NCS_Cart_*` is a
Compatibility Mode surface, not a canonical class name.

**Functions / options / hooks / globals:** same on/off split in later slices.

Pre-assign IT IDs for parallel agents. Prefer one
`tests/codeception/Integration/Prefix/<Set>Test.php` per class set.

**Filenames:** align `class-ppcart-{kebab-suffix}.php` with the class suffix;
fix historical basename drift (e.g. `Contacts_Page` → `class-ppcart-contacts-page.php`).

Settings UI: section callback intro uses `ppcart-settings__card-desc` (24px
horizontal padding). Field `description` is the checkbox help, not the group intro.

## WordPress.org scan

The plugin checker still **sees** StudioCart/`sc`/`ncs` names inside this
package. The intended review split:

- Everything **outside** the package uses `PPCart_` / `ppcart_`.
- Leftover short/StudioCart PHP identifiers live **only** here, behind the
  merchant toggle, as Compatibility Mode.

Do not sprinkle leftover `sc_*` functions through checkout, admin, or models
to “keep BC”.

## Tests already shipped

| IDs | Coverage |
|-----|----------|
| UT-179–183, UT-184–186 | Merchant gate, wp-config override, supports filter, settings field |
| UT-195–196, IT-262 | Canonical compat class and canonical-only Settings API `ppcart` page |
| IT-263–IT-264 | Canonical `$ppcart_*` runtime globals with Compatibility Mode `$sc_*` / `$scp` aliases |
| UT-189, IT-259, IT-265 | Canonical hooks plus Compatibility Mode bridges, including shipped `ncs-cart-*` plugin-identity hooks |
| UT-197, IT-266 | Canonical-only wp-admin page slugs and hook suffixes after the hard cutover |
| UT-198, IT-267 | Canonical public query vars/rewrite plus gated legacy URL redirects |
| UT-199, IT-268 | Canonical AJAX actions/nonces plus gated legacy POST routes and nonce strings |
| UT-204, IT-282 | Canonical shortcode tags plus gated leftover `add_shortcode()` dual-register |
| UT-205, IT-283 | Canonical emitters/detectors; leftover email replace only while the mode is on |
| IT-161–165 | Package path (no `legacy.php`), aliases off/on, supports on a real site, settings registration |
| UT-022–031 | Developer/CI helpers in `aliases.php` |
| UT-190–194, IT-260–IT-261 | Constant map, inbound/outbound gating, bootstrap aliases off/on |

## Related

- [issue-629.md](references/issue-629.md) — prefix rename slices
- [prefix-leftover-slice](../prefix-leftover-slice/SKILL.md) — leftover families 43+
- Cart `prefix-leftover-orchestrator` — remaining queue 58–64
- [docs/dev/prefix-standardization.md](../../../docs/dev/prefix-standardization.md) — official #629 map, leftover tracker, Pro contract
- [plugin-features](../plugin-features/SKILL.md) — catalog
