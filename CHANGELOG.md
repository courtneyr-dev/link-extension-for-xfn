# Changelog

All notable changes to the Link Extension for XFN will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed

- `xfn/suggest-relationship` required only `read`, so any authenticated user — not just someone who could edit content — could trigger the AI provider call behind it, with no cap on how often. The permission callback now requires `edit_posts`, matching the other content abilities in this file. The execute callback also caps callers at 20 requests per hour via a per-user transient, returning a `WP_Error` with `status => 429` once the cap is hit. The `url` and `context` inputs are now sanitized with `esc_url_raw()` and `wp_strip_all_tags()` before they reach the AI prompt, and `context` has its quote marks and line breaks stripped and is capped at 500 characters, closing a prompt-injection path where an unescaped quote in either input could break out of the quoted prompt string.

## [1.0.5] - 2026-08-20

### Changed

- The activation gate now enforces **PHP 8.2**, matching the `Requires PHP` header and the
  `>=8.2` composer constraint. It previously checked for 7.4 and would not have blocked PHP 8.0
  or 8.1 on its own. In practice WordPress reads the 8.2 header and refuses activation below it,
  so the weaker guard was masked rather than harmful — but the two disagreed in source, and the
  guard's error message advertised the wrong floor. Verified at the boundary: 8.1.31 is blocked,
  8.2.0 is allowed.
- **Tested up to WordPress 7.1.** Verified against WordPress 7.1 final on PHP 8.2.32 with `WP_DEBUG` and `SCRIPT_DEBUG` enabled: the PHPUnit suite passes (90 tests: 22 unit, 68 integration), PHPStan level 5 and PHPCS report no errors, all nine `xfn/*` abilities register under core's name grammar, the three blocks and four editor scripts register with every dependency resolving, and the Interactivity tooltip module and stylesheet both serve. No PHP notices or deprecations were raised. The supported floor stays at 6.9. The Interactivity tooltip gate stays at 7.0 and up — 7.1 satisfies it, so tooltips are on.

### Fixed

- The meta-backed abilities in `XFN_Core_Abilities` now register. Their names used underscores (`xfn/set_relationships`), and core's `WP_Abilities_Registry::register()` only accepts `/^[a-z0-9-]+\/[a-z0-9-]+$/`, so each was refused with a `_doing_it_wrong()` notice and nothing else — invisible with `WP_DEBUG` off. They are now `xfn/set-meta-relationships`, `xfn/get-meta-relationships`, `xfn/add-meta-relationship`, and `xfn/remove-meta-relationship`. The `-meta` qualifier is load-bearing: `XFN_Content_Abilities` already owns `xfn/get-relationships` and its siblings, and two abilities cannot share a name. No aliases, since the underscored names never registered.
- Three abilities declared `meta.mcp.type = resource` without a `meta.mcp.uri`, which `RegisterAbilityAsMcpResource` treats as fatal, so every WordPress load logged three errors from `McpResource::fromAbility`. A resource read only ever receives the JSON-RPC envelope, so an ability that needs caller input cannot work as one: `xfn/validate-relationships` requires `rels` and `xfn/suggest-relationship` requires `url`, and both are tools now. `xfn/get-relationships` keeps resource status and resolves at `xfn://relationships`, since `post_id` is optional and the no-`post_id` branch already scans every published post.
- The `wp_pinch_mcp_server_abilities` filter appended every declared XFN name unconditionally, including names core had refused at registration, and mcp-adapter logged one missing-ability error per name while building its tool list. The filter now checks `wp_has_ability()` and advertises only names actually registered, leaving incoming entries untouched.
- The five content abilities passed a top-level `type` property, which is not one of `WP_Ability`'s properties. Core discarded it and emitted a `_doing_it_wrong()` notice on every request that touched the registry. The tool/resource distinction now lives at `meta.mcp.type`, matching Post Formats for Block Themes.

### Removed

- The meta-backed `validate_relationships` ability. It took the same `rels` input as the content-backed `xfn/validate-relationships` and ran a strict subset of its checks, since content validation also rejects values outside the XFN 1.1 vocabulary. Dropped rather than renamed alongside the other four.

### Added

- `AbilitiesManagerTest` covers the MCP advertisement filter: only registered names are advertised, unregistered ones are dropped, and entries added by other plugins pass through untouched.
- `AbilitiesRegistrationTest` asserts every declared ability is present in `wp_get_abilities()` after init, that declared names are unique and satisfy core's grammar, and that the declared list and the registry agree in both directions.

### Planned Features

- User preferences for default collapsible states
- Customizable animation speeds
- Additional relationship validation rules
- REST API endpoint for XFN management
- Block pattern library with XFN examples
- Import/export functionality for XFN settings

## [1.0.4] - 2026-07-10

> **Version renumbering.** WordPress.org never published 1.1.0 (its listing continues from 1.0.3), so the public line resumes at 1.0.4. The 1.1.0 entry below records a GitHub-only release; everything in it ships to WordPress.org as part of 1.0.4.

