# Recommendation — NEEDS-WORK → HEALTHY

**Current label:** **NEEDS-WORK**
**Code Quality Index:** 3.2

**Why (audit):** Two Compatibility Mode god files (`functions.php` 2,279 lines, `hooks.php` 859) and PHPMetrics average class CCN 41.5 keep Architecture at 3.0. Maintainability is already 3.5 (no 100-line functions, no TODOs). Documentation is 3.2: complete class PHPDoc, but no architecture map and a stale extract guide. Together that is 3.2 — inside NEEDS-WORK, short of HEALTHY (≥3.5).

---

## Transition map

```
CRITICAL (<2.5) ──not here──▶ NEEDS-WORK (3.2, current) ──file splits + architecture.md──▶ HEALTHY (≥3.5)
                                                                                              │
                                                                                              ▼
                                                                                      Target: ≥3.8 with class CCN <20
```

| Stage | Code Quality Index | Primary work | Guide |
|-------|-------------------|--------------|-------|
| Exit CRITICAL | ≥2.5 | **Already clear** — packages exist, no 100-line functions | [architecture-and-design.md](architecture-and-design.md) |
| **Solid NEEDS-WORK (current)** | 2.5–3.4 | Split god files; write architecture.md | [architecture-and-design.md](architecture-and-design.md), [documentation.md](documentation.md) |
| HEALTHY | ≥3.5 | Wizard/migrator class splits; feature records | [classes.md](classes.md), [avg-cyclomatic-complexity.md](avg-cyclomatic-complexity.md) |
| Sustained HEALTHY | ≥3.5 buffer | 0 PHPMetrics errors; security audit for col D | [violations.md](violations.md) |

---

## Actionable items

| Priority | Action | Effort | Recommendation impact | Evidence / where | Done-when |
|----------|--------|--------|----------------------|------------------|-----------|
| P0 | Split `functions.php` and `hooks.php` so no file exceeds 700 lines | M | NEEDS-WORK → closer to HEALTHY | 2 god files | Arch. ≥3.3 |
| P0 | Add `docs/dev/architecture.md` and fix extract-guide status | M | Doc ≥3.5 | Missing architecture; stale extract | Doc P0 done-when in [documentation.md](documentation.md) |
| P1 | Split wizard (CCN 111) and CPT admin Blob | L | HEALTHY if Arch. and Doc both move | 14 error violations | PHPMetrics errors <10 |
| P1 | `$wpdb` accessor for the three SQL-heavy migrators | M | Holds Maint. while files shrink | 57 globals | See [code-maintainability.md](code-maintainability.md) |
| P1 | Data migration feature record + `readme.txt` changelog | S | Doc buffer above 3.5 | One feature file; skeleton changelog | Feature catalog lists both product surfaces |
| P2 | Security audit so Maintainability Index is computable | M | Spreadsheet complete | Index is `—` | `docs/dev/security-audit-report.md` exists |
| P2 | Re-audit after the next remediation batch | S | Confirms HEALTHY or not | 2026-09-04 baseline | Fresh `code-quality-audit-report.md` |

## Milestone paths

| Milestone | Work | Recommendation |
|-----------|------|----------------|
| **Exit CRITICAL** | Already met | NEEDS-WORK |
| **Solid NEEDS-WORK** | P0 file splits + architecture.md | NEEDS-WORK ~3.4 |
| **HEALTHY** | + wizard/Blob splits and Doc P1 | HEALTHY ≥3.5 |

## Supporting metrics checklist

- [ ] Code Quality Index ≥3.5
- [ ] No first-party PHP file over 700 lines
- [ ] PHPMetrics error violations = 0
- [ ] Average class CCN <30 (near-term) then <10
- [ ] `docs/dev/architecture.md` exists
- [ ] Security audit present (Maintainability Index no longer `—`)

## Re-audit procedure

1. `vendor/bin/phpmetrics --config=phpmetrics.json --report-json=metrics/report.json --report-summary-json=metrics/summary.json --report-violations=metrics/violations.xml`
2. Re-count `global $`, `$wpdb->`, files >700 lines, functions >100 lines in `includes/` + `publishpress-cart-compat.php` (exclude `.web/`, `tests/`, `vendor/`).
3. Replace [code-quality-audit-report.md](../code-quality-audit-report.md) and regenerate this directory.

## References

- [code-quality-audit-report.md](../code-quality-audit-report.md)
- [readme.md](readme.md)
- [code-quality-score.md](code-quality-score.md)
- [architecture-and-design.md](architecture-and-design.md)
- [code-maintainability.md](code-maintainability.md)
- [documentation.md](documentation.md)
- [violations.md](violations.md)
- [classes.md](classes.md)
- [avg-cyclomatic-complexity.md](avg-cyclomatic-complexity.md)
- [avg-bugs-by-class.md](avg-bugs-by-class.md)
