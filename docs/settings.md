# Plugin Settings

This guide explains how to configure the Link Extension for XFN plugin settings to control where XFN relationship options appear in your WordPress editor.

## Accessing Plugin Settings

1. **Log in to your WordPress admin dashboard**
2. **Go to Settings → XFN Link Extension** in the left sidebar
3. You'll see the **XFN Link Extension Settings** page

## Settings Overview

The plugin offers two optional interface locations that you can enable or disable. A third interface (Link Advanced Panel) is always enabled and cannot be disabled.

## Interface Options

### 1. Inspector Controls (Sidebar Panel)

**Setting Name:** Inspector Controls
**Default:** Disabled (unchecked)
**Applies to:** Block-level links (Button, Image, Navigation, Site Logo, etc.)

#### What It Does

When enabled, this adds an "XFN Relationships" panel to the **Inspector sidebar** (right side of the editor) for blocks that are entirely links.

#### When to Enable

✅ **Enable if you**:
- Frequently work with Button blocks and want XFN options visible
- Add relationships to Navigation menu items regularly
- Link Images often and want quick access to XFN
- Prefer having all block settings in one place (the sidebar)
- Work with Site Logo or Post Title links

#### Where It Appears

When enabled, you'll see "XFN Relationships" panel in the Inspector sidebar when you select:
- **Button blocks** (opens by default)
- **Image blocks** (opens by default)
- **Navigation link items** (opens by default)
- **Site Logo block** (opens by default)
- **Post Title blocks** (when linked)
- **Query Title blocks** (when linked)

#### Visual Example

```
┌─────────────────────────────┐
│   Inspector Controls        │
│   (Right Sidebar)           │
├─────────────────────────────┤
│ Block                       │
│ ▼ Button                    │
│   - Link URL                │
│   - Style options           │
│                             │
│ ▼ XFN Relationships         │ ← Appears here when enabled
│   Friendship                │
│   ○ None                    │
│   ○ Contact                 │
│   ○ Acquaintance            │
│   ○ Friend                  │
│   ...                       │
└─────────────────────────────┘
```

#### Best For

- **Content editors** who work with Button blocks regularly
- **Site builders** creating navigation menus
- **Users who prefer sidebar controls** over popups
- **Multi-block workflows** where you're editing many buttons/images

---

### 2. Floating Toolbar Button

**Setting Name:** Floating Toolbar Button
**Default:** Disabled (unchecked)
**Applies to:** Block-level links (Button, Image, Navigation, etc.)

#### What It Does

When enabled, this adds an "XFN" button to the **floating toolbar** that appears above blocks when selected.

#### When to Enable

✅ **Enable if you**:
- Want quick access to XFN without opening the sidebar
- Prefer toolbar buttons over sidebar panels
- Have limited screen space and keep the sidebar closed
- Want a consistent XFN button across all block types

#### Where It Appears

When enabled, you'll see an "XFN" button in the toolbar above:
- **Button blocks**
- **Image blocks** (when linked)
- **Navigation link items**
- **Site Logo block**
- **Other block-level links**

#### Visual Example

```
┌─────────────────────────────────────┐
│  [B] [I] [Link] [XFN] [•••]        │ ← XFN button in toolbar
└─────────────────────────────────────┘
        ↓
┌─────────────────────────────────────┐
│  XFN Relationship Options           │
│  ┌───────────────────────────────┐  │
│  │ ▼ XFN                         │  │
│  │   Friendship: Friend          │  │
│  │   Physical: Met               │  │
│  │   Active: friend met          │  │
│  └───────────────────────────────┘  │
└─────────────────────────────────────┘
```

#### Best For

- **Quick edits** to existing links
- **Users with small screens** who keep the sidebar collapsed
- **Keyboard-first workflows** using toolbar shortcuts
- **Minimal UI preference** (fewer open panels)

---

### 3. Link Advanced Panel (Always Enabled)

**Setting Name:** None - Always enabled
**Cannot be disabled**
**Applies to:** Inline links (paragraphs, headings, lists, etc.)

#### What It Is

This is the XFN section that appears in the **Link Advanced panel** when you create or edit inline links within text blocks like paragraphs.

