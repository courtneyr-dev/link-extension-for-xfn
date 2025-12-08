=== Link Extension for XFN ===

Contributors:      courane01
Tags:              xfn, links, relationships, accessibility, gutenberg
Tested up to:      6.9
Stable tag:        1.0.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 6.4
Requires PHP:      7.4

Add XFN (XHTML Friends Network) relationship metadata to WordPress links. Semantic social connections for the block editor.

== Description ==

The XFN Relationship Link Extension seamlessly integrates XFN (XHTML Friends Network) relationship options into WordPress's native link interface. This plugin enhances every block that supports links—including Paragraph, Button, Navigation, List, and more—with comprehensive relationship tagging capabilities through an intuitive collapsible interface.

XFN is a simple way to represent human relationships using hyperlinks. By adding XFN relationships to your links, you can indicate how you're connected to the people and organizations you link to, creating a more semantic and meaningful web.

## Key Features

### Complete XFN 1.1 Specification Support
This plugin implements the full XFN 1.1 specification with all relationship categories:

* **Friendship relationships**: contact, acquaintance, friend
* **Physical relationships**: met (have you met this person?)
* **Professional relationships**: co-worker, colleague
* **Geographical relationships**: co-resident, neighbor
* **Family relationships**: child, parent, sibling, spouse, kin
* **Romantic relationships**: muse, crush, date, sweetheart
* **Identity relationships**: me (link to yourself)

### Triple Interface Integration with Collapsible Design
The plugin provides XFN controls in three convenient locations, all featuring a clean collapsible interface:

* **Floating Toolbar**: Click the "XFN" button in the block toolbar for direct access with expandable XFN section
* **Inspector Controls**: Traditional WordPress panel in the block sidebar with full XFN categorization
* **Link Advanced Panel**: Collapsible XFN section alongside existing link options in popovers

### Seamless Integration with WordPress
The plugin extends the existing link interfaces without disrupting your workflow:

* Works with Paragraph, Button, Navigation, List, and all other link-supporting blocks
* Compatible with both Post Editor and Site Editor
* Preserves existing rel attributes (nofollow, noopener, noreferrer)
* No configuration required - works immediately after activation
* Clean collapsible interface matches WordPress design patterns

### Advanced User Interface with Collapsible Sections
The relationship selection interface is designed for optimal usability with modern collapsible design:

