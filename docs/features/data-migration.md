---
id: data-migration
plugin: publishpress-cart-compat
name: StudioCart Data migration
status: active
description: Merchant-run wizard that rewrites leftover StudioCart CPT slugs, meta keys, shortcode content, option keys, and custom tables onto canonical PublishPress Cart names.
edition: free
tags: [migration, studiocart, data]
source_areas:
  - includes/admin/class-ppcartcomp-data-migration-wizard.php
  - includes/compat/cpt-slug-migration/
  - includes/compat/meta-key-migration/
  - includes/compat/shortcode-tag-migration/
  - includes/compat/option-key-migration/
  - includes/compat/custom-table-migration/
  - assets/admin/migration-wizard/
---

# StudioCart Data migration

## Description

An explicit, merchant-run rewrite of leftover StudioCart **storage** onto
canonical PublishPress Cart names. It is not Compatibility Mode and not a
plugin upgrade migration.

Compatibility Mode keeps leftover PHP names working while rows stay as they
are. Data migration is the only path that copies, renames, or deletes leftover
CPT slugs, `_sc_*` post/user meta, leftover shortcode **content**, leftover
option/transient keys, or leftover `ncs_*` custom tables. Activate, boot, and
the Compatibility Mode toggle must not mutate those rows.

Without this companion, Cart’s canonical-only live helpers make unmigrated CPT
and table rows look missing. Leftover stores should run this wizard (or keep
the companion active) before expecting canonical-only Cart to see old data.

## Screen

Cart → StudioCart (`admin.php?page=ppcart-studiocart`) opens a landing page and
a step wizard (`PPCartComp_Data_Migration_Wizard`, React build under
`assets/admin/migration-wizard/`). Package AJAX stays on each migration admin
class. Optional **Migration backups** (`ppcartcomp_drop_migration_backups_on_uninstall`)
drops only the three reverse-map backup tables on uninstall.

## Steps

| Step | Package | What runs |
|------|---------|-----------|
| CPT slugs | `includes/compat/cpt-slug-migration/` | Rename leftover CPT/taxonomy/capability slugs; backup + restore |
| Roles | (same CPT package, step 2) | Rename leftover roles |
| Meta keys | `includes/compat/meta-key-migration/` | Rewrite leftover `_sc_*` post/user meta names |
| Shortcodes | `includes/compat/shortcode-tag-migration/` | Rewrite leftover shortcode tags in post and email HTML |
| Option keys | `includes/compat/option-key-migration/` | Copy leftover option/transient rows onto canonical keys |
| Custom tables | `includes/compat/custom-table-migration/` | `RENAME TABLE` leftover `ncs_*` tables; alter order-item meta column |

Option-key **read-through** (leftover rows in place) is Compatibility Mode /
storage bridges, not this feature. The option-key step is the explicit copy.

## Related

- [StudioCart Compatibility Mode](studiocart-compatibility-mode.md) — PHP shims; does not rewrite stored rows
- [Architecture](../dev/architecture.md)
