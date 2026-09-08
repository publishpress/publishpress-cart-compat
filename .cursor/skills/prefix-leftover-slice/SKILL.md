---
name: prefix-leftover-slice
description: >-
  Companion half of leftover prefix slices 43+ (issue #629). Adds
  Compatibility Mode maps, leftover aliases, and catalog tests in
  publishpress-cart-compat after Cart first-party is renamed. Use when the
  user asks to run a leftover prefix slice, prefix-standardization.md,
  sc-webhook, sc-api, before_sc_order_refund, or Compatibility
  Mode bridges for a leftover family, including leftover has_shortcode
  detectors.
---

# Leftover prefix slice (Compat)

Canonical playbook: sibling
`publishpress-cart/.cursor/skills/prefix-leftover-slice/SKILL.md`.
Read that file and [slices.md](../../../publishpress-cart/.cursor/skills/prefix-leftover-slice/slices.md)
when the Cart checkout is available.

This repo owns leftover **names** (maps, wrappers, gated redirects, leftover
shortcode/asset detectors) and Compat-on tests. Cart owns first-party
canonical code, the leftover inventory, and the Pro contract.

## Unbreakable: leftover read/write lives here

Cart first-party never dual-reads, dual-writes, falls back to, *or detects*
leftover names (`has_shortcode` leftover tags, asset detectors). Any leftover
or backward-compat path belongs **only** in this plugin. Do not add leftover
fallbacks or leftover-tag detectors to `publishpress-cart`.

Silent migrate is always denied. Any migration should be on the compat plugin.
Dual-register, dual-reading, only in the compat plugin also. Free plugin is a
complete new and clean plugin. The only exception is keep the studiocart
conflict checker at the beginning of the Cart plugin.

## This repo

1. Do not rename leftover names in maps until Cart first-party is canonical.
2. Add map/wrapper/redirect rows so leftover still works **only while Compatibility Mode is on**.
3. No silent row copy/rename/delete (AGENTS.md hard rule 8).
4. Catalog IDs are shared with Cart — next unused `UT-` / `IT-` across both repos.
5. After Cart `composer test:all:mocked`, always run `composer test:all` in this
   repo (not only when Compat files changed).
6. Commit after both suites are green **and** the Cart leftover-slice capable
   reviewer pass (`refactor(prefix):` then `docs(prefix):` on Cart; this repo’s
   slice commit when Compat changed). Minor fixes are their own `fix(compat):`
   commits. No PR unless asked.
7. Do not start the next family. Orchestrator:
   `publishpress-cart/.cursor/skills/prefix-leftover-orchestrator/`.

## Package

`includes/compat/studiocart-compatibility-mode/` — see
[studiocart-compatibility-mode](../studiocart-compatibility-mode/SKILL.md).
