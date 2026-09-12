=== Tooltipy (tooltips for WP) ===
Contributors: jamelzarga, lebleut
Tags: tooltip, keywords, glossary, highlight, definition
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 7.0.0-alpha
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically creates tooltip boxes for your technical keywords to explain them to your site visitors.

== Description ==

**Tooltipy** automatically detects keywords in your posts/pages and displays tooltip popups with their definitions.

**Version 7** is a major architectural rewrite (PHP 8.1+ OOP, Tippy.js rendering) with **100% backward compatibility** for existing users (same database options, post meta keys, CSS classes, shortcodes, and hooks).

= Key Features =

* Auto-detect keywords and display tooltips on hover (Tippy.js)
* Synonyms support (pipe-separated)
* Case-sensitive matching option
* Prefix matching (e.g. "photo" matches "photography")
* Glossary shortcode: `[tooltip_glossary]` (aliases: `[kttg_glossary]`, `[tooltipy_glossary]`)
* Manual shortcode: `[tooltip]`
* Keyword families / categories
* AJAX loading of tooltip content
* YouTube video tooltips
* Image alt-text tooltips
* Sidebar widget showing related keywords
* Extensible addon system (`AddonInterface` / `AddonManager`)

= Architecture (7.x) =

* PHP 8.1 namespaces under `Tooltipy\`
* PSR-4 autoloading via `spl_autoload_register`
* Tippy.js bundled locally (no CDN)
* Full backward compatibility: same option names, post meta keys, hooks

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate through the Plugins menu
3. Go to **Tooltipy > Settings** to configure

== Frequently Asked Questions ==

= Is 7.x compatible with existing data from 5.x? =
Yes. All database options, post meta, CSS classes, and shortcodes are identical.

= How do I create a keyword? =
Go to **Tooltipy > Add New**. The title is the keyword; the content is the tooltip body.

= How do I add a glossary? =
Create a page and add `[tooltip_glossary]` (or the legacy `[kttg_glossary]`).

== Changelog ==

= 7.0.0-alpha =
* Major OOP rewrite: PHP 8.1 namespaces, PSR-4 autoloading
* Tippy.js for tooltip positioning (legacy CSS classes preserved)
* AddonInterface + AddonManager for extensibility
* Security: metabox nonces, escaped output, wp_enqueue for assets
* Backward compatible: all option keys, meta keys, shortcodes, and hooks preserved

= 5.5.9 =
* Security: nonce verification on AJAX calls
* Improved: excluded posts migration to postmeta

= 5.2 =
* Previous stable procedural release

== Upgrade Notice ==

= 7.0.0-alpha =
Major architectural rewrite. Requires PHP 8.1+ and WordPress 6.0+. Existing data and settings are preserved automatically.