### Fixed

- readme.txt header uses the standard `Contributors:` field — the nonstandard `Developers:` field kept WordPress.org from showing the plugin author
- The editor scripts declared by the Blogroll, Relationship Badge, and Relationship Directory blocks (`blocks/*/index.js`) now actually build and ship — every `block.json` declared an `editorScript`, but the old webpack config never emitted them
- Build system rebuilt on `@wordpress/scripts` native script-modules support (`WP_EXPERIMENTAL_MODULES`) — the hand-rolled config shared plugin instances between the script and module passes, forcing the script compile into module mode and breaking clean builds
- `deploy-to-wordpress-org.sh` reads the release version from readme.txt's stable tag instead of a hardcoded value

### Removed

- The "Floating Toolbar Button" setting. It was advertised from 1.0.x but the toolbar button it promised was never implemented — the checkbox did nothing. The Link Advanced panel (always on) and the Inspector Controls panel cover the same relationships. A leftover `enable_floating_toolbar` key in `xfn_link_extension_options` is ignored.
- The unused `xfn_link_extension` nonce from the editor script's localized data — no endpoint ever verified it.

### Changed

- Requires WordPress 6.9 or newer (script modules and the guarded Abilities API integration need it); tested up to 7.0
- The distributed plugin ships compiled build output only; uncompiled source stays in the GitHub repository

## [1.1.0] - 2026-07-03

### Added

- **Outpost mp-xfn bridge adapter** — consumes the `_outpost_xfn` post meta written by Outpost's Micropub endpoint, mirrors relationships into XFN metadata, and syncs `rel` attributes into post content immediately on save (completes the XFN wire-through)

### Fixed

- **Tooltips restored** — the Interactivity view module (`build/interactivity/tooltip.js`) was never built or committed, leaving tooltips inert on every 1.0.3 install; the built module now ships with the plugin
- Anchors nested inside multiple block levels were wrapped with duplicate tooltip markup, once per enclosing block
- `wp_http_validate_url()` misuse in the meta mirror dropped relationships for unresolvable hosts and offline saves

### Technical

- Canonical WordPress coding-standards ruleset adopted repo-wide, with a real PHPStan gate in CI

## [1.0.3] - 2024-12-09

### Fixed

- **WordPress Playground blueprint now works correctly**
  - Fixed JSON structure to use `runPHPWithOptions` with environment variables
  - Disabled WordPress content filters to preserve XFN rel attributes
  - Changed demo page to published status (was draft)
  - Set landing page to `/xfn-demo/` for immediate demo access
  - Added `login: true` for automatic authentication
  - Button blocks now correctly include rel attribute in rendered HTML (not just metadata)

### Improved

- **Enhanced demo content**
  - Added visual indicators (emojis) for better readability
  - Included comprehensive testing instructions
  - Added table showing expected XFN results
  - Improved inline link examples with multiple XFN attributes
  - Better explanation of how to inspect XFN in browser DevTools

### Technical

- Blueprint now uses proper WordPress Playground best practices
- Content preserved exactly as authored without filter modifications
- CORS-enabled development server for local blueprint testing

## [1.0.2] - 2024-12-09

### Added

- **Embed block support**: XFN relationships now work with all embed blocks (YouTube, Twitter, WordPress, etc.)
  - Added `core/embed` to supported blocks list in Inspector Controls
  - Server-side filter injects XFN attributes into frontend HTML
  - Embeds with links get `rel="..."` attribute
  - Embeds without links get `data-xfn-rel="..."` on figure element
- **WordPress Playground blueprint**: Live preview now available on WordPress.org
  - Comprehensive demo post with inline links, buttons, and embeds
  - Auto-enables Inspector Controls for immediate testing
  - Shows multiple XFN attributes on various block types
  - Includes instructions for inspecting XFN in browser
- **Documentation updates**: All docs now mention embed block support
  - Updated readme.txt and README.md
  - Added embed block section to "Integration with Popular Blocks"
  - Updated Quick Start guides to include embed blocks

### Fixed

- Blueprint.json now in correct location (`assets/blueprints/`) for WordPress.org
- Embed blocks now properly output XFN relationships to frontend HTML

### Technical

- Added `xfn_render_embed_block()` filter function
- Hooks into `render_block` to modify embed output
- Properly sanitizes and combines XFN with existing rel attributes
- Handles both linked and non-linked embed types

## [1.0.0] - 2024-12-08

### Added

- **Initial release** of Link Extension for XFN
- **Comprehensive user documentation** in `docs/` directory:
  - Getting Started guide with XFN overview and quick start
  - Plugin Settings guide for configuring interface options
  - Paragraph Links guide for inline text links
  - Button Links guide for Button block XFN
  - Image Links guide for clickable images
  - Other Block Links guide for Navigation, Site Logo, and more