#### Why It's Always Enabled

Inline links (links within text) are the most common type of link in WordPress. The Link Advanced Panel is the standard WordPress interface for these links, and XFN fits naturally here.

#### Where It Appears

You'll find the XFN section when you:
1. **Select text** in a paragraph, heading, or list
2. **Add a link** (Cmd/Ctrl+K)
3. **Click the link** to open the link popover
4. **Click "Advanced"** to expand advanced options
5. **Find the "XFN" collapsible section**

#### Visual Example

```
┌─────────────────────────────────────┐
│  Link: https://example.com          │
│  [✓] Open in new tab                │
│  ───────────────────────────────    │
│  ▼ Advanced                          │
│    Link Rel                          │
│    ▼ XFN (6)                         │ ← Always available
│      Friendship: Friend              │
│      Physical: Met                   │
│      ...                             │
│    [Apply]                           │
└─────────────────────────────────────┘
```

#### Best For

- **Paragraph links** - most common use case
- **Blog posts** with inline references
- **Article authors** linking to sources
- **Any text-based content** with links

---

## Recommended Settings

### For Most Users

```
☐ Inspector Controls (Leave unchecked)
☐ Floating Toolbar Button (Leave unchecked)
✓ Link Advanced Panel (Always on)
```

**Why**: The Link Advanced Panel handles most use cases. Enable the others only if you frequently work with block-level links.

### For Site Builders / Designers

```
✓ Inspector Controls (Enable)
☐ Floating Toolbar Button (Leave unchecked)
✓ Link Advanced Panel (Always on)
```

**Why**: Site builders work with Buttons, Navigation, and Images often. Having the Inspector panel open saves time.

### For Button-Heavy Sites

```
✓ Inspector Controls (Enable)
✓ Floating Toolbar Button (Enable)
✓ Link Advanced Panel (Always on)
```

**Why**: If your site uses many Button blocks (landing pages, CTAs), both options provide maximum flexibility.

### For Minimal UI / Small Screens

```
☐ Inspector Controls (Leave unchecked)
✓ Floating Toolbar Button (Enable)
✓ Link Advanced Panel (Always on)
```

**Why**: Keep the sidebar clean and use the toolbar button only when needed.

---

## Enabling/Disabling Settings

### To Enable an Interface Option

1. **Go to Settings → XFN Link Extension**
2. **Check the checkbox** next to the interface option
3. **Click "Save Changes"** at the bottom
4. **Refresh any open editor windows** to see the changes

### To Disable an Interface Option

1. **Go to Settings → XFN Link Extension**
2. **Uncheck the checkbox** next to the interface option
3. **Click "Save Changes"** at the bottom
4. **Refresh any open editor windows** to remove the interface

### Important Notes

