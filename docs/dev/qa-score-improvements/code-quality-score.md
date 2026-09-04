# Code Quality Index — Priorities & Milestones

**Current Code Quality Index:** 3.2/5.0
**Recommendation:** NEEDS-WORK

**Sub-metrics:** Arch. 3.0 · Maint. 3.5 · Doc 3.2
**Weakest:** Arch. (3.0) then Doc (3.2) — start with [architecture-and-design.md](architecture-and-design.md) and [documentation.md](documentation.md).

| Label | Code Quality Index |
|-------|-------------------|
| HEALTHY | ≥3.5 |
| NEEDS-WORK | 2.5–3.4 |
| CRITICAL | <2.5 |

---

## Actionable items

| Priority | Action | Effort | Expected score impact | Evidence / where | Done-when |
|----------|--------|--------|----------------------|------------------|-----------|
| P0 | Land P0 file splits from [architecture-and-design.md](architecture-and-design.md) (`functions.php` 2,279 lines, `hooks.php` 859 lines) | M | CQI into the 3.3–3.4 range | Two god files | No scoped PHP file over 700 lines |
| P0 | Land P0 rows from [documentation.md](documentation.md) (`architecture.md` + stale extract guide) | M | Doc ≥3.5, CQI +0.1 | No architecture map; extract guide says not started | Both docs match `includes/compat/` |
| P1 | Split the wizard (CCN 111) and compatibility-mode class (LCOM 30) per [classes.md](classes.md) and [avg-cyclomatic-complexity.md](avg-cyclomatic-complexity.md) | L | Arch. toward 3.6; violations down | 14 PHPMetrics errors | Wizard class CCN <50 |
| P1 | Land P0 `$wpdb` accessor + custom-table renderer split from [code-maintainability.md](code-maintainability.md) | M | Holds Maint. 3.5+ while Arch. moves | 57 globals, 178 `$wpdb->` | Shortcode/meta/CPT migrators ≤2 globals each |
| P1 | Clear the CPT admin Blob violation ([violations.md](violations.md)) | M | Error-level violations down | `PPCartComp_Cpt_Slug_Migration_Admin` Blob | PHPMetrics Blob count 0 |
| P2 | Re-run `vendor/bin/phpmetrics --config=phpmetrics.json` after each remediation PR and diff `metrics/summary.json` | S | Confirms movement | PHPMetrics 2.11.0 baseline 2026-09-04 | Summary JSON in the PR description |
| P2 | Run a security audit so Maintainability Index can be filled | M | Spreadsheet col D becomes computable | Index currently `—` | `docs/dev/security-audit-report.md` exists |

## Milestone paths

**Path A — Stay NEEDS-WORK but climb:** P0 god-file splits + architecture.md. Target index ~3.3–3.4.

**Path B — HEALTHY:** Path A plus wizard/compatibility-mode splits and Doc P1 feature records. Target index ≥3.5.

**Path C — Comfortable HEALTHY:** Path B plus no file over 500 lines, class CCN average under 20, and a security audit. Target index ≥3.8.

## References

- [code-quality-audit-report.md](../code-quality-audit-report.md)
- [architecture-and-design.md](architecture-and-design.md)
- [code-maintainability.md](code-maintainability.md)
- [documentation.md](documentation.md)
- [recommendation.md](recommendation.md)
