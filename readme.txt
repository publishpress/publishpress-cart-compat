=== PublishPress Cart Migrations ===

Contributors: andergmartins, rizaardiyanto, ojopaul, stevejburge, publishpress
Tags: ecommerce, studiocart, migration, compatibility, publishpress cart
Requires at least: 6.7
Tested up to: 7.0
Stable tag: 1.1.2
Requires PHP: 7.4
Requires Plugins: publishpress-cart
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin helps you migrate your store to PublishPress Cart.

== Description ==

PublishPress Cart Migrations is a companion plugin for [PublishPress Cart](https://publishpress.com/publishpress-cart/). It keeps StudioCart PHP names, hooks, and related surfaces available for existing extensions, and provides merchant-run data migration tools.

This plugin does not replace PublishPress Cart and is not a live StudioCart integration. Install it only on sites that need leftover StudioCart compatibility or data migration.

**Want product updates and support resources?** Visit [PublishPress Cart](https://publishpress.com/publishpress-cart/).

== Installation ==

1. Install and activate [PublishPress Cart](https://wordpress.org/plugins/publishpress-cart/).
2. Upload the plugin files to `/wp-content/plugins/publishpress-cart-compat`, or install the plugin through the WordPress plugins screen.
3. Activate the plugin through the Plugins screen.

== Changelog ==

= 1.1.2 =
* Fixed: the Elementor editor no longer stays on its loading screen when Compatibility Mode is on. The plugin repeated the same database query thousands of times on each page load.
* Fixed: the "Shortcodes in your content" step now updates StudioCart Elementor widgets, so Elementor order forms keep working after you remove this plugin.
* Added: if your site already finished that step and still has StudioCart Elementor widgets, the step reopens and a notice asks you to run it again.
* Fixed: the "Shortcodes in your content" step now counts Elementor layouts in its items to migrate.

= 1.1.1 =
* Fixed: the leftover shortcode check no longer scans all post content on every page load. It now runs once. Large sites could slow down or overload the database.
* Improved: the shortcode migration skips finished post and notification scans on later batches, so large sites finish much faster.

= 1.1.0 =
* Renamed the plugin to PublishPress Cart Migrations.
* Rewrote the migration notice, landing, and wizard in plain language, with a large completion message.
* Steps with no StudioCart data now show "Nothing to migrate" instead of "Completed" before the migration starts.
* One backup reminder and one confirm before the first run. Removed the extra custom-tables confirm and the Backup & restore step.
* Explained Compatibility Mode with concrete examples and removed the Migration backups box.

= 1.0.0 =
* StudioCart Compatibility Mode: optional PHP shims for leftover functions, hooks, classes, constants, and public URLs.
* Data migration wizard: merchant-run rewrite of leftover CPT slugs, meta keys, shortcode content, option keys, and custom tables.
