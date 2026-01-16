# Link Extension for XFN - User Guide

## Table of Contents

1. [Introduction](#introduction)
2. [What is XFN?](#what-is-xfn)
3. [Installation](#installation)
4. [Getting Started](#getting-started)
5. [Using XFN Relationships](#using-xfn-relationships)
6. [Interface Locations](#interface-locations)
7. [Understanding Relationship Types](#understanding-relationship-types)
8. [Step-by-Step Tutorials](#step-by-step-tutorials)
9. [Best Practices](#best-practices)
10. [Troubleshooting](#troubleshooting)
11. [FAQs](#faqs)

---

## Introduction

The Link Extension for XFN makes it easy to add semantic relationship metadata to your WordPress links. This plugin integrates seamlessly with the WordPress block editor (Gutenberg) to help you describe your connections to the people and organizations you link to.

### Who is this plugin for?

- **Bloggers** who want to indicate relationships with linked sites
- **Content creators** building semantic web connections
- **Developers** implementing microformats in WordPress
- **SEO professionals** enhancing link metadata
- **Community builders** creating relationship networks

---

## What is XFN?

**XFN (XHTML Friends Network)** is a simple way to represent human relationships using hyperlinks. It's a microformat that uses the HTML `rel` attribute to describe how you know someone or your connection to an organization.

### Why use XFN?

- **Semantic Web**: Makes your links more meaningful to search engines and readers
- **Social Networking**: Creates a distributed social network across websites
- **Trust Signals**: Indicates your relationship with linked content
- **Compatibility**: Works alongside other `rel` attributes (nofollow, noopener, etc.)

### XFN in Action

Without XFN:
```html
<a href="https://example.com">John Smith</a>
```

With XFN:
```html
<a href="https://example.com" rel="friend met">John Smith</a>
```

This tells browsers and search engines that John Smith is a friend you've met in person.

---

## Installation

### From WordPress Admin

1. Go to **Plugins** → **Add New**
2. Search for "Link Extension for XFN"
3. Click **Install Now**
4. Click **Activate**

That's it! The plugin works immediately—no configuration needed.

### Manual Installation

1. Download the plugin ZIP file
2. Go to **Plugins** → **Add New** → **Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Click **Activate**

### From GitHub

1. Download from https://github.com/courtneyr-dev/xfn-link-extension
2. Extract to `/wp-content/plugins/xfn-link-extension/`
3. Go to **Plugins** and activate

---

## Getting Started

### First Steps

After activation, XFN controls appear automatically in three locations whenever you work with links in the block editor:

1. **Floating Toolbar** - Quick access button
2. **Inspector Controls** - Block settings sidebar
3. **Link Advanced Panel** - Inline link popover

No setup or configuration is required. Just start adding links as you normally would!

### Quick Start Example

Let's add a link with XFN relationships:

1. Create a new post or page
2. Add a Paragraph block
3. Type some text and select it
4. Click the **Link** button
5. Enter a URL and press Enter
6. Click **Advanced** in the link popover
7. Expand the **XFN** section
8. Select relationships (e.g., "Friend" and "Met")
9. Click outside the popover to save

Your link now has XFN metadata!

---

## Using XFN Relationships

### The Three Access Methods

#### Method 1: Floating Toolbar (Recommended for Block Links)

**Best for**: Button blocks, Navigation blocks, and block-level links

1. Select any block containing a link (Button, Navigation Link, etc.)
2. Look for the **XFN** button in the block toolbar
3. Click the XFN button to open the relationship selector
4. Click the **XFN** toggle header to expand the collapsible section
5. Select relationships using the button groups
6. View active relationships as colored pills in the summary
7. Click outside to save

**Advantages**:
- Quick access from the toolbar
- Visual relationship pills
- Count badges show active relationships at a glance
- Clean collapsible interface

#### Method 2: Inspector Controls (Recommended for Detailed Selection)

**Best for**: Careful selection, reviewing relationships, working with multiple options

1. Select a block that contains a link
2. Open the **Block** tab in the right sidebar
3. Scroll to find the **XFN Relationships** panel
4. Click to expand the panel
5. Select relationships organized by category:
   - Friendship (radio buttons - choose one)
   - Physical (checkbox)
   - Professional (checkboxes - multiple allowed)
   - Geographical (radio buttons - choose one)
   - Family (radio buttons - choose one)
   - Romantic (checkboxes - multiple allowed)
   - Identity (checkbox)
6. View active relationships in the summary section

**Advantages**:
- Organized by category
- Clear descriptions for each category
- Shows validation rules (choose one vs. multiple)
- Always accessible in sidebar

#### Method 3: Link Advanced Panel (Recommended for Inline Links)

**Best for**: Links within paragraph text, list items, and other inline content

1. Create or select inline text
2. Click the **Link** button
3. Enter or edit the URL
4. Click **Advanced** at the bottom of the link popover
5. Find the **XFN** collapsible section
6. Click to expand XFN options
7. Select relationships using button groups
8. See active relationships in summary pills
9. Press Enter or click outside to save

**Advantages**:
- Integrated with native link interface
- Doesn't disrupt writing flow
- Compact collapsible design
- Same powerful features as other methods

---

## Interface Locations

### Understanding the UI Elements

#### Count Badges
Small circular badges showing the number of active relationships:
```
XFN (3)  ← Badge shows 3 relationships selected
```

#### Relationship Pills
Color-coded visual indicators for active relationships:
```
[friend] [met] [colleague]  ← Visual pills
```

#### Toggle Buttons
Expand/collapse controls with chevron icons:
```
▼ XFN (2)  ← Expanded
▶ XFN (2)  ← Collapsed
```

#### Button Groups
Toggle-style buttons for relationship selection:
```
○ Contact   ● Friend   ○ Acquaintance
```
- Empty circle = not selected
- Filled circle = selected

---

## Understanding Relationship Types

### Friendship Relationships (Choose ONE)

These describe your level of friendship with a person. You can only select one per link.

| Value | Meaning | Example Use |
|-------|---------|-------------|
| **contact** | Someone you know how to reach | A business contact whose email you have |
| **acquaintance** | Someone you've met but don't know well | Person you met at a conference once |
| **friend** | Someone you consider a friend | Your actual friend or close colleague |

**Mutual Exclusivity**: You can't be both "friend" and "acquaintance" with someone—pick the closest match.

---

### Physical Relationship

| Value | Meaning | Example Use |
|-------|---------|-------------|
| **met** | You've met this person face-to-face | Check this for anyone you've met in real life |

**Note**: This can be combined with any other relationship.

---

### Professional Relationships (Multiple Allowed)

These describe work-related connections. You can select both if applicable.

| Value | Meaning | Example Use |
|-------|---------|-------------|
| **co-worker** | Works at the same organization | Someone at your company |
| **colleague** | Works in your professional field | Someone at a different company in your industry |

**Example**: A developer at your company who you work with directly = "co-worker" + "colleague"

---

### Geographical Relationships (Choose ONE)

These describe physical proximity. You can only select one.

| Value | Meaning | Example Use |
|-------|---------|-------------|
| **co-resident** | Lives in the same residence | Roommate, housemate, family member in same home |
| **neighbor** | Lives nearby | Someone on your street or in your building |

---

### Family Relationships (Choose ONE)

These describe family connections. You can only select one per link.

| Value | Meaning | Example Use |
|-------|---------|-------------|
| **child** | Your child | Son, daughter, stepchild |
| **parent** | Your parent | Mother, father, stepparent |
| **sibling** | Your brother or sister | Brother, sister, stepsibling |
| **spouse** | Your husband, wife, or partner | Married partner or domestic partner |
| **kin** | Other family relation | Cousin, aunt, uncle, grandparent |

---

### Romantic Relationships (Multiple Allowed)

These describe romantic connections. Multiple can apply to the same person.

| Value | Meaning | Example Use |
|-------|---------|-------------|
| **muse** | Someone who inspires you | Artist, writer, or creator who inspires you |
| **crush** | Someone you have romantic feelings for | Unrequited interest |
| **date** | Someone you're dating | Current dating relationship |
| **sweetheart** | Romantic partner | Boyfriend, girlfriend, significant other |

**Example**: Your partner could be "sweetheart" + "muse" if they also inspire you.

---

### Identity Relationship

| Value | Meaning | Example Use |
|-------|---------|-------------|
| **me** | Link to your own content or profile | Links to your other websites, social profiles |

**Example**: Linking from your blog to your Twitter profile or portfolio site.

---

## Step-by-Step Tutorials

### Tutorial 1: Adding XFN to a Button Block

**Scenario**: You want to create a button linking to a friend's website.

1. Add a **Button** block to your page
2. Enter the button text: "Visit Sarah's Blog"
3. Click the button to select it
4. Enter the URL in the link field: `https://sarahsblog.com`
5. Look at the block toolbar and click the **XFN** button
6. Click the **XFN** header to expand the section
7. Under **Friendship**, select **Friend**
8. Under **Physical**, select **Met** (if you've met Sarah)
9. View the summary: You'll see `[friend] [met]` pills
10. Click outside the popover to save

**Result**: Your button now has `rel="friend met"` in its HTML.

---

### Tutorial 2: Creating a Navigation Menu with Relationships

**Scenario**: Building a navigation menu with links to partner sites.

1. Open the **Site Editor** or add a **Navigation** block
2. Add a navigation item
3. Enter the label: "Partner Organizations"
4. Click to edit the link
5. Select the navigation item
6. Open the **Block** sidebar (right side)
7. Find the **XFN Relationships** panel
8. Expand it
9. Under **Professional**, check **Colleague**
10. Repeat for other partner links

**Result**: All partner links are marked with `rel="colleague"`.

---

### Tutorial 3: Inline Links in a Paragraph

**Scenario**: Writing a blog post mentioning your spouse's website.

1. Add a **Paragraph** block
2. Type: "My spouse writes about gardening at..."
3. Select the word "spouse"
4. Click the **Link** button in the toolbar
5. Enter the URL
6. Click **Advanced** at the bottom of the link popover
7. Expand the **XFN** section
8. Under **Family**, select **Spouse**
9. Under **Physical**, select **Met**
10. Press **Enter** to save the link

**Result**: The link has `rel="spouse met"`.

---

### Tutorial 4: Linking to Your Own Content

**Scenario**: Adding links to your social media profiles in a footer.

1. Add a **Paragraph** or **List** block
2. Type your social media labels
3. Link each one:
   - Select "Twitter" → Link → Enter Twitter URL
   - Click **Advanced** → Expand **XFN**
   - Under **Identity**, select **Me**
   - Save
4. Repeat for each social profile (LinkedIn, GitHub, etc.)

**Result**: All self-links have `rel="me"`, which helps with profile verification.

---

### Tutorial 5: Complex Relationship (Multiple Categories)

**Scenario**: Linking to your business partner who is also a friend.

1. Add a link using any method
2. Open XFN controls (Toolbar, Inspector, or Advanced panel)
3. Select multiple relationships:
   - **Friendship**: Friend
   - **Physical**: Met
   - **Professional**: Co-worker
4. View the summary showing all three pills
5. Save

**Result**: Link has `rel="friend met co-worker"`.

---

## Best Practices

### When to Use XFN

**DO use XFN for**:
- Links to people's personal websites or blogs
- Links to professional contacts and colleagues
- Links to family members' sites
- Links to your own content (use "me")
- Author bios and about pages

**DON'T use XFN for**:
- Links to corporations or brands (unless personal relationship)
- Generic resource links (tutorials, documentation)
- External tools or services
- Commercial product links

---

### Relationship Selection Tips

1. **Be Honest**: Only select relationships that actually exist
2. **Start Simple**: Use basic relationships (friend, colleague, met)
3. **Don't Over-Tag**: Not every link needs XFN metadata
4. **Update Regularly**: Relationships change—update old links periodically
5. **Respect Privacy**: Don't tag relationships people might want private

---

### Combining with Other Rel Attributes

XFN works alongside other rel values:

**Good Combinations**:
- `rel="friend met noopener"` - Friend link that opens safely
- `rel="me nofollow"` - Your profile on untrusted platform
- `rel="colleague nofollow ugc"` - Colleague's user-generated content

**Preserved Attributes**:
The plugin automatically preserves existing rel attributes:
- `nofollow` - SEO instruction
- `noopener` - Security for target="_blank"
- `noreferrer` - Privacy attribute
- `ugc` - User-generated content
- `sponsored` - Paid links

---

### Accessibility Considerations

The plugin is fully accessible, but follow these tips:

1. **Use Keyboard**: All controls work with Tab, Enter, Space, and Arrow keys
2. **Screen Readers**: Relationship descriptions are read aloud
3. **Clear Link Text**: Use descriptive link text (not just "click here")
4. **Logical Order**: XFN is supplementary—ensure base link is clear

---

### Performance Best Practices

1. **No Frontend Impact**: XFN only affects editor experience
2. **Build Before Publishing**: Build production assets for best performance
3. **Cached Output**: Relationships are static HTML—cache-friendly
4. **Minimal Overhead**: Only adds a few characters to HTML

---

## Troubleshooting

### XFN Controls Don't Appear

**Problem**: Can't find XFN button or panel.

**Solutions**:
1. **Check Block Type**: Only works with blocks that support links
   - Works: Button, Navigation, Paragraph (with link), List (with link)
   - Doesn't work: Image caption without link, Heading without link
2. **Refresh Editor**: Reload the page
3. **Check Plugin Status**: Go to Plugins → ensure plugin is active
4. **Clear Cache**: Clear browser cache and reload
5. **Theme Conflict**: Test with a default theme (Twenty Twenty-Four)

---

### Relationships Not Saving

**Problem**: Selected relationships disappear after saving.

**Solutions**:
1. **Save the Block**: Ensure you click outside the popover or press Enter
2. **Check Validation**: Some combinations are invalid:
   - Can't select "friend" AND "acquaintance" (mutually exclusive)
   - Can't select "child" AND "parent" (mutually exclusive)
3. **Update Post**: Click "Update" or "Publish" button
4. **Check Browser Console**: Look for JavaScript errors (F12 → Console)

---

### Inspector Panel Missing

**Problem**: Don't see XFN panel in sidebar.

**Solutions**:
1. **Select Block**: Click on the block containing the link
2. **Block Tab**: Make sure you're on the "Block" tab (not "Post")
3. **Scroll Down**: Panel might be below the fold
4. **Expand Panel**: Click the panel header to expand it

---

### Link Advanced Section Not Showing

**Problem**: No XFN section in link popover.

**Solutions**:
1. **Click Advanced**: Look for "Advanced" at bottom of link popover
2. **Inline Links Only**: This method works for inline links (paragraph text, list items)
3. **Try Alternative**: Use Floating Toolbar or Inspector Controls instead
4. **Plugin Conflict**: Deactivate other link-related plugins temporarily

---

### Relationships Look Wrong in HTML

**Problem**: Viewing source shows unexpected rel values.

**Check**:
1. **Other Plugins**: Another plugin might be modifying rel attributes
2. **Theme Functions**: Check theme's functions.php for rel modifications
3. **Expected Format**: Should be space-separated: `rel="friend met colleague"`
4. **Preserved Values**: Existing rel values (nofollow, etc.) should remain

---

### Accessibility Issues

**Problem**: Can't use keyboard or screen reader.

**Solutions**:
1. **Use Tab**: Tab key moves between controls
2. **Use Arrow Keys**: Navigate within button groups
3. **Use Space/Enter**: Activate buttons and toggles
4. **Update Plugin**: Ensure you have the latest version
5. **Report Issue**: Submit accessibility bug if problem persists

---

## FAQs

### General Questions

**Q: Do I need to configure anything after installation?**
A: No, the plugin works immediately without any configuration.

**Q: Will XFN relationships affect my site's SEO?**
A: XFN provides semantic metadata but has minimal direct SEO impact. It helps search engines understand relationships between sites.

**Q: Can I use XFN on existing links?**
A: Yes! Edit any existing link and add XFN relationships using any of the three methods.

**Q: Does this work with the Classic Editor?**
A: No, this plugin is designed for the Block Editor (Gutenberg) only.

---

### Technical Questions

**Q: Where is XFN data stored?**
A: In the standard HTML `rel` attribute—no custom database tables or meta fields.

**Q: Will relationships survive theme changes?**
A: Yes, XFN is stored in post content, not theme settings.

**Q: Can I export/import XFN relationships?**
A: Yes, they're part of your post content and work with all WordPress export/import tools.

**Q: Does this work with page builders?**
A: It depends on the page builder. Works best with Gutenberg-compatible builders.

---

### Compatibility Questions

**Q: What WordPress version do I need?**
A: WordPress 6.4 or higher.

**Q: What PHP version is required?**
A: PHP 7.4 or higher.

**Q: Does this work with WordPress Multisite?**
A: Yes, install network-wide or per-site.

**Q: Is this compatible with WooCommerce?**
A: Yes, works with WooCommerce product descriptions and pages using the block editor.

---

### Relationship Questions

**Q: Can I select multiple friendship levels?**
A: No, friendship relationships are mutually exclusive (contact, acquaintance, or friend—pick one).

**Q: Can someone be both "co-worker" and "colleague"?**
A: Yes! Professional relationships allow multiple selections.

**Q: Should I use "me" for all links to my own site?**
A: Use "me" for links to your profiles or content on other platforms. Internal site links don't need it.

**Q: What if my relationship doesn't fit any category?**
A: XFN is designed for common relationships. For unique connections, consider not using XFN or using the closest match.

---

### Usage Questions

**Q: How do I remove XFN relationships?**
A: Open the XFN interface and deselect all relationships.

**Q: Can I see all links with XFN on my site?**
A: Not built into the plugin, but you can view page source or use browser dev tools to find links with rel attributes.

**Q: What happens if I deactivate the plugin?**
A: Existing XFN relationships remain in your HTML. The plugin just adds/removes the editing interface.

**Q: Can I bulk-add XFN to existing links?**
A: No bulk operation is built in. Each link must be edited individually.

---

## Support and Resources

### Getting Help

- **GitHub Issues**: https://github.com/courtneyr-dev/xfn-link-extension/issues
- **WordPress.org Support**: (Coming soon after WordPress.org submission)
- **Documentation**: This guide and README.md in the plugin

### Additional Resources

- **XFN Specification**: http://gmpg.org/xfn/
- **Microformats**: http://microformats.org/
- **WordPress Block Editor**: https://wordpress.org/support/article/wordpress-editor/

### Contributing

Want to improve this plugin? Contributions are welcome!

- Submit bug reports and feature requests on GitHub
- Review the CONTRIBUTING.md file for guidelines
- Check CHANGELOG.md for version history

---

## Appendix: Quick Reference

### Relationship Quick Chart

| Type | Values | Selection Rule |
|------|--------|----------------|
| Friendship | contact, acquaintance, friend | Choose ONE |
| Physical | met | Single option |
| Professional | co-worker, colleague | Multiple allowed |
| Geographical | co-resident, neighbor | Choose ONE |
| Family | child, parent, sibling, spouse, kin | Choose ONE |
| Romantic | muse, crush, date, sweetheart | Multiple allowed |
| Identity | me | Single option |

### Keyboard Shortcuts

| Action | Shortcut |
|--------|----------|
| Open link popover | Ctrl+K (⌘+K on Mac) |
| Navigate controls | Tab / Shift+Tab |
| Expand/collapse section | Enter or Space |
| Select relationship | Space or Enter |
| Navigate button group | Arrow keys |
| Save and close | Escape or click outside |

---

**Document Version**: 1.0.0
**Last Updated**: 2025-12-02
**Plugin Version**: 1.0.0
