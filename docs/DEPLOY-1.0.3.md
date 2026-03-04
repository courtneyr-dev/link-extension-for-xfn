# Deployment Guide: Version 1.0.3

## 🎉 What's New in 1.0.3

**Blueprint now works!** The WordPress Playground live preview is fully functional with:
- ✅ Working demo page that loads automatically
- ✅ XFN attributes properly rendered in HTML
- ✅ Button blocks show `rel` attributes
- ✅ Embed blocks show `data-xfn-rel` attributes
- ✅ Enhanced demo with instructions and testing guide

## 📦 Pre-Deployment Checklist

- [x] Version updated to 1.0.3 in all files
- [x] CHANGELOG.md updated
- [x] readme.txt changelog updated
- [x] Changes committed to git
- [x] Tag v1.0.3 created
- [x] Pushed to GitHub
- [x] Blueprint tested and working locally
- [ ] Deploy to WordPress.org SVN
- [ ] Enable Playground preview on WordPress.org
- [ ] Create GitHub release
- [ ] Test live preview on WordPress.org

## 🚀 Deployment Steps

### Step 1: Deploy to WordPress.org SVN

Run the deployment script:

```bash
cd /Users/crobertson/Downloads/xfn/link-extension-for-xfn
./deploy-to-wordpress-org.sh
```

**What it will do:**
1. Check out the SVN repository
2. Copy plugin files to trunk/
3. Copy blueprint to assets/blueprints/
4. Add new files to SVN
5. Ask for confirmation
6. Commit to trunk
7. Create tag 1.0.3
8. Commit the tag

**Or deploy manually:**

```bash
# Checkout SVN
SVN_URL="https://plugins.svn.wordpress.org/link-extension-for-xfn/"
SVN_DIR="/tmp/link-extension-for-xfn-svn"
svn co "$SVN_URL" "$SVN_DIR"

# Navigate to SVN directory
cd "$SVN_DIR"

# Copy plugin files to trunk (excluding dev files)
rsync -rc --exclude-from="/Users/crobertson/Downloads/xfn/link-extension-for-xfn/.distignore" \
  --exclude=".git" \
  --exclude=".github" \
  --exclude="deploy-to-wordpress-org.sh" \
  --delete \
  /Users/crobertson/Downloads/xfn/link-extension-for-xfn/ trunk/

# Copy blueprint to assets
mkdir -p assets/blueprints
cp /Users/crobertson/Downloads/xfn/link-extension-for-xfn/assets/blueprints/blueprint.json \
   assets/blueprints/

# Add new files
svn status | grep '^?' | awk '{print $2}' | xargs -I{} svn add {}@

# Commit
svn ci -m "Release version 1.0.3 - Fix WordPress Playground blueprint"

# Create tag
svn cp trunk tags/1.0.3
svn ci tags/1.0.3 -m "Tagging version 1.0.3"
```

### Step 2: Enable Playground Preview on WordPress.org

1. Go to: https://wordpress.org/plugins/link-extension-for-xfn/advanced/
2. Log in as plugin committer (courane01)
3. Scroll to **"Playground Preview"** section
4. Toggle **"Enable Live Preview"** to **ON**
5. Click **Save**

### Step 3: Create GitHub Release

1. Go to: https://github.com/courtneyr-dev/link-extension-for-xfn/releases/new
2. Select tag: **v1.0.3**
3. Release title: **Version 1.0.3 - Working Playground Blueprint**
4. Description:

