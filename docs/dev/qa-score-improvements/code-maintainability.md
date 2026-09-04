# Code Maintainability — Remediation Guide

**Current score:** 3.5/5.0
**Targets:** Needs-work ≥2.5 · Healthy ≥4.0

Primary issue: **57 `global $` usages and 178 `$wpdb->` references** in a small companion, almost all in Data migration SQL. Hotspot: `includes/compat/shortcode-tag-migration/class-ppcartcomp-shortcode-tag-migrator.php` (12 `global $`, 31 `$wpdb->`).

**Total LOC (~3,072 class-scoped) is not a remediation target.** Function length is already healthy (0 functions over 100 lines). Maintainability moves by containing SQL and flattening branchy admin renderers.

---

## Actionable items

| Priority | Action | Effort | Expected score impact | Evidence / where | Done-when |
|----------|--------|--------|----------------------|------------------|-----------|
| P0 | Introduce a thin `$wpdb` accessor used by shortcode, meta-key, and CPT migrators so methods stop repeating `global $wpdb` | M | Maint. ~3.6 | 12 globals in shortcode migrator; 8 in meta-key migrator; 8 in CPT migrator | Those three classes drop to ≤2 `global $` each |
| P0 | Split `ppcart_render_custom_table_migration_step()` (heuristic CCN 39, 63 lines) into complete vs pending vs error markup helpers | S | Maint. ~3.6 | `includes/compat/custom-table-migration/admin.php:38` | Renderer under 30 lines; no single helper CCN over 15 |
| P1 | Extract leftover-option SQL (`ppcart_write_option_to_db()` `:264`, `ppcart_copy_leftover_option_row()` `:89`) from Compatibility Mode `options.php` into the option-key-migration package | M | Maint. ~3.6 | 15 `$wpdb->` in `options.php`; 9 in `option-key-migration/index.php` | Compatibility Mode `options.php` has no `$wpdb` |
| P1 | Flatten `ncs_cart_canonical_to_legacy_hook_names()` (heuristic CCN 23, 60 lines) by precomputing the reverse map once | S | Maint. ~3.6 | `includes/compat/studiocart-compatibility-mode/hooks.php:222` | Function under 30 lines; reverse map built in the config loader |
| P1 | Reduce branching in `restore_backup_row()` (CCN 17, 64 lines) and `run_step1_chunk_body()` (CCN 14, 52 lines) | M | Maint. ~3.7 | `trait-ppcartcomp-cpt-slug-migrator-restore.php:127`; `class-ppcartcomp-cpt-slug-migrator.php:95` | Each method under 40 lines / CCN 10 |
| P1 | Keep `publishpress-cart-compat.php:36` `global $wp_version` as the only bootstrap global; do not add more | S | Holds Maint. | 1 of 57 globals is the version gate | Bootstrap stays at one `global $` |
| P2 | Document why migration SQL uses `$wpdb` directly (chunked leftover rewrites, backup tables) in `docs/dev/architecture.md` | S | Maint. + Doc | 178 `$wpdb->` is domain-appropriate | Architecture note exists; no drive to “eliminate all SQL” |
| P2 | Do not add TODO/FIXME markers; the scoped tree currently has **0** | S | Holds Maint. | Grep `TODO\|FIXME\|HACK\|XXX` in `includes/` + bootstrap | Count stays 0 |

## Milestone paths

| Milestone | Work | Estimated score |
|-----------|------|-----------------|
| **Hold 3.5** | P0 wpdb accessor + custom-table renderer split | ~3.6 Maint. |
| **Solid NEEDS-WORK** | + option SQL move and hook reverse-map | ~3.7 Maint. |
| **HEALTHY** | Migrator chunk/restore CCN under 10 | ≥4.0 Maint. |

## References

- [code-quality-audit-report.md](../code-quality-audit-report.md) §1
- [architecture-and-design.md](architecture-and-design.md)
- [avg-cyclomatic-complexity.md](avg-cyclomatic-complexity.md)
- [documentation.md](documentation.md)
