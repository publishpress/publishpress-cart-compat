# Classes — Remediation Guide

**Current value:** 13 classes (12 classes + 1 trait) across 2 PHPMetrics packages (avg 6.5 classes/package)
**Source:** PHPMetrics — excludes vendor, lib/vendor, tests, dist, assets, .web, .ddev, dev-workspace per audit.

The class count is low because Compatibility Mode is procedural. Do not inflate the count for its own sake. Extract types from high-LCOM / Blob surfaces so PHPMetrics packages start matching the real `includes/compat/*` layout.

| Target | Metric | Notes |
|--------|--------|-------|
| Near-term | Wizard + CPT admin split | Highest LCOM and the only Blob |
| Healthy band | No class with LCOM >15 | Compatibility Mode class currently 30 |

---

## Actionable items

| Priority | Action | Effort | Expected impact | Evidence / where | Done-when |
|----------|--------|--------|-----------------|------------------|-----------|
| P0 | Split `PPCartComp_Data_Migration_Wizard` (LCOM 23, 23 methods, WMC 133) into enqueue/bootstrap vs status-payload builders | M | LCOM <10 on each piece | `includes/admin/class-ppcartcomp-data-migration-wizard.php`; `action_map()` `:283`, `status_map()` `:332`, `remaining_map()` `:381` | Wizard class no longer owns both assets and leftover counts |
| P0 | Split `PPCartComp_Cpt_Slug_Migration_Admin` Blob (LCOM 17, 14 public methods) into notice/redirect vs AJAX controller | M | LCOM <10; Blob gone | `class-ppcartcomp-cpt-slug-migration-admin.php:10` | AJAX handlers live on a dedicated class |
| P1 | Split `PPCartComp_Studiocart_Compatibility_Mode` (LCOM 30, 30 methods, 29 public) — registration flags vs settings UI vs feature gate | L | LCOM <15 | `class-ppcartcomp-studiocart-compatibility-mode.php:12`; `init()` `:80` | Gate class does not also register every bridge |
| P1 | Keep `PPCartComp_Cpt_Slug_Migrator` + `PPCartComp_Cpt_Slug_Migrator_Restore_Trait` as the pattern; extract matching restore types for meta-key and shortcode migrators if those classes grow | M | Avoids a second Blob | Restore trait already exists (`trait-ppcartcomp-cpt-slug-migrator-restore.php`) | Meta/shortcode restore is not inlined in the forward migrator |
| P1 | Extract a query-var map type from `PPCartComp_Studiocart_Public_Query_Compatibility` (9 methods, CCN 47) | S | Class CCN <30 | `class-ppcartcomp-studiocart-public-query-compatibility.php` | Map data lives beside other `maps/` files |
| P2 | Give procedural packages (`option-key-migration`, `custom-table-migration`) a small admin/migrator class so PHPMetrics packages match directories | L | Packages >2 | PHPMetrics only sees `\` and `PPCartComp\` | At least one class per `includes/compat/*` package |
| P2 | Leave `PPCartComp_Studiocart_Conflict` and `PPCartComp_Studiocart_Admin` as-is (CCN 26 and 17, LCOM 7 and 8) | S | No churn | Bootstrap conflict gate + submenu | No drive-by rewrite |

## Milestone paths

| Milestone | Work | Estimated classes/LCOM |
|-----------|------|------------------------|
| **Hold current** | 13 types, no new Blobs | LCOM unchanged |
| **Near-term** | Wizard + CPT admin splits (~15–16 classes) | Highest LCOM <20 |
| **Healthy band** | + Compatibility Mode collaborators | No class LCOM >15 |

## References

- [code-quality-audit-report.md](../code-quality-audit-report.md) §1
- [architecture-and-design.md](architecture-and-design.md)
- [avg-cyclomatic-complexity.md](avg-cyclomatic-complexity.md)
- [violations.md](violations.md)