```markdown
## 🎉 WordPress Playground Blueprint Now Works!

The live preview on WordPress.org is now fully functional! Visitors can instantly try the plugin with a working demo.

### What's Fixed

- **Blueprint loads correctly** with proper JSON structure
- **Demo page publishes** and loads at `/xfn-demo/` automatically
- **Button blocks show `rel` attributes** in rendered HTML (not just metadata)
- **Content filters disabled** so XFN attributes are preserved
- **Auto-login enabled** for seamless demo experience

### Enhanced Demo

- Visual indicators (emojis) for better readability
- Comprehensive testing instructions
- Table showing expected XFN results
- Better inline link examples with multiple attributes
- Clear DevTools inspection guide

### Try It Now

Visit the [plugin page](https://wordpress.org/plugins/link-extension-for-xfn/) and click the **Preview** button!

### Technical Details

- Uses `runPHPWithOptions` with environment variables (Playground best practice)
- Content preserved exactly as authored
- All XFN `rel` attributes properly rendered in frontend HTML
- Includes CORS-enabled development server for local testing

See [CHANGELOG.md](https://github.com/courtneyr-dev/link-extension-for-xfn/blob/main/CHANGELOG.md) for complete details.
```

5. Click **Publish release**

### Step 4: Test Live Preview

After SVN deployment and enabling the preview:

1. Visit: https://wordpress.org/plugins/link-extension-for-xfn/
2. Look for the **"Preview"** or **"Try it out"** button
3. Click it to launch WordPress Playground
4. Verify:
   - Demo page loads at `/xfn-demo/`
   - You're automatically logged in
   - XFN attributes are visible in HTML
   - Button has `rel="friend met colleague"`
   - YouTube embed has `data-xfn-rel="friend met"`

## 🔍 Verification Commands

Check if blueprint is on WordPress.org SVN:

```bash
svn ls https://plugins.svn.wordpress.org/link-extension-for-xfn/assets/blueprints/
```

Download and validate blueprint from WordPress.org:

```bash
curl -s https://plugins.svn.wordpress.org/link-extension-for-xfn/assets/blueprints/blueprint.json \
  | python3 -m json.tool | head -20
```

Test GitHub version:

```bash
curl -s https://raw.githubusercontent.com/courtneyr-dev/link-extension-for-xfn/main/assets/blueprints/blueprint.json \
  | python3 -m json.tool | head -20
```

## 📊 Post-Deployment Checklist

After deployment, verify:

- [ ] Plugin shows version 1.0.3 on WordPress.org
- [ ] Changelog displays correctly
- [ ] Blueprint file exists in SVN assets/
- [ ] Preview button appears on plugin page
- [ ] Clicking preview launches Playground successfully
- [ ] Demo loads at `/xfn-demo/` page
- [ ] XFN attributes visible in browser DevTools
- [ ] GitHub release created
- [ ] All links in README work

## 🎯 Expected Results

### On WordPress.org Plugin Page

- Version shows **1.0.3**
- **Preview** button is visible
- Changelog shows 1.0.3 entry

### In WordPress Playground

- Loads without errors
- Lands at `/xfn-demo/` page
- Demo content visible with:
  - Inline links with `rel` attributes
  - Button with `rel="friend met colleague"`
  - YouTube embed with `data-xfn-rel="friend met"`
  - Instructions and testing table

### Browser DevTools Inspection

```html
<!-- Inline link -->
<a href="https://example.com/sarah" rel="colleague met co-worker">Sarah, my colleague</a>

<!-- Button -->
<a class="wp-block-button__link" href="..." rel="friend met colleague">Visit My Friend & Colleague</a>

<!-- Embed figure -->
<figure class="wp-block-embed..." data-xfn-rel="friend met">
```

## 🐛 Troubleshooting

### Blueprint doesn't load
- Clear browser cache
- Wait 5-10 minutes for WordPress.org CDN to update
- Check browser console for errors
- Verify preview is enabled in Advanced settings

### XFN attributes missing
- Check if plugin activated in Playground
- Verify Inspector Controls enabled
- Check if content filters disabled in blueprint

### Preview button doesn't appear
- Verify blueprint.json in assets/blueprints/
- Check file is valid JSON
- Ensure preview enabled in Advanced settings
- Allow up to 1 hour for WordPress.org to detect blueprint

## 📞 Support

- GitHub Issues: https://github.com/courtneyr-dev/link-extension-for-xfn/issues
- WordPress.org Support: https://wordpress.org/support/plugin/link-extension-for-xfn/

---

**Deployment prepared:** 2024-12-09
**Version:** 1.0.3
**Status:** Ready for deployment
