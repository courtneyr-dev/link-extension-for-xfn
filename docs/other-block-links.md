# Adding XFN to Other Block Types

This guide covers XFN relationships for Navigation blocks, Site Logo, Post Titles, and other specialized link blocks.

## Supported Block Types

The Link Extension for XFN plugin supports XFN relationships on these block-level link types:

- **Navigation Links** - Menu items and navigation
- **Site Logo** - Your site's clickable logo
- **Post Title Links** - Post/page title links in query loops
- **Query Title Links** - Archive and taxonomy title links

## Navigation Links

Navigation blocks are commonly used for site menus, headers, and footers.

### Adding XFN to Navigation Items

1. **Open the Navigation editor**:
   - Go to Appearance → Editor → Navigation
   - Or edit a Navigation block in a post/page/template
2. **Select or add a navigation item**
3. **Click on the navigation link**
4. **Look at the right sidebar** (Inspector Controls)
5. **Find "XFN Relationships"** panel (open by default)
6. **Select your relationships**
7. **Changes save automatically**

### Common Navigation Scenarios

#### Personal Profile Link
```
Menu Item: "About Me"
Link: https://yoursite.com/about
XFN: Identity → Me
Result: rel="me"
```

#### Social Media Navigation
```
Menu Items:
- "Twitter" → https://twitter.com/you → rel="me"
- "LinkedIn" → https://linkedin.com/in/you → rel="me"
- "GitHub" → https://github.com/you → rel="me"
```

#### Team Members Menu
```
Menu Items:
- "Meet Alice" → rel="colleague met"
- "Meet Bob" → rel="colleague met"
- "Meet Sarah" → rel="colleague friend met"
```

#### Partner/Sponsor Links
```
Menu Items:
- "Our Partners"
  - "Partner A" → rel="colleague"
  - "Partner B" → rel="colleague"
  - "Friend's Business" → rel="friend met"
```

### Navigation Link Tips

✅ **Best Practices**:
- Use XFN for personal/professional relationship links only
- Don't add XFN to generic site pages (Home, Contact, etc.)
- Consistent relationships across menus
- Use "Me" for links to your social profiles

⚠️ **Avoid**:
- Don't add XFN to every menu item
- Skip XFN on standard navigation (internal pages)
- Don't use XFN for sponsored/paid links

## Site Logo

The Site Logo block can link to your homepage or another site.

### Adding XFN to Site Logo

1. **Add or select the Site Logo block**
2. **Set the link**:
   - By default, logos link to your homepage (no XFN needed)
   - To link externally, you may need to use custom code
3. **If linking to external site**:
   - Look at Inspector sidebar for XFN options
   - Select "Me" if it's your other site
   - Select appropriate relationships

### Site Logo Scenarios

Most site logos link to the homepage and **don't need XFN**. However:

#### Multi-site Setup
If your logo links to a parent site or network:
```
Logo Link: https://main-site.com
XFN: Identity → Me (if you own both sites)
```

#### Partner Site
If collaborating with another organization:
```
Logo Link: https://partner-org.com
XFN: Professional → Colleague
```

## Post Title Links

Post Title blocks in query loops can have XFN relationships when they link externally.

### When to Use XFN on Post Titles

**Standard use**: Post titles usually link to the post itself (no XFN needed)

**External links**: If your post title links to an external site:

1. **Edit the post** that's being displayed
2. **Find the link setting** in the post meta or block
3. **Add XFN** if you have a relationship with the link destination

### Post Title Scenarios

#### Guest Post Attribution
```
Post: "Guest Post by Alice Smith"
Title Link: https://alice-blog.com
XFN: Professional → Colleague, Physical → Met
```

#### Linking to Referenced Work
```
Post: "About My Friend's New Book"
Title Link: https://friend-author-site.com
XFN: Friendship → Friend
```

## Query Title Links

Query Title blocks show archive, category, or tag titles.

### When to Use XFN

**Rarely needed** - Query titles typically link to internal archives.

**Possible use case**: If you've customized a query title to link externally (requires custom code), you can add XFN relationships through the Inspector sidebar.

## Block-Level Links in Templates

Many of these blocks appear in templates (header, footer, site editor):

### Editing Template Links

1. **Go to Appearance → Editor**
2. **Select the template** (Header, Footer, etc.)
3. **Click on the block** (Navigation, Site Logo, etc.)
4. **Access XFN options** via Inspector sidebar
5. **Changes apply site-wide** for that template

### Template Use Cases

#### Footer Social Links
```html
<Navigation>
  "Twitter" → rel="me"
  "LinkedIn" → rel="me"
  "GitHub" → rel="me"
</Navigation>
```

#### Header Partner Link
```html
<Image or Button>
  "Partner Logo" → rel="colleague"
</Image or Button>
```

## Advanced: Custom Block Support

If you're a developer and want to add XFN support to custom blocks:

### Requirements
Your block must have either:
- A `url` attribute (most common)
- A `href` attribute
- A `linkDestination` attribute

### How It Works
The plugin automatically detects blocks with link attributes and adds XFN controls to the Inspector sidebar.

### Supported Blocks (Full List)
- `core/button`
- `core/image`
- `core/navigation-link`
- `core/site-logo`
- `core/post-title`
- `core/query-title`

## General Tips for Block-Level Links

### ✅ Best Practices

1. **Inspector sidebar is key**: Most block-level links have XFN panel open by default
2. **Save often**: Changes are automatic but save your post/page regularly
3. **Test on frontend**: Verify links work and rel attributes are present
4. **Use "Me" liberally**: For all your own sites and profiles

### ⚠️ Common Mistakes

1. **Don't add XFN to internal links**: Links within your own site don't need XFN
2. **Don't overuse**: Only use for actual relationships
3. **Don't forget to add the link first**: XFN needs a URL to work with

## Checking Block-Level XFN

### In the Editor
1. **Select the block**
2. **Check Inspector sidebar** - see "XFN Relationships" panel
3. **Look for relationship count** badge
4. **Verify active relationships** in the summary

### On the Frontend
1. **View the published page**
2. **Right-click the element** (menu item, logo, etc.)
3. **Select "Inspect Element"**
4. **Look for**: `<a href="..." rel="friend met">`

### Using Browser DevTools
```javascript
// Find all elements with XFN relationships
document.querySelectorAll('[rel*="friend"], [rel*="colleague"], [rel*="me"]')
```

## Troubleshooting

### Problem: XFN panel not showing for Navigation
**Solution**:
- Make sure you've selected a navigation **item**, not the navigation **block**
- Click directly on the link text
- Check that the navigation item has a URL

### Problem: Can't edit Site Logo link
**Solution**:
- Site Logo links are usually controlled by WordPress core
- External links may require custom code or a plugin
- Check your theme documentation for Site Logo link options

### Problem: Post Title XFN not working
**Solution**:
- Verify the post title is actually a link (may be just text)
- Check if it's linking externally or internally
- Internal links don't need XFN

### Problem: Changes not saving in templates
**Solution**:
- Make sure you saved the template
- Clear any caching plugins
- Check that you're editing the right template (Header vs Footer)

## Related Documentation

- [Paragraph Links](paragraph-links.md) - For inline text links
- [Button Links](button-links.md) - For Button block links
- [Image Links](image-links.md) - For clickable images

## Need More Help?

- Visit the [WordPress.org support forum](https://wordpress.org/support/plugin/link-extension-for-xfn/)
- Report issues on [GitHub](https://github.com/courtneyr-dev/link-extension-for-xfn/issues)
- Read the [XFN 1.1 specification](http://gmpg.org/xfn/)
