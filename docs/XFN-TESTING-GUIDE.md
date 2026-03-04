# XFN Testing Guide

This guide will help you verify that XFN relationships are working correctly on your website's front end.

## Step 1: Create the Test Page

1. Go to your WordPress admin: `http://xfn.local/wp-admin`
2. Navigate to **Pages → Add New**
3. Give it a title: "XFN Testing Page"
4. Click the ⋮ (three dots) in the top right → **Code editor**
5. Copy all the content from `xfn-test-page-content.html`
6. Paste it into the code editor
7. Click **Publish**

## Step 2: Enable Inspector Controls

Before testing, make sure Inspector Controls are enabled:

1. Go to **Settings → Link Extension for XFN**
2. Check **Inspector Controls**
3. Click **Save Changes**

## Step 3: View the Test Page

1. Click **View Page** after publishing
2. You should see a page with various links, buttons, and an embed

## Step 4: Verify XFN Attributes

### Method A: Inspect Individual Links

1. **Right-click** on any link (e.g., "my friend John")
2. Select **Inspect** or **Inspect Element**
3. Look for the `<a>` tag in the HTML
4. Find the `rel` attribute

**What you should see:**
```html
<a href="https://example.com/friend" rel="friend met">my friend John</a>
```

### Method B: Use the Console Script

1. Open Developer Tools (F12 or Cmd+Option+I)
2. Go to the **Console** tab
3. Copy and paste this script:

```javascript
const xfnValues = ['contact', 'acquaintance', 'friend', 'met', 'co-worker',
                   'colleague', 'co-resident', 'neighbor', 'child', 'parent',
                   'sibling', 'spouse', 'kin', 'muse', 'crush', 'date',
                   'sweetheart', 'me'];

document.querySelectorAll('a[rel]').forEach(link => {
  const relValues = link.rel.split(' ');
  const xfnFound = relValues.filter(val => xfnValues.includes(val));

  if (xfnFound.length > 0) {
    console.log('✓ XFN Link:', link.textContent.trim());
    console.log('  XFN:', xfnFound.join(', '));
    console.log('  Full rel:', link.rel);
    console.log('  URL:', link.href);
    console.log('---');
  }
});
```

4. Press Enter
5. You should see a list of all links with XFN relationships

**Expected output:**
```
✓ XFN Link: my friend John
  XFN: friend, met
  Full rel: friend met
  URL: https://example.com/friend
---
✓ XFN Link: Sarah, my colleague
  XFN: colleague, met
  Full rel: colleague met
  URL: https://example.com/colleague
---
```

## Expected XFN Relationships on Test Page

| Link Text | Expected rel Attribute |
|-----------|----------------------|
| my friend John | `friend met` |
| Sarah, my colleague | `colleague met` |
| my personal website | `me` |
| Visit My Coworker (button) | `friend met colleague` |
| My Spouse (button) | `spouse` |
| Team Member (button) | `co-worker met` |
| My Profile (button) | `me` |
| Contact Person | `contact` |
| Acquaintance | `acquaintance met` |
| My Sibling | `sibling` |
| My Partner | `sweetheart met` |
| Portfolio Image | `friend acquaintance` |

## Testing Embed Blocks

**Important:** Embed blocks work differently!

1. In the WordPress editor, select the YouTube embed block
2. Look in the **Inspector Controls** (right sidebar)
3. You should see the **XFN Relationships** panel
4. The test page has `friend colleague` set

**On the front end:**
- Embed blocks typically render as `<iframe>` or other embed HTML
- They **do NOT** render as `<a>` tags
- The XFN data is stored in the block's metadata in the database
- It won't appear in the front-end HTML

To verify embed XFN data:
1. Go to the WordPress editor
2. Select the embed block
3. Click ⋮ (three dots) → **Copy**
4. Paste into a text editor
5. You'll see: `"metadata":{"rel":"friend colleague"}`

## Troubleshooting

### XFN attributes not appearing

**Check 1:** Make sure you're viewing the published page, not the editor.

**Check 2:** Clear your browser cache and hard refresh (Cmd+Shift+R or Ctrl+Shift+R).

**Check 3:** Check if other `rel` attributes are working:
```javascript
// Run in console
document.querySelectorAll('a[rel]').forEach(link => {
  console.log(link.href, '→', link.rel);
});
```

**Check 4:** View the page source (Cmd+U or Ctrl+U) and search for `rel="` to see if any rel attributes exist.

### Inspector Controls not showing in editor

1. Go to **Settings → Link Extension for XFN**
2. Make sure **Inspector Controls** is checked
3. Save and refresh the editor

### Button blocks not showing XFN

Button blocks store XFN in `metadata.rel`, not directly in the `rel` attribute. The plugin should convert this on the front end. Check:

1. View page source
2. Look for the button's `<a>` tag
3. The `rel` attribute should be present

## Success Criteria

✅ Inline paragraph links have `rel` attributes with XFN values
✅ Button blocks render with `rel` attributes
✅ Image blocks with links show `rel` attributes
✅ List items with links have `rel` attributes
✅ The console script finds and lists all XFN links
✅ Embed blocks store XFN in metadata (visible in editor)

## Additional Testing

### Test with your own links

1. Create a new post/page
2. Add a button or paragraph with a link
3. Apply XFN relationships via:
   - **Link Advanced Panel** (for inline links)
   - **Inspector Controls** (for button/image/embed blocks)
4. Publish and inspect the front end

### Test XFN + other rel values

XFN should combine with other rel values like `nofollow`:

1. Create a link with both XFN and other rel values
2. Expected: `rel="nofollow friend met"`

## Questions?

If XFN attributes aren't appearing on the front end:

1. Check the browser console for JavaScript errors
2. Verify the plugin is activated
3. Test with a default WordPress theme (Twenty Twenty-Four)
4. Disable other plugins to check for conflicts

---

**Files:**
- Test page content: `xfn-test-page-content.html`
- This guide: `XFN-TESTING-GUIDE.md`
