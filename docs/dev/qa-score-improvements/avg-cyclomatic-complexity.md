# Average Cyclomatic Complexity — Remediation Guide

**Current value:** 41.5 average cyclomatic complexity by class
**Source:** PHPMetrics — excludes vendor, lib/vendor, tests, dist, assets, .web, .ddev, dev-workspace per audit.

Healthy band for this metric is under 10. 41.5 is driven by a few large classes (wizard 111, CPT migrator 59, Compatibility Mode 58), not by long functions — the longest function in scope is 74 lines. Flatten branching and extract collaborators; do not chase a lower average by adding empty classes.

| Target | Avg CCN by class | Notes |
|--------|------------------|-------|
| Near-term | <30 | Wizard out of the 100s |
| Healthy band | <10 | Matches the Cart Free healthy line |

---

## Actionable items

| Priority | Action | Effort | Expected impact | Evidence / where | Done-when |
|----------|--------|--------|-----------------|------------------|-----------|
| P0 | Split `PPCartComp_Data_Migration_Wizard` (class CCN 111, max method 17, `enqueue_assets()` heuristic CCN 17 at `:66`) | L | Class CCN <50; average drops several points | `includes/admin/class-ppcartcomp-data-migration-wizard.php` | No wizard method CCN over 10 |
| P0 | Flatten `ppcart_render_custom_table_migration_step()` (heuristic CCN 39) — highest function in the tree | S | Removes a procedural hotspot PHPMetrics under-counts | `includes/compat/custom-table-migration/admin.php:38` | Function CCN under 15 |
| P1 | Reduce `PPCartComp_Cpt_Slug_Migrator` class CCN 59 / max method 14 (`run_step1_chunk_body()` `:95`) | M | Class CCN <40 | `class-ppcartcomp-cpt-slug-migrator.php` | Chunk body CCN under 10 |
| P1 | Reduce `restore_backup_row()` (heuristic CCN 17, 64 lines) | M | Trait CCN 27 → <20 | `trait-ppcartcomp-cpt-slug-migrator-restore.php:127` | Restore row CCN under 10 |
| P1 | Precompute reverse hook map; `ncs_cart_canonical_to_legacy_hook_names()` heuristic CCN 23 | S | Hooks runtime CCN down | `hooks.php:222` | Function CCN under 10 |
| P1 | Split `PPCartComp_Studiocart_Public_Query_Compatibility` (`is_safe_public_request()` heuristic CCN 13 at `:124`; class CCN 47) | M | Class CCN <30 | `class-ppcartcomp-studiocart-public-query-compatibility.php` | Safety checks extracted |
| P2 | Split `PPCartComp_CLI_Seed_Migration` (class CCN 49, `seed_custom_tables()` CCN 13 at `:345`) | M | Class CCN <30 | `includes/cli/class-ppcartcomp-cli-seed-migration.php` | One method per leftover family |
| P2 | Re-run PHPMetrics; average CCN is the mean of 13 types — splitting the wizard does most of the work | S | Confirms drop | Baseline 41.5 | `metrics/summary.json` Complexity.avgCyclomaticComplexityByClass <30 |

## Milestone paths

| Milestone | Work | Estimated avg CCN |
|-----------|------|-------------------|
| **Hold current** | No new class CCN over 50 | ~41.5 |
| **Near-term** | Wizard + custom-table renderer | <30 |
| **Healthy band** | + migrator / query-compat / CLI splits | <10 |

## References

- [code-quality-audit-report.md](../code-quality-audit-report.md) §1
- [architecture-and-design.md](architecture-and-design.md)
- [classes.md](classes.md)
- [violations.md](violations.md)
