# CLAUDE.md — Link Extension for XFN

## Project

- Slug: link-extension-for-xfn
- Text Domain: link-extension-for-xfn
- Prefix: lexfn\_
- Min WP: 6.4 | Min PHP: 8.2 (upgrading from 7.4)
- Repo: https://github.com/courtneyr-dev/link-extension-for-xfn

## What It Does

Extends Gutenberg link interface with XFN 1.1 relationship options across all link-supporting blocks. Triple interface (toolbar, inspector, link popover), collapsible sections, visual pills, mutual exclusivity validation. Stores data in HTML rel attribute. 53.3% JavaScript.

## Standards

Same as Post Kinds: WPCS, Security Trinity, i18n, apiVersion 3.

## WP 7.0 Upgrade — Branch: feature/wp70-api-integration

### Priority Order

1. Abilities API — add/remove/get/validate/suggest relationships
2. Block Bindings — xfn/relationship-data source
3. Interactivity API — frontend tooltips, relationship directory block, visual indicators
4. PHP-only blocks — blogroll, relationship-badge
5. WP AI Client — relationship suggestions from linked page content

### Version Gate Pattern

```php
if ( version_compare( get_bloginfo( 'version' ), '7.0', '>=' ) ) { /* 7.0 features */ }
if ( function_exists( 'wp_register_ability' ) ) { /* abilities */ }
if ( function_exists( 'wp_ai_client_prompt' ) && get_option( 'lexfn_enable_ai' ) ) { /* AI */ }
```

## Key Notes

- Currently no frontend interactivity — editor-only. The Interactivity API additions are net-new frontend features.
- XFN data lives in HTML rel attributes on `<a>` tags, not in post meta. Block Bindings source needs to parse post_content to extract relationships.
- The plugin is JS-heavy (53.3%). Interactivity API features should be for NEW frontend blocks only — don't rewrite the existing editor JS.
