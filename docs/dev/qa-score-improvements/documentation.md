# Documentation — Remediation Guide

**Current score:** 3.2/5.0
**Targets:** Needs-work ≥2.5 · Healthy ≥4.0

Primary issue: **runtime architecture is undocumented and the extract guide is stale**, while class PHPDoc is already complete. Hotspot: `docs/dev/extract-from-cart.md` still says “Status: Not started. Skeleton plugin exists; packages still live in Cart.”

**Total LOC (~3,072 class-scoped) is not a remediation target.** Do not PHPDoc every `functions.php` wrapper; document the load path and maps instead.

---

## Actionable items

| Priority | Action | Effort | Expected score impact | Evidence / where | Done-when |
|----------|--------|--------|----------------------|------------------|-----------|
| P0 | Add `docs/dev/architecture.md` covering bootstrap (`publishpress-cart-compat.php`), Cart load-from-companion contract, Compatibility Mode gate, and the five Data migration packages | M | Doc ~3.5 | No architecture file today; only `extract-from-cart.md` and `ai/plugin-rules.md` under `docs/dev/` | Architecture doc exists and is linked from `README.md` |
| P0 | Rewrite `docs/dev/extract-from-cart.md` status to match current code (packages live here; Cart must not keep copies) or replace it with a “how Cart loads this plugin” note | S | Doc ~3.4 | Line 7: “Not started. Skeleton plugin exists” | Status block matches `includes/compat/` layout |
| P1 | Add a `docs/features/` record for merchant Data migration (wizard + CPT/meta/shortcode/option/custom-table steps) | M | Doc ~3.4 | Only `docs/features/ppcart-compatibility-mode.md` exists; feature `plugin:` still says `publishpress-cart` | Feature record exists; frontmatter `plugin:` is `publishpress-cart-compat` |
| P1 | Fix `ppcart-compatibility-mode.md` frontmatter `plugin: publishpress-cart` and `source_areas` now that the package lives in this repo | S | Doc ~3.3 | `docs/features/ppcart-compatibility-mode.md:3` | Frontmatter points at this plugin |
| P1 | Update `readme.txt` changelog — it still says “Initial plugin skeleton” despite a full compat + migration tree | S | Doc ~3.3 | `readme.txt` `= 1.0.0 =` | Changelog names Compatibility Mode and Data migration |
| P1 | Add a file-level docblock to `functions.php` listing wrapper prefixes (`ncs_*`, `sc_*`) and that bodies must stay one-line delegates | S | Doc ~3.3 | 324 wrappers, 0 with PHPDoc; 401/725 functions documented overall (55%) | File header explains the catalog; wrappers stay undoc’d individually |
| P2 | Expand `README.md` Layout table beyond the bootstrap file (packages under `includes/compat/`, wizard assets) | S | Doc ~3.4 | `README.md` Layout has one row | Layout table lists compat packages and admin wizard |
| P2 | Keep class-level PHPDoc at 13/13 as new classes land; require `@param`/`@return` on methods over ~40 lines | S | Holds Doc | 13/13 classes documented; 288 `@param` / 428 `@return` in `includes/` | New classes ship with a class docblock |

## Milestone paths

| Milestone | Work | Estimated score |
|-----------|------|-----------------|
| **Hold 3.2** | P0 architecture.md + extract-guide status | ~3.5 Doc |
| **Solid NEEDS-WORK** | + feature records and readme.txt | ~3.6 Doc |
| **HEALTHY** | README layout + wrapper file header | ≥4.0 Doc |

## References

- [code-quality-audit-report.md](../code-quality-audit-report.md) §1
- [architecture-and-design.md](architecture-and-design.md)
- [code-quality-score.md](code-quality-score.md)
