# Adding XFN to Paragraph Links

This guide shows you how to add XFN (XHTML Friends Network) relationships to links within paragraphs and other text-based blocks.

## What are Paragraph Links?

Paragraph links are inline links within text content, including:
- Paragraph blocks
- Heading blocks
- List blocks (bulleted and numbered)
- Quote blocks
- Verse blocks
- Any block with text content

## Three Ways to Access XFN Options

### Method 1: Link Advanced Panel (Recommended)

This is the most direct method for inline links:

1. **Select text** in your paragraph
2. **Click the Link button** (🔗) in the toolbar, or press `Cmd+K` (Mac) / `Ctrl+K` (Windows)
3. **Enter your URL** and press Enter or click the arrow
4. **Click the link** to reveal the link popover
5. **Click "Advanced"** to expand advanced options
6. **Click the "XFN" section** to expand relationship options
7. **Select your relationships**:
   - Use radio buttons for single-choice categories (Friendship, Family, etc.)
   - Use checkboxes for multiple-choice categories (Romantic, Identity)
8. **View your selections** in the "Active Relationships" summary at the bottom
9. **Click "Apply"** to save your changes

The Advanced panel will automatically collapse after clicking Apply to keep your editing space clean.

### Method 2: Floating Toolbar

For quick access while editing:

1. **Click on the block** containing your link
2. **Look for the "XFN" button** in the floating toolbar above the block
3. **Click the XFN button** to open the relationship panel
4. **Expand the XFN section** if needed
5. **Select your relationships**
6. **The relationships are saved automatically** as you select them

### Method 3: Inspector Sidebar

For a persistent panel while working:

1. **Click on the block** containing your link
2. **Look at the right sidebar** (Inspector)
3. **Find the "Link" panel** (may need to expand it)
4. **Scroll to the XFN section**
5. **Select your relationships**
6. **Changes save automatically**

## Step-by-Step Example: Linking to a Friend

Let's say you're writing about your friend Sarah and want to link to her website:

1. **Write your text**: "Check out my friend Sarah's photography portfolio"
2. **Select "Sarah's photography portfolio"**
3. **Press `Cmd+K`** (Mac) or `Ctrl+K` (Windows)
4. **Type her URL**: `https://sarahphotos.com`
5. **Press Enter** to create the link
6. **Click the link** to open the link popover
7. **Click "Advanced"** to expand
8. **Click "XFN"** to expand the relationship options
9. **Under Friendship**, select **"Friend"**
10. **Under Physical**, check **"Met"** (if you've met in person)
11. **See the summary** showing "friend met"
12. **Click "Apply"** to save

The link now has `rel="friend met"` which tells the web that Sarah is your friend whom you've met!

## Common Paragraph Link Scenarios

### Linking to Your Portfolio
```
Text: "View my design work on Behance"
XFN: Identity → Me
Result: rel="me"
```

### Linking to a Colleague's Blog
```
Text: "My colleague John wrote about this topic"
XFN: Professional → Colleague, Physical → Met
Result: rel="colleague met"
```

### Linking to a Family Member
```
Text: "My sister runs a bakery downtown"
XFN: Family → Sibling
Result: rel="sibling"
```

### Linking to Your Spouse's Business
```
Text: "My husband's consulting firm"
XFN: Family → Spouse, Romantic → Sweetheart, Physical → Met
Result: rel="spouse sweetheart met"
```

## Multiple Links in One Paragraph

You can add different XFN relationships to multiple links in the same paragraph:

**Example:**
> My friend [Alice](https://alice.com) and I met at a conference where [Bob](https://bob.dev), a colleague, was speaking.

- Alice's link: `rel="friend met"`
- Bob's link: `rel="colleague met"`

Each link maintains its own XFN relationships independently.

## Tips for Paragraph Links

### ✅ Best Practices

- **Be accurate**: Only add relationships that truly exist
- **Use "Met"**: Always add "met" if you've met the person in real life
- **Multiple relationships**: You can combine relationships (e.g., "friend colleague met")
- **Update when needed**: Relationships can change over time - update them!

### ⚠️ Things to Avoid

- **Don't overuse**: Not every link needs XFN - only use for people/organizations you have a relationship with
- **Don't guess**: If you're unsure about a relationship, leave it blank
- **Don't use for commercial links**: XFN is for personal/professional relationships, not advertisements

## Viewing Your XFN Relationships

After adding XFN relationships:

1. **In the editor**: Look for the relationship badges in the link popover
2. **In the code**: View the HTML source to see `rel="friend met"` etc.
3. **On the frontend**: Right-click the link and "Inspect Element" to see the rel attribute

## Troubleshooting

### Problem: Can't find the XFN section
**Solution**:
- Make sure you've clicked "Advanced" in the link popover
- Scroll down - the XFN section may be below other options
- Try using the Floating Toolbar method instead

### Problem: Relationships not saving
**Solution**:
- Make sure to click "Apply" after selecting relationships
- Check that your link URL is valid
- Try refreshing the page and checking again

### Problem: Advanced panel closes immediately
**Solution**:
- This is normal behavior after clicking Apply
- Click the link again to reopen and verify your relationships were saved
- Look for the relationship count badge in the XFN section header

## Related Documentation

- [Button Links](button-links.md) - For Button block links
- [Image Links](image-links.md) - For clickable images
- [Other Block Links](other-block-links.md) - For Navigation and other blocks

## Need More Help?

- Visit the [WordPress.org support forum](https://wordpress.org/support/plugin/link-extension-for-xfn/)
- Report issues on [GitHub](https://github.com/courtneyr-dev/link-extension-for-xfn/issues)
