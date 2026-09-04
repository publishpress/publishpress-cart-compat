# Average Bugs by Class — Remediation Guide

**Current value:** 0.7 average bugs by class
**Source:** PHPMetrics — excludes vendor, lib/vendor, tests, dist, assets, .web, .ddev, dev-workspace per audit.

This is Halstead’s theoretical defect estimate, not observed production bugs. The suite already has ~20 unit and ~28 integration tests. Lower the estimate by shrinking operator/operand volume on the worst classes and adding tests only where a class is both high-bugs and thinly covered.

| Target | Avg bugs by class | Notes |
|--------|-------------------|-------|
| Near-term | <0.5 | Wizard (1.94) and the two 1.1+ migrators split |
| Healthy band | <0.3 | In line with Cart Free’s 0.2 |

---

## Actionable items

| Priority | Action | Effort | Expected impact | Evidence / where | Done-when |
|----------|--------|--------|-----------------|------------------|-----------|
| P0 | Split `PPCartComp_Data_Migration_Wizard` (bugs 1.94, 183 operators, 772 Halstead length) | L | Class bugs <0.6 each | Highest bugs-by-class; `class-ppcartcomp-data-migration-wizard.php` | No remaining class over 1.0 bugs |
| P0 | Add integration coverage for wizard `ajax_status` / `build_bootstrap` leftover counts if Unit `DataMigrationWizardTest` does not hit SQL-backed statuses | M | Halstead still drops via split; tests catch regressions | `tests/codeception/Unit/Compat/DataMigrationWizardTest.php` | Status payload cases cover CPT/meta/shortcode/tables/options |
| P1 | Split `PPCartComp_Shortcode_Tag_Migrator` (bugs 1.19, 148 operators) | M | Class bugs <0.6 | `class-ppcartcomp-shortcode-tag-migrator.php` | Forward migrate vs restore vs count queries separated |
| P1 | Split `PPCartComp_Cpt_Slug_Migrator` (bugs 1.17) | M | Class bugs <0.6 | `class-ppcartcomp-cpt-slug-migrator.php` | Step 1 / step 2 / backup table not one class |
| P1 | Split `PPCartComp_CLI_Seed_Migration` (bugs 1.08) | M | Class bugs <0.6 | `includes/cli/class-ppcartcomp-cli-seed-migration.php` | Seed families in helpers already used by `tests/store-fixtures/` |
| P1 | Reduce `PPCartComp_Meta_Key_Migrator` (bugs 0.85, max method CCN 12, `migrate_one_row()` `:202`) | M | Class bugs <0.6 | `class-ppcartcomp-meta-key-migrator.php` | One-row migrate CCN under 10 |
| P2 | Leave `PPCartComp_Studiocart_Admin` (0.19) and the two small migration-admin classes (0.11 each) alone | S | No churn | Lowest bugs-by-class | No drive-by rewrite |
| P2 | Re-run PHPMetrics after splits; average is 0.71 across 13 types | S | Confirms drop | Baseline 0.7 | `metrics/summary.json` Bugs.avgBugsByClass <0.5 |

## Milestone paths

| Milestone | Work | Estimated avg bugs |
|-----------|------|--------------------|
| **Hold current** | Keep tests green on migrators | ~0.7 |
| **Near-term** | Wizard + shortcode/CPT migrator splits | <0.5 |
| **Healthy band** | + CLI / meta-key splits | <0.3 |

## References

- [code-quality-audit-report.md](../code-quality-audit-report.md) §1
- [violations.md](violations.md)
- [classes.md](classes.md)
- [avg-cyclomatic-complexity.md](avg-cyclomatic-complexity.md)