- Changes apply **site-wide** for all users
- Changes take effect immediately after saving
- You may need to **refresh the editor** to see changes
- **Link Advanced Panel cannot be disabled** (it's always available)

---

## Which Interface Should I Use?

### Comparison Table

| Feature | Inspector Controls | Floating Toolbar | Link Advanced Panel |
|---------|-------------------|------------------|---------------------|
| **Always visible when block selected** | ✓ | ✗ | ✗ |
| **Saves space** | ✗ | ✓ | ✓ |
| **Works for inline links** | ✗ | ✗ | ✓ |
| **Works for block links** | ✓ | ✓ | ✗ |
| **Panel open by default (Buttons/Images)** | ✓ | ✗ | N/A |
| **Requires click to access** | ✗ | ✓ | ✓ |
| **Can be disabled** | ✓ | ✓ | ✗ |

### Decision Guide

**Question 1: What kind of links do you work with most?**
- Inline links in paragraphs → Use Link Advanced Panel (always on)
- Button blocks → Enable Inspector Controls
- Mix of both → Enable Inspector Controls + use Link Advanced Panel

**Question 2: Do you prefer sidebar or toolbar?**
- Sidebar → Enable Inspector Controls
- Toolbar → Enable Floating Toolbar Button
- Both → Enable both!

**Question 3: How much screen space do you have?**
- Large screen → Enable Inspector Controls (always visible)
- Small screen → Enable Floating Toolbar Button (on-demand)

---

## Troubleshooting Settings

### Problem: I enabled a setting but don't see any changes

**Solution:**
1. Click "Save Changes" on the settings page
2. Go back to your post/page editor
3. Refresh the page (F5 or Cmd/Ctrl+R)
4. Select a block that should show XFN options
5. Look for the "XFN Relationships" panel or "XFN" button

### Problem: I enabled Inspector Controls but don't see the panel

**Solution:**
- Make sure you're editing a **block-level link** (Button, Image, Navigation)
- The panel won't appear for inline paragraph links
- Try selecting a Button block specifically
- Scroll down in the Inspector sidebar - it may be below other panels

### Problem: I want to disable XFN entirely

**Solution:**
- Uncheck both Interface Options (Inspector Controls and Floating Toolbar)
- This will hide XFN from block-level links
- **Note**: Link Advanced Panel cannot be disabled, so XFN will still appear for inline links
- To fully disable the plugin, go to Plugins → Deactivate "Link Extension for XFN"

### Problem: Settings reset after update

**Solution:**
- Settings should persist across plugin updates
- If they reset, check Settings → XFN Link Extension
- Re-enable your preferred options
- Report this as a bug on [GitHub](https://github.com/courtneyr-dev/link-extension-for-xfn/issues)

### Problem: Some users see XFN options, others don't

**Solution:**
- Settings apply site-wide, so all users should see the same interfaces
- Make sure all users have refreshed their editor
- Check that all users have the necessary permissions (editor or above)
- Clear browser cache and try again

---

## Best Practices

### ✅ Do

- **Start with defaults** (both unchecked) and enable as needed
- **Enable Inspector Controls** if you work with Buttons/Navigation frequently
- **Refresh the editor** after changing settings
- **Test on a staging site** before changing production settings
- **Document your settings** for team members

### ⚠️ Don't

- **Don't enable all options** if you don't use block-level links often
- **Don't forget to save** after changing settings
- **Don't expect changes instantly** - refresh the editor
- **Don't disable Link Advanced Panel** - you can't (it's always on)

---

## Settings for Teams

### Multi-User Sites

If multiple people edit your site:

1. **Communicate changes**: Let your team know when you change settings
2. **Standardize on one approach**: Choose Inspector or Toolbar, not both (reduces confusion)
3. **Train on the interface**: Make sure everyone knows where to find XFN options
4. **Document your choice**: Add a note to your team documentation

### Recommended Team Settings

**Small team (2-5 editors):**
```
✓ Inspector Controls
✗ Floating Toolbar Button
```

**Large team (6+ editors):**
```
✓ Inspector Controls
✓ Floating Toolbar Button
(Gives flexibility for different workflows)
```

---

## Advanced: Settings Storage

For developers and administrators:

### Database Storage

Settings are stored in `wp_options` table:
```php
// Option name
'xfn_link_extension_options'

// Structure
array(
    'enable_inspector_controls' => false,
    'enable_floating_toolbar' => false,
)
```

### Programmatic Access

```php
// Get settings
$options = get_option( 'xfn_link_extension_options' );

// Check if Inspector Controls enabled
$inspector_enabled = ! empty( $options['enable_inspector_controls'] );

// Check if Floating Toolbar enabled
$toolbar_enabled = ! empty( $options['enable_floating_toolbar'] );
```

### Reset to Defaults

To reset settings to defaults:
1. Go to Settings → XFN Link Extension
2. Uncheck all options
3. Click "Save Changes"

Or via database:
```sql
DELETE FROM wp_options WHERE option_name = 'xfn_link_extension_options';
```

---

## Related Documentation

- [Paragraph Links](paragraph-links.md) - Using XFN with inline text links
- [Button Links](button-links.md) - Using XFN with Button blocks
- [Image Links](image-links.md) - Using XFN with clickable images
- [Other Block Links](other-block-links.md) - Navigation, Site Logo, etc.

## Need More Help?

- Visit the [WordPress.org support forum](https://wordpress.org/support/plugin/link-extension-for-xfn/)
- Report issues on [GitHub](https://github.com/courtneyr-dev/link-extension-for-xfn/issues)
- Check the [main documentation](README.md)
