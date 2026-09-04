# Architecture & Design — Remediation Guide

**Current score:** 3.0/5.0
**Targets:** Needs-work ≥2.5 · Healthy ≥4.0

Primary issue: **two files exceed 700 lines** and four more sit at 501–640. Hotspot: `includes/compat/studiocart-compatibility-mode/functions.php` (~2,279 lines, 324 thin `ncs_*` / `sc_*` wrappers with no PHPDoc).

**Total LOC (~3,072 class-scoped; ~13,860 physical PHP) is not a remediation target.** The problem is concentration in Compatibility Mode and a few migrator/admin classes, not overall size.

---

## Actionable items

| Priority | Action | Effort | Expected score impact | Evidence / where | Done-when |
|----------|--------|--------|----------------------|------------------|-----------|
| P0 | Split `functions.php` (2,279 lines) into `ncs_*` vs `sc_*` wrapper files (break is around `:1106`) and require both from the package index | M | Arch. ~3.3 | 324 wrappers, 0 documented; largest scoped file | No compatibility-mode PHP file over 700 lines; wrappers still load only when Compatibility Mode is on |
| P0 | Split `hooks.php` (859 lines) into map-config loaders vs bridge attach/fire runtime | M | Arch. ~3.4 | `includes/compat/studiocart-compatibility-mode/hooks.php`; `ncs_cart_get_legacy_hook_map_config()` at `:16`, `ppcart_hook_bridge_maybe_attach()` at `:600` | `hooks.php` under 400 lines; map loading lives next to `maps/hooks.php` |
| P1 | Shrink `class-ppcartcomp-studiocart-compatibility-mode.php` (640 lines, LCOM 30, 30 methods) by moving registration flags/helpers into focused collaborators | M | Arch. ~3.5 | PHPMetrics CCN 58, 538 class LOC | Class file under 400 lines; LCOM <15 |
| P1 | Split `class-ppcartcomp-data-migration-wizard.php` (564 lines, CCN 111) into status payload vs enqueue/bootstrap | M | Arch. ~3.6 | 23 methods, WMC 133; `enqueue_assets()` `:66` | Wizard file under 400 lines; class CCN <50 |
| P1 | Split `options.php` (556 lines) the same way as hooks: config loader vs leftover option SQL | M | Arch. ~3.5 | `ncs_cart_get_legacy_option_map_config()` `:11`; `$wpdb` writes `:264` | Options bridge file under 350 lines |
| P1 | Move restore SQL out of `class-ppcartcomp-shortcode-tag-migrator.php` (501 lines) / keep chunk runners under 400 lines | M | Arch. ~3.4 | PHPMetrics 339 class LOC, CCN 50 | Shortcode migrator file under 400 lines |
| P2 | Extract CPT slug admin Blob (`PPCartComp_Cpt_Slug_Migration_Admin`, 14 public methods, LCOM 17) into notice vs AJAX vs redirect collaborators | M | Arch. ~3.6 | PHPMetrics Blob violation; `class-ppcartcomp-cpt-slug-migration-admin.php` (335 lines) | No Blob violation on this class |
| P2 | Keep new PHP files under the existing 500-line convention; add a checklist item on PRs that touch `includes/compat/` | S | Holds Arch. | `AGENTS.md` convention | No new first-party PHP file over 500 lines |

## Milestone paths

| Milestone | Work | Estimated score |
|-----------|------|-----------------|
| **Hold NEEDS-WORK** | P0 splits on `functions.php` and `hooks.php` | ~3.3 Arch. |
| **Solid NEEDS-WORK** | + wizard / compatibility-mode / options splits | ~3.6 Arch. |
| **HEALTHY** | Blob extraction + no file over 500 lines | ≥4.0 Arch. |

## References

- [code-quality-audit-report.md](../code-quality-audit-report.md) §1
- [code-maintainability.md](code-maintainability.md)
- [classes.md](classes.md)
- [avg-cyclomatic-complexity.md](avg-cyclomatic-complexity.md)
