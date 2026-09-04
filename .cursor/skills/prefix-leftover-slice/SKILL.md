---
name: prefix-leftover-slice
description: >-
  Companion half of leftover prefix slices 32–42 (issue #629). Adds
  Compatibility Mode maps, leftover aliases, and catalog tests in
  publishpress-cart-compat after Cart first-party is renamed. Use when the
  user asks to run a leftover prefix slice, prefix-leftovers-remaining.md,
  sc_make_, sc_view_log, sc_order_pending, sc-csv-export, or Compatibility
  Mode bridges for a leftover family.
---

# Leftover prefix slice (Compat)

Canonical playbook: sibling
`publishpress-cart/.cursor/skills/prefix-leftover-slice/SKILL.md`.
Read that file and [slices.md](../../../publishpress-cart/.cursor/skills/prefix-leftover-slice/slices.md)
when the Cart checkout is available.

This repo owns leftover **names** (maps, wrappers, gated redirects) and
Compat-on tests. Cart owns first-party canonical code, the leftover inventory,
and the Pro contract.

## Unbreakable: leftover read/write lives here

Cart first-party never dual-reads, dual-writes, or falls back to leftover
names. Any secondary or compatibility read/write of leftover keys, values,
GET/POST, hooks, or URLs belongs **only** in this plugin (Compatibility Mode
package, or Data migration when the merchant opts in). Do not add leftover
fallbacks to `publishpress-cart`.

## This repo

1. Do not rename leftover names in maps until Cart first-party is canonical.
2. Add map/wrapper/redirect rows so leftover still works **only while Compatibility Mode is on**.
3. No silent row copy/rename/delete (AGENTS.md hard rule 8).
4. Catalog IDs are shared with Cart — next unused `UT-` / `IT-` across both repos.
5. One family per session. Stop for review. Do not commit unless asked.

## Package

`includes/compat/studiocart-compatibility-mode/` — see
[studiocart-compatibility-mode](../studiocart-compatibility-mode/SKILL.md).
