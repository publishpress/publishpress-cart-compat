# Testing docs

Test catalog for PublishPress Cart Compatibility. Configuration lives in
[`.tests.config.yaml`](../../.tests.config.yaml).

Markdown cases under `docs/testing/cases/<suite>/` are the catalog source of
truth. Keep IT/UT ids from Cart when a case moved with the assertion.

First-party Cart scanners (leftover-literal, HTML ids, JS globals, REST
namespace, admin screen slugs) stay in PublishPress Cart. This catalog owns
Compatibility Mode, leftover maps, and Data migration.

## Layout

```
.tests.config.yaml
docs/testing/cases/unit/
docs/testing/cases/integration/
docs/testing/seed-migration-data.md
tests/README.md
```

Seeding leftovers for the Data migration wizard: [seed-migration-data.md](seed-migration-data.md).

| Suite | Prefix | Runner |
|-------|--------|--------|
| `unit` | `UT` | `composer test:unit` |
| `integration` | `IT` | `composer test:integration` |

Integration WPLoader activates Cart (`../publishpress-cart`) and this plugin.
See [tests/README.md](../../tests/README.md).
