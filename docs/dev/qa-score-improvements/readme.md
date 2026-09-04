# QA Score Improvement Guides

Actionable remediation for [PublishPress Cart Compatibility Code Quality Audit](../code-quality-audit-report.md). Baseline scores and full evidence: [code-quality-audit-report.md](../code-quality-audit-report.md).

**Baseline:** Code Quality Index **3.2** → **NEEDS-WORK**

| Metric | Current | Start here |
|--------|---------|------------|
| [Arch.](architecture-and-design.md) | 3.0/5.0 | Split `functions.php` (2,279 lines) and `hooks.php` (859 lines) |
| [Maint.](code-maintainability.md) | 3.5/5.0 | Cut `global $` / `$wpdb` concentration in migrators; shrink branchy admin renderers |
| [Doc](documentation.md) | 3.2/5.0 | Write `architecture.md`; fix stale extract guide; document Data migration as a feature |
| [Code Quality Index](code-quality-score.md) | 3.2/5.0 | Cross-cutting priorities and milestone paths |
| [Violations](violations.md) | 29 | 0 critical · 14 error · 10 warning · 5 info — too-complex class/method plus Halstead bugs |
| [Classes](classes.md) | 13 | Extract types from the wizard, compatibility-mode class, and CPT admin Blob |
| [Avg Cyclomatic Complexity](avg-cyclomatic-complexity.md) | 41.5 | Break up wizard (CCN 111) and migrator classes |
| [Avg Bugs by Class](avg-bugs-by-class.md) | 0.7 | Lower Halstead risk in wizard (1.94) and shortcode/CPT migrators |
| [Recommendation](recommendation.md) | NEEDS-WORK | Exit NEEDS-WORK → HEALTHY |

**Weakest sub-metrics:** Arch. (3.0) then Doc (3.2) — file splits plus an architecture map move the index fastest.

## Thresholds

| Label | Code Quality Index |
|-------|-------------------|
| HEALTHY | ≥3.5 |
| NEEDS-WORK | 2.5–3.4 |
| CRITICAL | <2.5 |

Sub-metric targets: needs-work **≥2.5**, healthy **≥4.0** each.

## How to use

1. Work **P0** rows in weakest metric guides first.
2. Use **Done-when** on each row before claiming progress.
3. Re-run QA audit and update [code-quality-audit-report.md](../code-quality-audit-report.md) to refresh baselines.

## Related

- [code-quality-audit-report.md](../code-quality-audit-report.md)
- [security-audit-report.md](../security-audit-report.md)
- [extract-from-cart.md](../extract-from-cart.md)
