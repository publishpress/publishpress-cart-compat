# PublishPress Cart Compatibility Code Quality Audit

**Audit date:** 2026-09-04 · **Method:** static white-box + PHPMetrics v2.11.0

> **Scope:** `includes/` and `publishpress-cart-compat.php` per `phpmetrics.json`. Excludes `vendor/`, `lib/vendor/`, `tests/`, `assets/`, `.web/`, `.ddev/`, `languages/`, `dev-workspace*`, and `dist/`.
>
> **First audit** of this companion plugin. PHPMetrics class-scoped LOC is 3,072; physical PHP in the same directories is ~13,860 lines because Compatibility Mode is mostly procedural wrappers and maps, which PHPMetrics does not count as class LOC.

## SPREADSHEET DATA

Paste cols **D–M** here. Paste col **C** from [`docs/dev/security-audit-report.md`](security-audit-report.md). **Do not report Code Size Index (col B)** — spreadsheet computes `=J2/MAX(J$2:J$20)*5` after Lines of Code paste.

| Metric | Score/Value | Notes |
|--------|-------------|-------|
| Maintainability Index | — | Requires Security Index from a security audit; `docs/dev/security-audit-report.md` is not present. |
| Arch. | 3.0 | Package layout is clear; two files exceed 700 lines (`functions.php` 2,279, `hooks.php` 859) and four more sit at 501–640. |
| Maint. | 3.5 | Zero methods over 100 lines and zero TODO/FIXME; 57 `global $` usages and 178 `$wpdb->` references are concentrated in migrators. |
| Doc | 3.2 | 13/13 classes have class-level PHPDoc; no `docs/dev/architecture.md`; `docs/dev/extract-from-cart.md` still says the extract is not started. |
| Code Quality Index | 3.2 | AVERAGE(Arch., Maint., Doc) = (3.0 + 3.5 + 3.2) ÷ 3 |
| Violations | 29 | PHPMetrics: 0 critical · 14 error · 10 warning · 5 info |
| Lines of Code | 3072 | From PHPMetrics — col J; drives spreadsheet Code Size Index |
| Classes | 13 | From PHPMetrics (12 classes + 1 trait) |
| Avg Cyclomatic Complexity | 41.5 | From PHPMetrics — col L |
| Avg Bugs by Class | 0.7 | From PHPMetrics — col M |
| Recommendation | NEEDS-WORK | Code Quality Index 3.2 is in the 2.5–3.4 band; file concentration and class CCN are the blockers, not missing tests. |

## 1. Code Quality Breakdown

**Code Quality Index: 3.2/5.0**

| Sub-Metric | Score | Key Finding |
|------------|-------|-------------|
| Arch. | 3.0/5.0 | Compat packages (`studiocart-compatibility-mode`, `cpt-slug-migration`, `meta-key-migration`, `shortcode-tag-migration`, `option-key-migration`, `custom-table-migration`) are the right shape, but `includes/compat/studiocart-compatibility-mode/functions.php` (2,279 lines, 324 undocumented `ncs_*`/`sc_*` wrappers) and `hooks.php` (859 lines) violate the 500-line file convention. |
| Maint. | 3.5/5.0 | Longest function is 74 lines (`ppcart_migrate_custom_tables()`); 0 TODOs. Drag is 57 `global $` (mostly `$wpdb`), 178 `$wpdb->` calls, and branchy migrator/admin methods (`ppcart_render_custom_table_migration_step()` heuristic CCN 39). |
| Doc | 3.2/5.0 | Class PHPDoc is complete (13/13). Function coverage is 401/725 (55%), almost all of the gap is the 324 wrappers in `functions.php`. Contributor docs: `README.md` and the test catalog are solid; runtime architecture is undocumented and the extract guide is stale. |

**Maintainability Index (spreadsheet col D):** — — 40% Security + 60% Code Quality, scaled by PHPMetrics penalty. Requires Security Index from `docs/dev/security-audit-report.md` (not yet produced).

**Code Size Index (spreadsheet col B):** not reported here — computed in spreadsheet only, relative to other tracked plugins.

**Major Issues:**
- God classes/files: **2** files >700 lines — `includes/compat/studiocart-compatibility-mode/functions.php` (2,279), `includes/compat/studiocart-compatibility-mode/hooks.php` (859). Four more at 501–640: compatibility-mode class (640), data-migration wizard (564), `options.php` (556), shortcode-tag migrator (501).
- Global vars: 57 (`global $` in scoped dirs; 1 in `publishpress-cart-compat.php`, 56 in `includes/`)
- TODO/FIXME: 0 in scoped source
- Large fns: 0 methods/functions >100 lines (6 functions ≥60 lines; top: `ppcart_migrate_custom_tables()` 74 lines in `includes/compat/custom-table-migration/migrator.php:15`)

## 2. Dependencies

**Critical Issues:** No runtime Composer packages. PHP `>=7.4` is end-of-life; runtime floor tracks WordPress 6.7+ compatibility. Not scored — see a future security audit.

**Immediate Updates:** None for production PHP. Dev-only: `publishpress/dev-workspace` `^1.8`, `@wordpress/scripts` `^32.1.0` (migration wizard).

**PHP Version:** `>=7.4` (`composer.json`, plugin header `Requires PHP`, `PPCARTCOMP_MINIMUM_PHP_VERSION`)

**WordPress Version:** `>=6.7` (`Requires at least`, `PPCARTCOMP_MINIMUM_WP_VERSION`; `readme.txt` tested up to 7.0)

**Payment stack:** None in this plugin. Checkout, Stripe, and PayPal live in PublishPress Cart. This companion owns shims, leftover-store read-through, and merchant Data migration SQL.

**Tests:** Codeception unit (`tests/codeception/Unit/`, ~20 cases) and integration (`tests/codeception/Integration/`, ~28 cases) via `composer test:unit` / `composer test:integration`. Catalog under `docs/testing/cases/`. `AGENTS.md` lists Playwright suites; this repo has no `composer test:regression` / `test:admin` scripts yet.

## 3. Final Recommendation: NEEDS-WORK

**Rationale:** The Code Quality Index is 3.2, below the 3.5 HEALTHY line. The plugin is a well-bounded companion (packages per migration type, map files for legacy names, 100% class PHPDoc, no 100-line functions), but PHPMetrics average cyclomatic complexity by class is 41.5 (healthy band is under 10) and two Compatibility Mode files are still god-sized. Splitting those files and the wizard/migrator classes is the shortest path to HEALTHY.

**Key Factors:**
- Two god files (>700 LOC), led by a 2,279-line alias catalog; four additional files over 500 lines
- PHPMetrics 14 error-level violations (too-complex class/method, one Blob on `PPCartComp_Cpt_Slug_Migration_Admin`) and average class CCN 41.5
- Strong function-level hygiene (0 functions >100 lines, 0 TODOs) and complete class PHPDoc, offset by a stale extract guide and no architecture map
