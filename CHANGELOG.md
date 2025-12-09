# Changelog

All notable changes to the Link Extension for XFN will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned Features
- User preferences for default collapsible states
- Customizable animation speeds
- Additional relationship validation rules
- REST API endpoint for XFN management
- Block pattern library with XFN examples
- Import/export functionality for XFN settings

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
  - Text domain: `xfn-link-extension`
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

- [GitHub Issues](https://github.com/courtneyr-dev/xfn-link-extension/issues)
- [WordPress.org Support Forum](https://wordpress.org/support/plugin/xfn-link-extension/)

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on how to contribute to this project.

---

[Unreleased]: https://github.com/courtneyr-dev/xfn-link-extension/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/courtneyr-dev/xfn-link-extension/releases/tag/v1.0.0
