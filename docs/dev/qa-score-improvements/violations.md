# Violations — Remediation Guide

**Current value:** 29 (0 critical · 14 error · 10 warning · 5 information)
**Source:** PHPMetrics — excludes vendor, lib/vendor, tests, dist, assets, .web, .ddev, dev-workspace per audit.

Fourteen **error** findings are “too complex class/method” plus one Blob on `PPCartComp_Cpt_Slug_Migration_Admin`. Ten **warnings** are Halstead “probably bugged” estimates (9 classes) plus one Stable Abstractions Principle hit on package `\`. Five **information** findings are “too long” classes. Fixing the P0/P1 rows in [avg-cyclomatic-complexity.md](avg-cyclomatic-complexity.md) and [classes.md](classes.md) retires most of this count.

| Target | Violation count | Notes |
|--------|-----------------|-------|
| Near-term | <20 | Clear Blob + split wizard (4 violations on that class alone) |
| Healthy band | 0 error · warnings only | Class CCN under the PHPMetrics “too complex” threshold |

---

## Actionable items

| Priority | Action | Effort | Expected impact | Evidence / where | Done-when |
|----------|--------|--------|-----------------|------------------|-----------|
| P0 | Split `PPCartComp_Data_Migration_Wizard` (class CCN 111, max method 17, too-long 366 LOC, bugs 1.94) | L | −4 violations on this class | `includes/admin/class-ppcartcomp-data-migration-wizard.php` | No too-complex / too-long / probably-bugged on the wizard |
| P0 | Remove Blob on `PPCartComp_Cpt_Slug_Migration_Admin` (14 public methods, LCOM 17) | M | −1 error (Blob) | `includes/compat/cpt-slug-migration/class-ppcartcomp-cpt-slug-migration-admin.php` | Blob count 0 |
| P1 | Split `PPCartComp_Cpt_Slug_Migrator` (CCN 59, max method 14, too-long 317 LOC) | M | −3 violations | `class-ppcartcomp-cpt-slug-migrator.php` | Class CCN <40; no too-long |
| P1 | Split `PPCartComp_Shortcode_Tag_Migrator` (CCN 50, too-long 339 LOC, bugs 1.19) | M | −3 violations | `class-ppcartcomp-shortcode-tag-migrator.php` | Class CCN <40; no too-long |
| P1 | Split `PPCartComp_CLI_Seed_Migration` (CCN 49, max method 13) — test-only CLI, still in PHPMetrics scope | M | −3 violations | `includes/cli/class-ppcartcomp-cli-seed-migration.php` | Class CCN <30 |
| P1 | Split `PPCartComp_Studiocart_Public_Query_Compatibility` (CCN 47, max method 13) | M | −3 violations | `class-ppcartcomp-studiocart-public-query-compatibility.php` | `is_safe_public_request()` extracted |
| P2 | Shrink `PPCartComp_Studiocart_Compatibility_Mode` (CCN 58, too-long 538 LOC) | L | −2 violations | `class-ppcartcomp-studiocart-compatibility-mode.php` | File under 400 lines |
| P2 | Re-run PHPMetrics after each batch and diff `metrics/violations.xml` | S | Confirms count | Baseline 0/14/10/5 | Error count tracked per PR |

## Typical violation categories

- **Too complex class/method** (14 errors) — total class CCN or max method CCN over PHPMetrics thresholds; addressed by extracting collaborators.
- **Probably bugged** (9 warnings) — Halstead estimate from operators/operands; see [avg-bugs-by-class.md](avg-bugs-by-class.md).
- **Too long** (5 info) — class logical LOC over 200; tracks file splits in [architecture-and-design.md](architecture-and-design.md).
- **Blob / God object** (1 error) — CPT slug migration admin mixes notice, redirect, enqueue, and five AJAX actions.
- **Stable Abstractions Principle** (1 warning) — PHPMetrics sees two packages (`\` and `PPCartComp\`); not a real namespace split. Ignore until classes move into real packages.

## Milestone paths

| Milestone | Work | Estimated violations |
|-----------|------|----------------------|
| **Hold current** | No new too-complex classes | ~29 |
| **Near-term** | Wizard split + Blob removal | <20 |
| **Healthy band** | + migrator / query-compat splits | 0 error |

## References

- [code-quality-audit-report.md](../code-quality-audit-report.md) §1
- [avg-bugs-by-class.md](avg-bugs-by-class.md)
- [avg-cyclomatic-complexity.md](avg-cyclomatic-complexity.md)
- [classes.md](classes.md)