- **Complete XFN 1.1 specification support** with all relationship categories:
  - Friendship relationships (contact, acquaintance, friend)
  - Physical relationships (met)
  - Professional relationships (co-worker, colleague)
  - Geographical relationships (co-resident, neighbor)
  - Family relationships (child, parent, sibling, spouse, kin)
  - Romantic relationships (muse, crush, date, sweetheart)
  - Identity relationships (me)
- **Triple interface integration** for maximum flexibility:
  - Floating Toolbar with XFN button and collapsible interface
  - Inspector Controls with comprehensive relationship panels
  - Link Advanced Panel with collapsible XFN section
- **Modern collapsible interface design** with:
  - Smooth expand/collapse animations
  - Count badges showing active relationship numbers
  - Visual relationship pills with color coding
  - Clean toggle buttons matching WordPress design patterns
- **Intelligent relationship management**:
  - Button groups for mutually exclusive relationships
  - Multi-selection support for compatible relationships
  - Real-time validation preventing invalid combinations
  - Automatic mutual exclusivity enforcement (friendship, geographical, family categories)
- **Seamless WordPress integration**:
  - Works with all link-supporting blocks (Paragraph, Button, Navigation, List, etc.)
  - Compatible with Post Editor and Site Editor
  - Preserves existing rel attributes (nofollow, noopener, noreferrer)
  - No configuration required - works immediately after activation
- **Accessibility excellence**:
  - Full keyboard navigation (Tab, Space, Enter, Arrow keys)
  - Comprehensive ARIA labels and descriptions
  - Screen reader support (NVDA, JAWS, VoiceOver tested)
  - High contrast mode compatibility
  - Proper focus management and visual indicators
  - WCAG 2.2 AA compliance
- **Performance optimization**:
  - Lightweight JavaScript bundle under 15KB gzipped
  - Lazy-loaded collapsible sections
  - No frontend performance impact (editor-only)
  - Uses WordPress core components for consistency
  - Smooth CSS animations without performance penalties
- **Developer-friendly features**:
  - Helper functions for relationship management
  - Rel attribute parsing and combination utilities
  - Relationship validation functions
  - Sanitization functions for security
  - Clean, well-documented code following WordPress standards
- **Internationalization**:
  - Translation-ready with all strings wrapped in translation functions
  - Text domain: `link-extension-for-xfn`
  - Support for WordPress translation system
- **Security features**:
  - Proper escaping of all output
  - Nonce verification for AJAX requests
  - Validation of relationship combinations
  - Sanitization of rel attribute values
  - Capability checks for admin functions

### Fixed

- **Panel synchronization** - Inspector sidebar and floating toolbar now display identical XFN values for block-level links
- **Block attribute reading** - Button, Image, and other block-level links now correctly read rel attributes from block attributes instead of HTML content
- **Inspector panel visibility** - XFN Relationships panel now opens by default for Button, Image, and Navigation blocks for improved discoverability

### Changed

- **Enhanced source code documentation** - Updated readme.txt with explicit documentation about uncompiled source code location, build tools, and GitHub repository
- **User documentation** - Replaced single USER-GUIDE.md with comprehensive docs/ directory containing separate guides for each link type

### Technical Implementation

- **PHP Version**: 7.4+ required
- **WordPress Version**: 6.4+ required
- **Build System**: @wordpress/scripts v30.15.0
- **Components**: Uses WordPress core UI components
- **Data Storage**: Standard HTML rel attributes (no custom tables)
- **Architecture**: Singleton pattern with clean separation of concerns

### Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Modern browsers with ES6 support

### Block Compatibility

Tested and confirmed working with:

- Paragraph block (inline links)
- Button block
- Navigation block
- List block
- Cover block
- Media & Text block
- All other blocks supporting the link interface

---

## Version Number Guidelines

This project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html):

- **MAJOR version (X.0.0)**: Incompatible API changes or breaking changes
- **MINOR version (1.X.0)**: New features added in a backward-compatible manner
- **PATCH version (1.0.X)**: Backward-compatible bug fixes

## Types of Changes

- **Added**: New features
- **Changed**: Changes to existing functionality
- **Deprecated**: Soon-to-be removed features
- **Removed**: Removed features
- **Fixed**: Bug fixes
- **Security**: Security improvements or fixes

## Reporting Issues

If you discover any bugs or have feature requests, please report them on:

- [GitHub Issues](https://github.com/courtneyr-dev/link-extension-for-xfn/issues)
- [WordPress.org Support Forum](https://wordpress.org/support/plugin/link-extension-for-xfn/)

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on how to contribute to this project.

---

[Unreleased]: https://github.com/courtneyr-dev/link-extension-for-xfn/compare/v1.0.3...HEAD
[1.0.3]: https://github.com/courtneyr-dev/link-extension-for-xfn/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/courtneyr-dev/link-extension-for-xfn/compare/v1.0.0...v1.0.2
[1.0.0]: https://github.com/courtneyr-dev/link-extension-for-xfn/releases/tag/v1.0.0
