# Link Extension for XFN

Extends the native WordPress block editor link interface to include XFN (XHTML Friends Network) relationship options across all blocks that support links.

[![WordPress](https://img.shields.io/badge/WordPress-6.4%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## Overview

The Link Extension for XFN seamlessly integrates XFN (XHTML Friends Network) relationship options into WordPress's native link interface. This plugin enhances every block that supports links—including Paragraph, Button, Navigation, List, and more—with comprehensive relationship tagging capabilities through an intuitive collapsible interface.

XFN is a simple way to represent human relationships using hyperlinks. By adding XFN relationships to your links, you can indicate how you're connected to the people and organizations you link to, creating a more semantic and meaningful web.

## Features

### Complete XFN 1.1 Specification Support

This plugin implements the full XFN 1.1 specification with all relationship categories:

- **Friendship**: contact, acquaintance, friend
- **Physical**: met (have you met this person?)
- **Professional**: co-worker, colleague
- **Geographical**: co-resident, neighbor
- **Family**: child, parent, sibling, spouse, kin
- **Romantic**: muse, crush, date, sweetheart
- **Identity**: me (link to yourself)

### Dual Interface Integration

The plugin provides XFN controls in two convenient locations:

1. **Link Advanced Panel** (Always enabled) - Collapsible XFN section in the link popover for inline paragraph links
2. **Inspector Controls** (Optional) - Panel in the block sidebar for Button, Image, and Navigation blocks - opens by default when enabled

**Note:** Inspector Controls can be enabled in Settings → XFN Link Extension. The Link Advanced Panel is always available for inline links.

### Modern Collapsible Interface

- **Collapsible XFN sections** with clean toggle buttons and smooth animations
- **Visual relationship pills** showing active relationships at a glance
- **Count badges** indicating number of active relationships
- **Button groups** for easy relationship selection with mutual exclusivity support
- **Real-time validation** preventing invalid relationship combinations

## Try It Live

**[Try the plugin in WordPress Playground →](https://playground.wordpress.net/#%7B%22%24schema%22%3A%22https%3A%2F%2Fplayground.wordpress.net%2Fblueprint-schema.json%22%2C%22landingPage%22%3A%22%2Fwp-admin%2Fpost-new.php%22%2C%22preferredVersions%22%3A%7B%22php%22%3A%228.0%22%2C%22wp%22%3A%22latest%22%7D%2C%22phpExtensionBundles%22%3A%5B%22kitchen-sink%22%5D%2C%22features%22%3A%7B%22networking%22%3Atrue%7D%2C%22steps%22%3A%5B%7B%22step%22%3A%22login%22%2C%22username%22%3A%22admin%22%2C%22password%22%3A%22password%22%7D%2C%7B%22step%22%3A%22installPlugin%22%2C%22pluginZipFile%22%3A%7B%22resource%22%3A%22url%22%2C%22url%22%3A%22https%3A%2F%2Fgithub.com%2Fcourtneyr-dev%2Flink-extension-for-xfn%2Freleases%2Flatest%2Fdownload%2Flink-extension-for-xfn.zip%22%7D%7D%2C%7B%22step%22%3A%22activatePlugin%22%2C%22pluginPath%22%3A%22link-extension-for-xfn%2Flink-extension-for-xfn.php%22%7D%2C%7B%22step%22%3A%22runPHP%22%2C%22code%22%3A%22%3C%3Fphp%20require%20'%2Fwordpress%2Fwp-load.php'%3B%20wp_insert_post(array('post_title'%20%3D%3E%20'XFN%20Demo%20Post'%2C%20'post_content'%20%3D%3E%20'%3C!--%20wp%3Aparagraph%20--%3E%3Cp%3EThis%20is%20a%20demo%20post%20to%20showcase%20the%20XFN%20Link%20Extension%20plugin.%20Select%20any%20text%20and%20add%20a%20link%20to%20see%20XFN%20relationship%20options%20in%20action.%3C%2Fp%3E%3C!--%20%2Fwp%3Aparagraph%20--%3E%3C!--%20wp%3Aparagraph%20--%3E%3Cp%3ETry%20adding%20a%20link%20and%20exploring%20the%20XFN%20relationships%20panel!%3C%2Fp%3E%3C!--%20%2Fwp%3Aparagraph%20--%3E%3C!--%20wp%3Abuttons%20--%3E%3Cdiv%20class%3D%5C%22wp-block-buttons%5C%22%3E%3C!--%20wp%3Abutton%20--%3E%3Cdiv%20class%3D%5C%22wp-block-button%5C%22%3E%3Ca%20class%3D%5C%22wp-block-button__link%20wp-element-button%5C%22%20href%3D%5C%22https%3A%2F%2Fexample.com%5C%22%3EExample%20Button%20Link%3C%2Fa%3E%3C%2Fdiv%3E%3C!--%20%2Fwp%3Abutton%20--%3E%3C%2Fdiv%3E%3C!--%20%2Fwp%3Abuttons%20--%3E'%2C%20'post_status'%20%3D%3E%20'draft'))%3B%20%3F%3E%22%7D%5D%7D)**

Test the plugin instantly in your browser with no installation required. The demo environment includes sample content ready for you to explore XFN relationships.

## Screenshot

![Link Extension for XFN interface showing collapsible sections, relationship selection buttons organized by category, and visual relationship pills displaying active selections](.wordpress-org/screenshot-1.png)

*The XFN interface in action: Collapsible relationship categories with button groups for easy selection, and visual pills showing active relationships at a glance.*

## Documentation

Comprehensive user guides are available in the [`docs/`](docs/) directory:

- **[Getting Started](docs/README.md)** - Quick start guide and XFN overview
- **[Plugin Settings](docs/settings.md)** - Configure where XFN options appear
- **[Paragraph Links](docs/paragraph-links.md)** - Add XFN to inline text links
- **[Button Links](docs/button-links.md)** - Add XFN to Button blocks
- **[Image Links](docs/image-links.md)** - Add XFN to clickable images
- **[Other Block Links](docs/other-block-links.md)** - Navigation, Site Logo, and more

## Requirements

- **WordPress**: 6.4 or higher
- **PHP**: 7.4 or higher
- **Modern browser** with ES6 support

## Installation

### For Users

1. Download the plugin ZIP file from WordPress.org or GitHub releases
2. Go to WordPress admin → Plugins → Add New → Upload Plugin
3. Upload the ZIP file and click Install Now
4. Activate the plugin
5. XFN options will immediately appear in the block editor

### For Developers

```bash
# Clone the repository
git clone https://github.com/courtneyr-dev/xfn-link-extension.git
cd xfn-link-extension

# Install dependencies
npm install

# Build the plugin
npm run build

# Start development with watch mode
npm run start
```

## Usage

### Quick Start

After installation, XFN options are immediately available in the **Link Advanced Panel** for inline links (links within paragraphs, headings, lists, etc.).

To enable XFN for Button, Image, and Navigation blocks:
1. Go to **Settings → XFN Link Extension**
2. Enable **Inspector Controls** (recommended)
3. Click **Save Changes**

### Adding XFN to Inline Links (Paragraphs, Lists, etc.)

1. Select text and create a link (Cmd/Ctrl+K)
2. Click the link to open the popover
3. Click "Advanced" to expand advanced options
4. Find the "XFN" section and expand it
5. Select your relationships
6. Click "Apply" to save

### Adding XFN to Button/Image/Navigation Blocks

With Inspector Controls enabled:
1. Select the Button/Image/Navigation block
2. Look in the right sidebar (Inspector)
3. Find "XFN Relationships" panel (opens by default)
4. Select relationships - they save automatically

## Development

### Project Structure

```
xfn-link-extension/
├── xfn-link-extension.php  # Main plugin file
├── readme.txt               # WordPress.org documentation
├── README.md                # GitHub documentation (this file)
├── CHANGELOG.md             # Version history
├── LICENSE                  # GPL v2+ license
├── package.json             # Node dependencies
├── .gitignore              # Git exclusions
├── .distignore             # Distribution exclusions
│
├── src/                    # Source files (not in ZIP)
│   ├── index.js           # Main JavaScript entry point
│   ├── edit.js            # Block edit component
│   ├── save.js            # Block save component
│   ├── view.js            # Frontend JavaScript
│   ├── editor.scss        # Editor styles
│   ├── style.scss         # Frontend styles
│   └── block.json         # Block metadata
│
├── build/                 # Compiled assets (in ZIP)
│   ├── index.js          # Compiled JavaScript
│   ├── index.css         # Compiled CSS
│   ├── editor.css        # Compiled editor styles
│   └── block.json        # Block metadata (copy)
│
└── .wordpress-org/        # WordPress.org assets (not in ZIP)
    ├── icon-256x256.png
    ├── banner-772x250.png
    ├── banner-1544x500.png
    ├── screenshot-1.png
    └── ...
```

### Build Commands

```bash
# Development build with watch mode
npm run start

# Production build (minified)
npm run build

# Lint JavaScript
npm run lint:js

# Lint CSS
npm run lint:css

# Format code
npm run format

# Create plugin ZIP
npm run plugin-zip
```

### Coding Standards

This plugin follows WordPress coding standards:

- **PHP**: [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- **JavaScript**: [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- **CSS**: [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)

### Architecture

The plugin uses a singleton pattern for the main class and hooks into WordPress's block editor via JavaScript filters:

- **PHP Class**: `XFN_Link_Extension` handles plugin initialization, asset enqueuing, and server-side functionality
- **JavaScript**: Extends the block editor's `BlockEdit` component to add XFN controls
- **Hooks System**: Uses WordPress hooks to integrate with the link interface without modifying core functionality

### Key Functions

#### PHP Functions

```php
// Get XFN relationship definitions
$relationships = xfn_get_relationships();

// Parse rel attribute to separate XFN from other values
$parsed = xfn_parse_rel_attribute( 'friend met nofollow' );
// Returns: ['xfn' => ['friend', 'met'], 'other' => ['nofollow']]

// Combine XFN and other rel values
$rel = xfn_combine_rel_values( ['friend', 'met'], ['nofollow'] );
// Returns: 'nofollow friend met'

// Validate XFN relationship combinations
$valid = xfn_validate_relationships( ['friend', 'acquaintance'] );
// Returns: false (mutually exclusive)

// Sanitize rel attribute value
$clean_rel = xfn_sanitize_rel_attribute( 'friend  met   nofollow' );
// Returns: 'nofollow friend met'
```

## Developer Hooks

This plugin provides hooks for extensibility:

### Filters

Currently, the plugin uses WordPress core filters. Custom filters may be added in future versions for:

- Customizing relationship definitions
- Modifying available interfaces
- Controlling default states
- Customizing UI elements

### Actions

Currently, the plugin uses WordPress core actions. Custom actions may be added in future versions for:

- Relationship selection events
- Validation events
- Interface state changes

## Technical Details

### Rel Attribute Management

The plugin intelligently manages the HTML `rel` attribute:

- Combines XFN relationships with existing rel values (nofollow, noopener, noreferrer)
- Validates relationship combinations according to XFN specification
- Preserves SEO-important and security-related attributes
- Uses space-separated format per HTML specification

### Data Persistence

- Relationships are stored in the standard HTML `rel` attribute
- No custom database tables or meta fields required
- Compatible with all WordPress import/export tools
- Relationships survive theme changes and plugin deactivation

### Performance

- Lightweight JavaScript bundle under 15KB gzipped
- Only loads in the block editor (no frontend impact)
- Lazy-loaded collapsible sections
- Uses WordPress core components for consistency
- Smooth CSS animations without performance penalties

### Browser Compatibility

- Modern browsers with ES6 support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Testing

### Manual Testing

1. Activate the plugin in a WordPress 6.4+ installation
2. Create a new post or edit an existing one
3. Add blocks with links (Paragraph, Button, Navigation, List, etc.)
4. Test XFN controls in all three interfaces:
   - Floating toolbar
   - Inspector controls
   - Link advanced panel
5. Verify relationships appear in published HTML `rel` attribute
6. Test keyboard navigation and screen reader support
7. Test with Query Monitor active to check for errors

### Compatibility Testing

Test with popular plugins:
- Yoast SEO
- Rank Math
- Jetpack
- WooCommerce
- Contact Form 7

Test with popular themes:
- Twenty Twenty-Four (default block theme)
- Blocksy
- Kadence
- GeneratePress
- Astra

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Write tests if applicable
5. Ensure code follows WordPress coding standards
6. Test thoroughly
7. Commit your changes (`git commit -m 'Add amazing feature'`)
8. Push to the branch (`git push origin feature/amazing-feature`)
9. Open a Pull Request

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## Support

- **WordPress.org Support Forum**: [Plugin Support](https://wordpress.org/support/plugin/link-extension-for-xfn/)
- **GitHub Issues**: [Report bugs or request features](https://github.com/courtneyr-dev/xfn-link-extension/issues)
- **Documentation**: [WordPress.org Plugin Page](https://wordpress.org/plugins/link-extension-for-xfn/)

## License

This plugin is licensed under GPL v2 or later.

Copyright (c) 2024 Courtney Robertson

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

## Credits

- **Author**: Courtney Robertson
- **WordPress.org**: courane01
- **Contributors**: [List contributors here]
- **XFN Specification**: [GMPG XFN](http://gmpg.org/xfn/)
- **Built with**: [@wordpress/scripts](https://www.npmjs.com/package/@wordpress/scripts)

## Related Links

- [XFN 1.1 Specification](http://gmpg.org/xfn/11)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [WCAG 2.2 Guidelines](https://www.w3.org/WAI/WCAG22/quickref/)
