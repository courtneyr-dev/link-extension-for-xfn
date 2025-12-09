# Adding XFN to Image Links

This guide shows you how to add XFN (XHTML Friends Network) relationships to clickable Image blocks.

## What are Image Links?

Image links are clickable images that take users to another page when clicked. Common uses include:
- Profile photos linking to social media
- Logo images linking to partner sites
- Thumbnail images linking to portfolios
- Author photos linking to about pages
- Product images linking to storefronts

## Two Ways to Access XFN Options

### Method 1: Inspector Sidebar (Recommended for Images)

The XFN Relationships panel is **open by default** for Image blocks, making it easy to add relationships:

1. **Add or select an Image block**
2. **Add a link to the image**:
   - Click the image
   - Find the "Link" field in the toolbar or sidebar
   - Enter your URL
3. **Look at the right sidebar** (Inspector Controls)
4. **Find "XFN Relationships"** panel (should be open already)
5. **Select your relationships**
6. **See your active relationships** in the summary at the bottom
7. **Changes save automatically**

### Method 2: Floating Toolbar

For quick access while editing:

1. **Click on the Image block**
2. **Look for the "XFN" button** in the floating toolbar
3. **Click it** to open the XFN panel
4. **Expand the XFN section** to see relationship options
5. **Select your relationships**
6. **Changes save automatically**

## Step-by-Step Example: Author Profile Image

Let's add a profile photo that links to your personal site:

1. **Add an Image block** by typing `/image` or clicking the + icon
2. **Upload or select your profile photo**
3. **Click the image** to select it
4. **Click the link icon** in the toolbar (or look for "Link" in the sidebar)
5. **Enter your personal site URL**: `https://yoursite.com`
6. **Press Enter** to apply the link
7. **Look at the right sidebar** - the XFN Relationships panel should be open
8. **Under Identity**, check **"Me"**
9. **See the summary** showing "me"
10. **Done!** The relationship is saved automatically

Your image link now has `rel="me"` indicating this goes to your own content!

## Common Image Link Scenarios

### Profile Photo to Personal Site
```
Image: Your headshot
Link: https://yoursite.com
XFN: Identity → Me
Result: rel="me"
```

### Friend's Photo to Their Blog
```
Image: Friend's photo
Link: https://friendblog.com
XFN: Friendship → Friend, Physical → Met
Result: rel="friend met"
```

### Team Member Photo
```
Image: Colleague's headshot
Link: https://colleague-portfolio.com
XFN: Professional → Colleague, Physical → Met
Result: rel="colleague met"
```

### Partner Logo
```
Image: Spouse's business logo
Link: https://spouse-business.com
XFN: Family → Spouse, Professional → Colleague
Result: rel="spouse colleague"
```

### Social Media Avatar
```
Image: Your avatar
Link: https://twitter.com/yourusername
XFN: Identity → Me
Result: rel="me"
```

## Gallery Images with Links

You can add different XFN relationships to multiple images in a Gallery block:

**Example: Team Gallery**
```
[Alice's Photo] [Bob's Photo] [Your Photo]
 rel="colleague"  rel="colleague"  rel="me"
```

Each image maintains its own XFN relationships independently.

## Image Settings and XFN

### Image Link Destination Options

WordPress offers multiple link destinations for images:
- **Custom URL**: Full control - add any URL and XFN relationships
- **Media File**: Links to the full-size image - XFN typically not needed
- **Attachment Page**: Links to the media attachment page - use XFN if appropriate
- **None**: No link - XFN options won't appear

**Only Custom URL** destinations should use XFN relationships.

### Image Alignment

XFN works with all image alignment options:
- Left aligned
- Center aligned
- Right aligned
- Wide width
- Full width

Alignment doesn't affect XFN functionality.

### Image Sizes

XFN relationships work the same regardless of image size:
- Thumbnail
- Medium
- Large
- Full size

