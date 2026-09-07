---
id: ppcart-compatibility-mode
plugin: publishpress-cart-compat
name: StudioCart Compatibility Mode
status: active
description: Optional layer that keeps StudioCart legacy options, functions, hooks, class names, PHP constants, runtime globals, and public URL redirects working for third-party plugins and apps after the fork.
edition: free
tags: [compatibility, studiocart, developer]
source_areas:
  - includes/compat/studiocart-compatibility-mode/
  - includes/compat/studiocart-compatibility-mode/functions/
  - includes/compat/studiocart-compatibility-mode/hooks/
---

# StudioCart Compatibility Mode

## Description

A user-toggleable compatibility layer for sites that still run third-party
plugins, themes, or apps written against the original StudioCart public
contract — option keys, functions, hooks, class names, PHP constants, and
runtime globals, plus redirects from legacy public URLs.

PublishPress Cart does not run alongside StudioCart. This is not a live
integration with another product. When the mode is on, this companion exposes
the legacy StudioCart surface so existing extensions keep working while Cart
core uses the canonical `PPCart_*` / `ppcart_*` API. When it is off, only
the canonical Cart API is available.

Stored leftover rows (CPT slugs, leftover shortcode tags in post/email HTML,
`_sc_*` post meta, `_sc_*` / `sc_*` option keys and transient option rows) stay
in the database until an explicit Data migration. Compatibility Mode and boot
must not copy, rename, or delete those rows. Shortcode **tags** dual-register
here when the mode is on; stored leftover shortcode **content** rewrites on the
Data migration card (`includes/compat/shortcode-tag-migration/`). Option key
renames are the **Option keys** wizard step
(`includes/compat/option-key-migration/`, AJAX `ppcart_migrate_option_keys`).
First-party PHP already saves and
reads canonical `_ppcart_*` post-meta keys; leftover `_sc_*` **names** are
Compatibility Mode metadata bridges, not a first-party dual-read. This feature
owns PHP-level shims:
class aliases, function wrappers, hook bridges, option bridges,
constant aliases, runtime-global aliases, leftover shortcode tag registration,
and public URL canonicalization. Leftover form `name=` / POST / GET keys copy
onto canonical request fields while the mode is on (`request-fields.php`).
Leftover Stripe object metadata keys (`sc_product_id`, `sc_order_id`, and
siblings) are read through `ppcart_stripe_metadata()` only while the mode is
on (`stripe-metadata.php`). First-party writes stay `ppcart_*`. Leftover FSE
template slug `single-sc_product` and leftover taxonomy rewrite
(`sc_product_cat` / `sc_product_tag`) register only while the mode is on
(`cpt-taxonomy.php`). First-party CPT/taxonomy **source** is canonical
`ppcart_*`; live helpers stay row-state-based until Data migration.

Canonical Cart code must not depend on this mode. Gate runtime with
`ppcart_cart_supports( 'ppcart-compatibility-mode' )`. The prior feature id is
accepted only by this package while Compatibility Mode is enabled.

Merchants toggle it under **Cart → StudioCart** (`admin.php?page=ppcart-studiocart`, option
`_ppcart_compatibility_mode`, default off). Prior toggle keys are read only by
this package and remain in place. Class aliases from
`includes/compat/studiocart-compatibility-mode/aliases.php` register only
when the mode is on. `NCS_CART_LEGACY_COMPAT` / `ncs_cart_legacy_compat_enabled()`
can still suppress individual shims in CI.

## Screen

Cart → StudioCart (`page=ppcart-studiocart`) also has a **Migration backups**
checkbox (`ppcartcomp_drop_migration_backups_on_uninstall`, default off). When
it is on, deleting this plugin drops only the three reverse-map backup tables
(`ppcart_cpt_migration_backup`, `ppcart_meta_key_migration_backup`,
`ppcart_shortcode_tag_migration_backup`). Cart store tables, leftover
StudioCart rows, Compatibility Mode, and wizard migration-state options stay.
That is uninstall cleanup for Data migration backups, not a Compatibility Mode
shim. Restore from those backups is unavailable after they are dropped.

## Hook bridges

First-party Cart fires `ppcart_*` / `_ppcart_*` hooks and plugin-identity
`ppcart-*` hooks only. Legacy `sc_*`, `studiocart_*`, `_sc_*`, `ncs_*`,
`nsc_*`, and shipped `ncs-cart-*` **extension** hook names live only in this
package (`hooks/` + `hooks.php` shim, `maps/hooks.php`):

- **Compatibility Mode on** — dual-fire and reverse-fire so third-party
  `add_action( 'sc_*' )` listeners still run.
- **Always on** — cron drain forwards legacy scheduled hook names stored in
  `wp_cron` to canonical handlers.

