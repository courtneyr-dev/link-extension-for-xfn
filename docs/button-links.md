# Adding XFN to Button Links

This guide shows you how to add XFN (XHTML Friends Network) relationships to Button blocks.

## What are Button Links?

Button blocks are standalone link elements designed for calls-to-action, navigation, and prominent links. Common uses include:
- Call-to-action buttons ("Contact Me", "View Portfolio")
- Download buttons
- Navigation buttons
- Social media profile buttons

## Two Ways to Access XFN Options

### Method 1: Inspector Sidebar (Recommended for Buttons)

The XFN Relationships panel is **open by default** for Button blocks, making it quick and easy:

1. **Add or select a Button block**
2. **Look at the right sidebar** (Inspector Controls)
3. **Find "XFN Relationships"** panel (should be open already)
4. **Select your relationships**:
   - Radio buttons for single-choice categories (Friendship, Professional, etc.)
   - Checkboxes for multiple-choice categories (Romantic, Identity)
5. **See your active relationships** in the summary at the bottom
6. **Changes save automatically** as you select them

No need to click Apply - your selections are saved instantly!

### Method 2: Floating Toolbar

For quick access while editing button text:

1. **Click on the Button block**
2. **Look for the "XFN" button** in the floating toolbar
3. **Click it** to open the XFN panel
4. **Expand the XFN section** to see relationship options
5. **Select your relationships**
6. **Changes save automatically**

## Step-by-Step Example: Portfolio Button

Let's create a button linking to your portfolio with XFN:

1. **Add a Button block** by typing `/button` or clicking the + icon
2. **Type your button text**: "View My Portfolio"
3. **Click the link icon** or the URL field
4. **Enter your portfolio URL**: `https://yourportfolio.com`
5. **Press Enter** or click the arrow to confirm
6. **Look at the right sidebar** - the XFN Relationships panel should be open
7. **Under Identity**, check **"Me"**
8. **See the summary** showing "me" in the active relationships
9. **Done!** The relationship is saved automatically

Your button now has `rel="me"` indicating this is your own content!

## Common Button Link Scenarios

### Portfolio/Personal Site Button
```
Button Text: "Visit My Website"
URL: https://yoursite.com
XFN: Identity → Me
Result: rel="me"
```

### Contact a Colleague Button
```
Button Text: "Schedule a Meeting"
URL: https://calendly.com/colleague
XFN: Professional → Colleague, Physical → Met
Result: rel="colleague met"
```

### Friend's Business Button
```
Button Text: "Support Sarah's Bakery"
URL: https://sarahsbakery.com
XFN: Friendship → Friend, Physical → Met
Result: rel="friend met"
```

### Spouse's Site Button
```
Button Text: "My Partner's Art Gallery"
URL: https://partnerart.com
XFN: Family → Spouse, Romantic → Sweetheart, Physical → Met
Result: rel="spouse sweetheart met"
```

### Social Media Profile Button
```
Button Text: "Follow Me on Twitter"
URL: https://twitter.com/yourusername
XFN: Identity → Me
Result: rel="me"
```

## Buttons in Button Groups

You can add different XFN relationships to multiple buttons in a Buttons block:

**Example:**
```
[View My Work] [Connect on LinkedIn] [Read My Blog]
     rel="me"         rel="me"            rel="me"
```

Each button maintains its own XFN relationships independently.

## Advanced Button Styling with XFN

XFN relationships don't affect button styling, but you can use them in custom CSS:

```css
/* Style buttons that link to your own content */
.wp-block-button__link[rel~="me"] {
    border: 2px solid var(--wp--preset--color--primary);
}

/* Style buttons that link to friends */
.wp-block-button__link[rel~="friend"] {
    background: linear-gradient(to right, #667eea, #764ba2);
}
```

## Tips for Button Links

### ✅ Best Practices

- **Use "Me" for self-promotion**: Link to your portfolio, social profiles, or other sites you own
- **Be consistent**: Use the same relationships across similar buttons
- **Update regularly**: Keep relationships current as they change
- **Use Inspector sidebar**: It's faster for buttons since the panel is already open

### ⚠️ Things to Avoid

- **Don't add XFN to generic CTAs**: Buttons like "Learn More" or "Get Started" that go to product pages don't need XFN
- **Don't use for ads**: XFN is for personal/professional relationships, not advertising
- **Don't conflate relationships**: If someone is both a friend and colleague, add both, but be accurate

## Combining XFN with Other Link Attributes

Button blocks support multiple link attributes that work alongside XFN:

### Open in New Tab
- **Setting**: Toggle "Open in new tab" in the Link settings
- **Result**: Adds `target="_blank" rel="me noopener"`
- **XFN preserved**: Your XFN relationships stay intact

### Nofollow
- **Setting**: Advanced link settings may include nofollow
- **Result**: `rel="me nofollow"`
- **Note**: XFN and SEO attributes combine automatically

## Checking Your Button XFN

To verify your XFN relationships were added correctly:

### In the Editor
1. **Select the button**
2. **Check the Inspector sidebar** - see active relationships
3. **Look for the relationship count** in the XFN panel header

### On the Frontend
1. **View the published page**
2. **Right-click the button** and select "Inspect Element"
3. **Look for the rel attribute**: `<a class="wp-block-button__link" rel="me" ...>`

### In the Code Editor
1. **Switch to Code Editor** (three dots → Code editor)
2. **Find your button block**
3. **Look for**: `<a class="wp-block-button__link" href="..." rel="friend met">`

## Troubleshooting

### Problem: XFN panel not visible
**Solution**:
- Scroll down in the Inspector sidebar
- Make sure you've selected the Button block (not the parent Buttons block)
- Try clicking directly on the button link area

### Problem: Can't add a URL to the button
**Solution**:
- Make sure you're in edit mode, not select mode
- Click the link icon or the URL field at the top of the button
- If the button already has a URL, click the button then click the link in the toolbar

### Problem: Relationships not showing on frontend
**Solution**:
- Save/update your post or page
- Clear your browser cache
- Check the HTML source to verify the rel attribute is there
- Make sure your theme isn't stripping the rel attribute

### Problem: Inspector panel is closed
**Solution**:
- Click on the "XFN Relationships" panel header to expand it
- If you still don't see it, try selecting the button again
- Check that the plugin is active

## Related Documentation

- [Paragraph Links](paragraph-links.md) - For inline text links
- [Image Links](image-links.md) - For clickable images
- [Other Block Links](other-block-links.md) - For Navigation and other blocks

## Need More Help?

- Visit the [WordPress.org support forum](https://wordpress.org/support/plugin/link-extension-for-xfn/)
- Report issues on [GitHub](https://github.com/courtneyr-dev/link-extension-for-xfn/issues)