## Advanced Image XFN Usage

### Featured Images

Featured images can also have XFN relationships when they're set to link to something:

1. **Set your featured image** in the post/page settings
2. **If your theme supports featured image links**, you may be able to add XFN
3. **Check with your theme documentation** for featured image link options

### Cover Block Images

Cover blocks with links can use XFN:

1. **Add a Cover block**
2. **Add your background image**
3. **Add a link to the entire cover block**
4. **Use Inspector sidebar** to add XFN relationships

### Image with Caption Links

If your caption contains a link (not the image itself):

1. **Click in the caption area**
2. **Select the link text**
3. **Follow the [Paragraph Links](paragraph-links.md) guide** for inline links

## Tips for Image Links

### ✅ Best Practices

- **Use "Me" for your photos**: Profile pictures, avatars, and headshots linking to your content
- **Alt text is important**: Always add descriptive alt text to images (separate from XFN)
- **Consistent relationships**: Use the same XFN for the same person across multiple images
- **Test the link**: Make sure the image is actually clickable after adding XFN

### ⚠️ Things to Avoid

- **Don't add XFN to decorative images**: If the image isn't clickable, XFN isn't applicable
- **Don't use for product images**: Unless you have a personal relationship with the product owner
- **Don't link media file with XFN**: When "Link to Media File" is selected, XFN doesn't make sense
- **Don't overuse**: Not every clickable image needs XFN

## Checking Your Image XFN

To verify your XFN relationships were added correctly:

### In the Editor
1. **Select the image**
2. **Check the Inspector sidebar** - see active relationships
3. **Verify the link** is present in the toolbar

### On the Frontend
1. **View the published page**
2. **Right-click the image** and select "Inspect Element"
3. **Look for the `<a>` tag** wrapping the `<img>` tag
4. **Check the rel attribute**: `<a href="..." rel="me">`

### Test the Click
1. **View the published page**
2. **Click the image** - it should navigate to your URL
3. **Verify the destination** matches your intended link

## Troubleshooting

### Problem: XFN panel not visible
**Solution**:
- Make sure the image has a link first (must be a Custom URL)
- Select the image block (click on it)
- Scroll down in the Inspector sidebar
- Try clicking the "XFN" button in the floating toolbar

### Problem: Image isn't clickable
**Solution**:
- Check that you've added a link to the image
- Make sure "Link to" is set to "Custom URL" (not "Media File" or "None")
- Verify the URL is valid and starts with http:// or https://
- Save and refresh the page

### Problem: Can't find the link field
**Solution**:
- Look in the Image block toolbar (top of block)
- Or check the Inspector sidebar for "Link" settings
- Make sure you've selected the Image block (not a parent block)

### Problem: Relationships not saving
**Solution**:
- Verify the image has a valid link URL
- Try selecting a relationship again - it should save automatically
- Save your post/page and reload to verify
- Check browser console for JavaScript errors

## Accessibility Considerations

When adding XFN to image links, don't forget accessibility:

### Alt Text
Always provide descriptive alt text:
```
Good: "Profile photo of Sarah Johnson, web designer"
Bad: "image1.jpg"
```

### Link Purpose
The image's alt text should describe where the link goes:
```
Good: "Visit John's portfolio site" (with XFN rel="friend")
Bad: "Photo" (user doesn't know where they'll go)
```

### Keyboard Navigation
- Ensure the image link is keyboard accessible (works with Tab key)
- Test with screen readers if possible

## Related Documentation

- [Paragraph Links](paragraph-links.md) - For inline text links
- [Button Links](button-links.md) - For Button block links
- [Other Block Links](other-block-links.md) - For Navigation and other blocks

## Need More Help?

- Visit the [WordPress.org support forum](https://wordpress.org/support/plugin/link-extension-for-xfn/)
- Report issues on [GitHub](https://github.com/courtneyr-dev/link-extension-for-xfn/issues)