* **Collapsible XFN sections**: Clean toggle buttons with expand/collapse animation
* **Visual relationship pills**: Color-coded indicators show active relationships at a glance
* **Count badges**: Quickly see how many relationships are active without expanding
* **Intelligent grouping**: Related options are logically organized by category within sections
* **Button groups**: Modern toggle-style buttons for easy relationship selection
* **Mutual exclusivity**: Button groups prevent conflicting relationships (e.g., can't be both "friend" and "acquaintance")
* **Multi-selection support**: Checkbox-style groups allow multiple compatible relationships
* **Real-time validation**: Prevents invalid relationship combinations
* **Smooth animations**: Fluid expand/collapse transitions

## How to Add XFN Relationships to Links

### Method 1: Using the Floating Toolbar (Recommended)
1. Select a block that contains a link (Button, Navigation Link, etc.)
2. Look for the "XFN" button in the floating block toolbar
3. Click the XFN button to open the relationship selector popover
4. Click the "XFN" toggle to expand the collapsible section
5. Choose your relationships using the button groups within categories
6. Active relationships appear as colored pills in the summary
7. Click outside the popover to save your changes

### Method 2: Using Inspector Controls
1. Select a block that contains a link
2. Open the block settings panel (sidebar)
3. Find the "XFN Relationships" panel and expand it
4. Select relationships using radio buttons and checkboxes organized by category
5. View active relationships in the summary section with color-coded pills

### Method 3: Using Link Advanced Panel with Collapsible XFN
1. Create or edit an inline link in any block (Paragraph, List, etc.)
2. In the link popover, click "Advanced" to expand additional options
3. Find the "XFN" collapsible section and click to expand
4. Select relationships using the organized button groups
5. See your active relationships in the summary pills
6. Save the link to apply your XFN relationships

## Understanding XFN Relationship Categories

### Friendship Relationships (Choose One)
These are mutually exclusive - you can only select one friendship level per person:

* **contact**: Someone you know how to get in touch with
* **acquaintance**: Someone you've met but don't know well
* **friend**: Someone you consider a friend

### Physical Meeting
* **met**: Check this if you've met this person face-to-face

### Professional Relationships (Multiple Allowed)
* **co-worker**: Someone you work with at the same company
* **colleague**: Someone in your professional field or industry

### Geographical Relationships (Choose One)
* **co-resident**: Someone who lives in the same residence
* **neighbor**: Someone who lives nearby

### Family Relationships (Choose One)
* **child**: Your child
* **parent**: Your parent
* **sibling**: Your brother or sister
* **spouse**: Your husband, wife, or domestic partner
* **kin**: Other family relation

### Romantic Relationships (Multiple Allowed)
* **muse**: Someone who inspires you
* **crush**: Someone you have romantic feelings for
* **date**: Someone you're dating
* **sweetheart**: Someone you have a romantic relationship with

### Identity
* **me**: A link to your own content or profiles

## Integration with Popular Blocks

### Button Block
Use the floating toolbar XFN button for quick relationship assignment. The collapsible interface lets you quickly expand the XFN section, select relationships, and see a summary without clutter.

### Navigation Block
Enhance your site navigation with relationship context using the Inspector Controls. Mark links to your social profiles, partner sites, or affiliated organizations with the organized collapsible interface.

### Paragraph Block (Inline Links)
For inline links within paragraph content, use the collapsible XFN section in the link popover's Advanced area to add relationship context without overwhelming the interface.

### List Block
Build relationship-rich link lists using either the floating toolbar or Inspector Controls. The collapsible sections make it easy to manage multiple relationships across list items.

### Cover Block and Media & Text Block
Add relationship context to overlay links and media-associated links using any of the three interface methods, all featuring the clean collapsible design.

## Technical Implementation

### Source Code

The plugin uses build tools to compile and optimize the JavaScript for production. The complete, uncompiled source code is available in the `src/` directory of the plugin:

* **Source files**: `src/index.js`, `src/view.js`, `src/edit.js`, `src/save.js`
* **Build output**: `build/index.js`, `build/view.js`
* **Build tool**: WordPress Scripts (webpack-based)
* **Build command**: `npm run build` (requires Node.js 14+)

To rebuild from source:
1. Install dependencies: `npm install`
2. Build production files: `npm run build`
3. Or use development mode: `npm start` (watches for changes)

All source code is maintained at: https://github.com/courtneyr-dev/xfn-link-extension

### Rel Attribute Management
The plugin intelligently manages the HTML rel attribute:

* Combines XFN relationships with existing rel values
* Preserves SEO-important attributes like nofollow
* Maintains security attributes like noopener and noreferrer
* Uses space-separated format per HTML specification
* Validates relationship combinations to prevent conflicts

### Data Persistence
* Relationships are stored in the standard HTML rel attribute
* No custom database tables or meta fields required
* Compatible with all WordPress import/export tools
* Relationships survive theme changes and plugin deactivation

### Performance Optimized
* Lightweight JavaScript bundle under 12KB gzipped
* Lazy-loaded collapsible sections only when accessed
* No impact on frontend performance
* Uses WordPress core components for consistency
* Smooth animations without performance penalties

## Migration from Other XFN Plugins

If you're migrating from another XFN plugin:

1. **Classic Editor plugins**: Existing XFN data in post meta will be preserved
2. **Custom rel attributes**: Any existing rel values are maintained
3. **Manual XFN**: Manually entered XFN relationships in rel attributes work immediately

## Frequently Asked Questions

### Where can I access XFN relationship options?

XFN controls are available in three locations, all featuring collapsible interfaces:
1. **Floating Toolbar**: Click the "XFN" button in the block toolbar, then expand the XFN section
2. **Inspector Controls**: In the block settings sidebar under "XFN Relationships" panel
3. **Link Advanced Panel**: In the Advanced section of inline link popovers as a collapsible "XFN" section

### Why use a collapsible interface?

The collapsible design keeps the interface clean and uncluttered while providing full access to XFN features:
* **Count badges** show active relationships at a glance
* **Expand only when needed** to reduce visual noise
* **Consistent with WordPress design** patterns
* **Accessible** with proper ARIA states
* **Mobile friendly** with touch-optimized toggles

### What is XFN and why should I use it?

XFN (XHTML Friends Network) is a simple way to represent human relationships using hyperlinks. It adds semantic meaning to your links by describing how you're connected to the people and organizations you link to. This creates a richer, more meaningful web where relationships between people and content are explicit.

### Does the collapsible interface affect accessibility?

No, the collapsible interface enhances accessibility by:
* **Reducing cognitive load** by showing only relevant information
* **Providing proper ARIA states** for screen readers
* **Supporting full keyboard navigation** including expand/collapse
* **Maintaining focus management** when sections open and close
* **Offering visual and semantic cues** about content state

### Does this affect my SEO or page speed?

No negative impact on either. The plugin adds standard HTML rel attributes that search engines understand. The JavaScript only loads in the WordPress editor, not on your public pages. The collapsible interface has no impact on frontend performance.

### Can I use this with Classic Editor?

This plugin is designed for the Gutenberg block editor (WordPress 5.0+). For Classic Editor, you would need a different XFN plugin or manually add rel attributes to your links.

### What happens if I deactivate the plugin?

Your XFN relationships remain in your content as standard rel attributes. They just won't be editable through the collapsible user interface anymore. Your content is never damaged or modified.

### Can I control which sections are expanded by default?

Currently, all collapsible sections start in the collapsed state to maintain a clean interface. We're considering user preferences for default states in future versions.

### Does this work with all themes?

Yes! XFN relationships are standard HTML attributes that work with any theme. The editing interface uses WordPress core components and follows WordPress design patterns for consistency across all admin themes.

### Can I customize the collapsible behavior?

The current version provides the standard collapsible behavior. We're considering customization options for animation speed and default states based on user feedback.

### Is this compatible with page builders?

This plugin works with the native WordPress block editor. Compatibility with third-party page builders depends on whether they use WordPress's standard link interface and support collapsible components.

### How do I see the XFN relationships on my published pages?

XFN relationships appear in the HTML rel attribute of links. You can view them by inspecting the page source or using browser developer tools. They're primarily for semantic markup rather than visual display.

### Can I use this for business relationships?

Absolutely! XFN includes professional relationships (co-worker, colleague) and can be used to indicate business partnerships, vendor relationships, and professional networks. The collapsible interface makes it easy to manage multiple professional relationships.

## Troubleshooting

### XFN options don't appear in floating toolbar

1. **Check block type**: Only blocks that are entirely links (Button, Navigation Link, etc.) show the XFN toolbar button
2. **Verify WordPress version**: Requires WordPress 6.4 or later
3. **Clear browser cache**: Try refreshing the page or clearing browser cache
4. **Check for block selection**: Ensure the block is properly selected

### XFN section won't expand in link popover

1. **Check Advanced section**: XFN options appear when you click "Advanced" in the link popover, then look for the "XFN" toggle
2. **Try clicking the toggle**: The XFN section requires an additional click to expand
3. **Verify in block editor**: Plugin only works with Gutenberg blocks, not Classic Editor
4. **Check browser console**: Look for JavaScript errors that might prevent interaction

### Collapsible sections not animating smoothly

1. **Check browser compatibility**: Ensure you're using a modern, supported browser
2. **Verify CSS loading**: Check that plugin stylesheets are loading properly
3. **Test in incognito mode**: Rule out browser extension interference
4. **Disable other plugins**: Test for plugin conflicts by temporarily disabling other plugins

### Count badges not updating

1. **Try refreshing**: Sometimes a page refresh resolves display issues
2. **Check JavaScript console**: Look for errors that might prevent updates
3. **Test relationship changes**: Make sure you're actually changing relationships
4. **Verify plugin activation**: Ensure the plugin is properly activated

### Relationships not saving properly

1. **Check browser console**: Look for JavaScript errors that might prevent saving
2. **Verify user permissions**: Ensure you have permission to edit the content
3. **Test with default theme**: Temporarily switch to a default WordPress theme
4. **Disable other plugins**: Test for plugin conflicts by temporarily disabling other plugins

### Keyboard navigation issues with collapsible sections

1. **Test tab order**: Use Tab key to navigate to toggle buttons
2. **Try space/enter**: Space or Enter should expand/collapse sections
3. **Use arrow keys**: Arrow keys work within expanded button groups
4. **Check browser settings**: Ensure keyboard navigation is enabled in browser settings

### Screen reader compatibility with collapsible interface

1. **Test with multiple screen readers**: NVDA, JAWS, and VoiceOver are all supported
2. **Check expansion announcements**: Section state changes should be announced
3. **Verify ARIA states**: All collapsible elements should have proper ARIA attributes
4. **Update screen reader**: Ensure you're using a current version of your screen reader

## Changelog

= 1.0.0 =
* Initial release with full XFN 1.1 specification support
* Triple interface integration: Floating Toolbar, Inspector Controls, and Link Advanced Panel
* Modern collapsible interface design with smooth animations
* Count badges for quick relationship status overview
* Seamless integration with WordPress native link interface
* Complete accessibility implementation with WCAG 2.2 AA compliance
* Support for all link-supporting blocks in Gutenberg editor
* Visual relationship pills with color-coded categories
* Intelligent mutual exclusivity and multi-selection handling
* Comprehensive keyboard navigation and screen reader support
* Collapsible sections with proper ARIA states and focus management
* Performance optimized with lazy loading and minimal bundle size
* Preservation of existing rel attributes (nofollow, noopener)
* Extensive internationalization with translation-ready strings
* Responsive design with mobile-friendly collapsible toggles
* High contrast mode support for enhanced accessibility

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/xfn-link-extension` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. No configuration required - XFN options will immediately appear in:
   - The floating toolbar for link blocks (Button, Navigation, etc.) with an expandable XFN section
   - The Inspector Controls sidebar for supported blocks with organized panels
   - The Advanced section of link popovers for inline links as a collapsible XFN section
4. Start adding relationship context to your links using the intuitive collapsible interface across all blocks that support links

== Screenshots ==

1. XFN relationship interface showing the floating toolbar with collapsible sections and relationship selection options