The shipped currency filters map explicitly to `ppcart_currencies`,
`ppcart_currency_symbols`, and `ppcart_zero_decimal_currency`. Other
`ncs-cart-*` hooks generated from the old runtime plugin identity bridge
generically to `ppcart-*` in both directions.

Frozen WordPress-shaped hooks (AJAX actions and CPT `save_post_sc_*`) are not
Cart extension hooks and are not bridged. Admin screen IDs made a hard cutover
to `ppcart_page_*`; old screen IDs are neither recognized nor bridged.

## Public URL redirects

Core registers only the canonical public query vars `ppcart-preview`,
`ppcart-order`, `ppcart-plan`, `ppcart-manage`, and `ppcart-download`. Secure
downloads use the canonical `%ppcart-download%` rewrite tag.

When Compatibility Mode is on, the package also recognizes the equivalent
legacy map:

- `sc-preview` → `ppcart-preview`
- `sc-order` → `ppcart-order`
- `sc-plan` → `ppcart-plan`
- `sc-manage` → `ppcart-manage`
- `sc-download` → `ppcart-download`

A safe public GET/HEAD request is redirected once to a same-site URL containing
the canonical keys. The temporary 302 status avoids permanently caching order,
plan, or download identifiers and reflects that legacy recognition can be
disabled. Unrelated query arguments and scalar values, including `0`, are
preserved; an existing canonical value wins over its legacy counterpart, and
array-shaped legacy values are ignored. POST, admin, AJAX, REST, and
canonical-only requests are never redirected.

The canonical pretty-download path and `%ppcart-download%` rewrite remain
active and unchanged in every mode, so existing pretty download URLs resolve
directly. Compatibility Mode does not add or replace a download rewrite. It
can defensively canonicalize a stale resolved `sc-download` query var left by
pre-upgrade rewrite state.

### Extension authors

Update plugins to `ppcart_*` hooks and test with Compatibility Mode off.
See [Hook prefix policy](../PREFIX_STANDARDIZATION.md#third-party-and-pro-extension-migration).

**PublishPress Cart Pro — today vs after dual-define:**

- **Today:** Pro still defines leftover `NCS_CART_*` contract constants and
  still listens on leftover hook names. Free sites with Pro installed need
  Compatibility Mode **on** for Pro **detection**, vendor assets, and Pro
  hook listeners. Free inbound constant copy stays gated (UT-194).
- **After Pro dual-defines** `PPCART_*` before requiring Free (slice 18 Pro
  PR; intended follow-up of `docs(prefix): record Pro-contract constants
  dual-define (#629)`): Pro **detection** works with Compatibility Mode
  **off**. Pro **modules** still need the later Pro hook slice. Mode on
  remains a third-party StudioCart check, not Pro’s own contract.

Playbook: [prefix-standardization.md](../dev/prefix-standardization.md#publishpress-cart-pro).

## Constant aliases

Canonical runtime constants are `PPCART_*`. Mapped `NCS_CART_*` /
`SC_STRIPE_CONNECT_*` names are copied inbound (legacy wp-config/Pro →
canonical) and aliased outbound (canonical → legacy) only when this mode
is on. `NCS_CART_LEGACY_COMPAT` suppresses outbound aliases. Test-only
names such as `PPCART_PLUGIN_ROOT` have no production aliases.

**Today:** Pro still defines `NCS_CART_LOADED_BY_PRO` and related Pro
constants. Until Pro dual-defines `PPCART_*` before requiring Free, Pro
sites need this mode on or Pro detection and vendor assets will not follow
the Free-side contract. Do not ungate inbound copy.

**After Pro dual-defines:** detection works with this mode **off**. Pro
modules still need the later Pro hook slice.

See [prefix-standardization.md](../dev/prefix-standardization.md#publishpress-cart-pro).

## Runtime global aliases

Canonical runtime globals are `$ppcart_stripe`, `$ppcart_currency`,
`$ppcart_currency_symbol`, `$ppcart_debug_logger`, `$ppcart_product`,
`$ppcart_checkout_request_rendered`, `$ppcart_checkout_block_arrangement`,
`$ppcart_public`, `$ppcart_files`, `$ppcart_product_fields`, and
`$ppcart_is_admin_screen`.
When this mode is on, the shipped StudioCart names (`$sc_stripe`,
`$sc_currency`, `$sc_currency_symbol`, `$sc_debug_logger`, `$scp`, leftover
`$GLOBALS['sc_checkout_*']`, leftover `$studiocart`, leftover `$scFiles`,
leftover `$sc_product_fields`, leftover `$sc_is_studiocart_admin_screen`) are
references to those values so third-party reads and writes stay in sync.
`NCS_CART_LEGACY_COMPAT` suppresses the aliases.
