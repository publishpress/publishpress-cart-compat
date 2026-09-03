---
name: plugin-features
description: >-
  Manage the sellable feature catalog in docs/features/: add or edit feature
  records, keep README registry current, link user docs via frontmatter
  features arrays, and align Studiocart marketing/docs naming. Use when the
  user asks about plugin features, feature catalog, docs/features, or mapping
  tests/docs to what the product sells.
---

# Plugin features

**Features** are sellable, customer-facing capabilities (what PublishPress Cart
can do). They are **not** test modules — modules group tests by code area.
See [test-modules](../test-modules/SKILL.md) for catalog `module` / `features`
on test cases.

Registry: [docs/features/README.md](../../../docs/features/README.md)

## What belongs here

| Include | Exclude |
|---------|---------|
| Checkout, subscriptions, coupons, downloads | Admin screen detection |
| Integrations, webhooks, Gutenberg blocks | Stripe sync handlers (internal) |
| Store settings, taxes, shipping | Secrets encryption, debug log viewer |
| Developer hooks / REST API (documented product areas) | Upgrade migrations, test-only compatibility smoke |
| StudioCart Compatibility Mode (merchant PHP shims) | Live “other plugin is present” integrations (`includes/integrations/`) |

When unsure, check [Studiocart docs](https://www.studiocart.co/docs/) and the
[marketing site](https://www.studiocart.co/) — features should map to something
a store owner would recognize as a product capability.

## File layout

One feature per file; **filename must equal `id`**:

```
docs/features/
  README.md           # Registry table (keep in sync)
  core-checkout.md
  subscriptions.md
  …
```

## Feature record template

```yaml
---
id: core-checkout
plugin: publishpress-cart
name: Core checkout & payments
status: active
description: Product checkout, one-time and free purchases, custom pricing, and payment gateway processing.
edition: both
tags: [checkout, stripe, paypal, payments]
source_areas:
  - includes/checkout/
  - public/class-ncs-cart-public.php
---

# Core Checkout & Payments

## Description

Customer-facing explanation of the capability…
```

| Field | Required | Notes |
|-------|----------|-------|
| `id` | yes | Kebab-case; equals filename without `.md` |
| `plugin` | yes | `publishpress-cart` |
| `name` | yes | Short customer-facing title |
| `status` | yes | `active` (or `planned` / `deprecated` when needed) |
| `description` | yes | One-line summary |
| `edition` | yes | `free` \| `pro` \| `both` |
| `tags` | no | Free-form filters |
| `source_areas` | no | Code paths for orientation (not exhaustive) |

## Add a feature

```
- [ ] Confirm it is sellable / user-facing (not internal infra)
- [ ] Pick `id` (unique; matches filename)
- [ ] Create docs/features/<id>.md from template
- [ ] Add row to docs/features/README.md
- [ ] Link from docs/public/ guides if user-facing docs exist
- [ ] Add id to relevant modules[].features in .tests.config.yaml
- [ ] Run node tests/tools/test-docs/scripts/backfill-features.mjs
```

## Rename or remove a feature

1. Rename file and update `id` in frontmatter.
2. Update [README.md](../../../docs/features/README.md) and cross-links in other feature docs.
3. Update `features:` in `docs/public/**/*.md` frontmatter.
4. Update `modules[].features` in `.tests.config.yaml`.
5. Run backfill-features.mjs on catalog cases.
6. Grep repo for old id before finishing.

## User documentation (`docs/public/`)

Guides that describe a capability should declare related features in
frontmatter:

```yaml
---
edition: both
nav_order: 1
features: [checkout-display, coupons]
---
```

Use an empty array only when the page is purely navigational (`docs/public/README.md`).

## Link to test catalog

Tests link to features indirectly:

1. Case has `module: <module-id>`.
2. Module in `.tests.config.yaml` lists `features: [feature-id, …]`.
3. Backfill script copies that list to case `features:`.

To add test coverage for a new feature, add its id to the right module(s),
then run backfill — see [test-modules](../test-modules/SKILL.md).

## Related

| Resource | Purpose |
|----------|---------|
| [test-modules](../test-modules/SKILL.md) | Module ↔ feature mapping on cases |
| [test-authoring](../test-authoring/SKILL.md) | Catalog cases and automation |
| [studiocart-compatibility-mode](../studiocart-compatibility-mode/SKILL.md) | StudioCart PHP shims; WordPress.org prefix issue 629 |
| [docs/public/README.md](../../../docs/public/README.md) | User-facing guides |
