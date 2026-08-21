# Manual Setup - Local WordPress Testing

## Option 1: Automated Script (EASIEST)

I created an automated setup script for you!

```bash
cd /path/to/link-extension-for-xfn
./setup-local-test.sh
```

This will:
- ✅ Find your Local sites
- ✅ Install Query Monitor, Debug Bar, Plugin Check
- ✅ Install your XFN plugin
- ✅ Enable debug mode
- ✅ Give you testing instructions

**Continue to Option 2 if script doesn't work or you prefer manual setup.**

---

## Option 2: Manual Setup (Step-by-Step)

### Step 1: Create New Local Site (5 minutes)

1. **Open Local by Flywheel**
   - Find in Applications or use Spotlight (Cmd+Space, type "Local")

2. **Create New Site**
   - Click "+" or "Create a new site"
   - Site name: `xfn-test` (or any name)
   - Click "Continue"

3. **Choose Environment**
   - Select "Preferred" (recommended)
   - Click "Continue"

4. **Setup WordPress**
   - WordPress Username: `admin`
   - WordPress Password: `password` (or your choice)
   - WordPress Email: your email
   - Click "Add Site"

5. **Wait for Setup**
   - Takes 2-3 minutes
   - Site will start automatically

6. **Note Site Details**
   - Site URL (usually: http://xfn-test.local)
   - Admin URL (usually: http://xfn-test.local/wp-admin)

---

### Step 2: Enable Debug Mode (2 minutes)

**Option A: Using WP-CLI (Recommended)**

```bash
# Navigate to your site's WordPress directory
cd ~/Local\ Sites/xfn-test/app/public

# Enable debug mode
wp config set WP_DEBUG true --raw --type=constant
wp config set WP_DEBUG_LOG true --raw --type=constant
wp config set WP_DEBUG_DISPLAY false --raw --type=constant
wp config set SCRIPT_DEBUG true --raw --type=constant
```

**Option B: Manual Edit**

1. In Local, click your site → "Open site shell"
2. Or navigate manually:
   ```bash
   cd ~/Local\ Sites/xfn-test/app/public
   nano wp-config.php
   ```
3. Add before `/* That's all, stop editing! */`:
   ```php
   define( 'WP_DEBUG', true );
   define( 'WP_DEBUG_LOG', true );
   define( 'WP_DEBUG_DISPLAY', false );
   define( 'SCRIPT_DEBUG', true );
   ```
4. Save (Ctrl+O, Enter, Ctrl+X)

---

### Step 3: Install Testing Plugins (5 minutes)

**Option A: Using WP-CLI (Fastest)**

```bash
cd ~/Local\ Sites/xfn-test/app/public

# Install Query Monitor
wp plugin install query-monitor --activate

# Install Debug Bar
wp plugin install debug-bar --activate

# Install Plugin Check
wp plugin install plugin-check --activate
```

**Option B: Via WordPress Admin**

1. Login to WordPress admin: http://xfn-test.local/wp-admin
   - Username: `admin`
   - Password: (what you set)

2. **Install Query Monitor**:
   - Go to Plugins → Add New
   - Search: "Query Monitor"
   - Click "Install Now" → "Activate"

3. **Install Debug Bar**:
   - Plugins → Add New
   - Search: "Debug Bar"
   - Click "Install Now" → "Activate"

4. **Install Plugin Check**:
   - Plugins → Add New
   - Search: "Plugin Check"
   - Click "Install Now" → "Activate"

---

### Step 4: Install XFN Plugin (2 minutes)

**Option A: Using WP-CLI**

```bash
cd ~/Local\ Sites/xfn-test/app/public

# Copy plugin ZIP to plugins directory
cp /path/to/link-extension-for-xfn/link-extension-for-xfn.zip wp-content/plugins/

# Unzip
cd wp-content/plugins
unzip link-extension-for-xfn.zip
rm link-extension-for-xfn.zip

# Activate
wp plugin activate link-extension-for-xfn
```

**Option B: Via WordPress Admin**

1. Go to Plugins → Add New
2. Click "Upload Plugin"
3. Click "Choose File"
4. Navigate to: `/path/to/link-extension-for-xfn/`
5. Select: `link-extension-for-xfn.zip`
6. Click "Install Now"
7. Click "Activate Plugin"

---

### Step 5: Verify Setup (2 minutes)

1. **Check Admin Toolbar**
   - You should see "Query Monitor" in admin toolbar (top)
   - It may have a colored badge:
     - 🟢 Green = No issues
     - 🔵 Blue = Notices
     - 🟠 Orange = Warnings
     - 🔴 Red = Errors

2. **Check Plugin List**
   - Go to Plugins
   - Verify activated:
     - ✅ Query Monitor
     - ✅ Debug Bar
     - ✅ Plugin Check
     - ✅ XFN Link Extension

3. **Check for Activation Errors**
   - Click "Query Monitor" in toolbar
   - Check "PHP Errors" tab
   - Should be empty (no errors)

---

## Step 6: Test XFN Plugin (10-15 minutes)

### Test 1: Inspector Controls

1. **Keep Button Block Selected**
2. **Open Inspector** (right sidebar)
3. **Find "XFN Relationships" Panel**
4. **Expand Panel**
5. **Select Different Relationships**
   - Use radio buttons for exclusive choices
   - Use checkboxes for multiple selections
6. **Verify Active Relationships Show**

### Test 2: Link Advanced Panel

1. **Add Paragraph Block**
2. **Type Some Text**: "Visit my website"
3. **Highlight "my website"**
4. **Click Link Button** (or Cmd+K)
5. **Enter URL**: https://example.com
6. **Click "Advanced"**
7. **Find XFN Collapsible Section**
8. **Expand and Select Relationships**
9. **Click "Submit" to Save Link**

### Test 3: Verify Output

1. **Save Post as Draft**
2. **Preview Post**
3. **Right-Click Button** → Inspect Element
4. **Check rel Attribute**:
   ```html
   <a href="..." rel="friend met" class="...">
   ```
5. **Verify XFN Values Present**

---

## Step 7: Check for Errors (5 minutes)

### Query Monitor Checks

1. **Click "Query Monitor" in Toolbar**
2. **Check Each Tab**:
   - **PHP Errors**: Should be empty ✅
   - **Deprecated**: Should be empty ✅
   - **Queries**: Should not add queries ✅
   - **HTTP**: Should be empty (no external requests) ✅
   - **Scripts**: Verify assets load ✅
   - **Hooks**: Verify plugin hooks fire ✅

### Debug Log Check

```bash
# View debug log
tail -f ~/Local\ Sites/xfn-test/app/public/wp-content/debug.log

# Should see no errors related to link-extension-for-xfn
# Press Ctrl+C to stop watching
```

### Browser Console Check

1. **Open Browser DevTools** (F12 or Cmd+Option+I)
2. **Go to Console Tab**
3. **Should See**: nothing from the plugin. Its logging runs through an `xfnLog` helper
   gated on `XFN_DEBUG`, which is hardcoded `false` in `src/index.js`, so a normal build is
   silent. Silence here is the pass condition — it does not mean the plugin failed to load.
   (Confirm loading by selecting a link and checking that the XFN section appears, or by
   running `wp plugin list` and seeing `link-extension-for-xfn` active.)
4. **Should NOT See**:
   - Red error messages
   - Failed module imports
   - Uncaught exceptions

---

## Step 8: Run Plugin Check (5 minutes)

1. **Go to Tools → Plugin Check**
2. **Select "XFN Link Extension"**
3. **Click "Check Plugin"**
4. **Review Results**:
   - Checks coding standards
   - Checks security issues
   - Checks best practices
   - Checks WordPress.org requirements

5. **Fix Any Issues** (if found)

---

## Testing Checklist

Complete this checklist during testing:

### Functionality
- [ ] XFN section appears in the Link Advanced panel
- [ ] XFN Relationships panel appears in Inspector Controls
- [ ] XFN collapsible section expands/collapses
- [ ] Relationship selection works (radio buttons)
- [ ] Relationship selection works (checkboxes)
- [ ] Active relationships pills display
- [ ] Count badge shows correct number
- [ ] Inspector Controls panel works
- [ ] Link Advanced panel integration works
- [ ] Inline links work in Paragraphs
- [ ] Button blocks work
- [ ] Navigation blocks work (if tested)
- [ ] Relationships save correctly
- [ ] rel attribute appears in published HTML
- [ ] Multiple relationships combine correctly

### Error Checks
- [ ] Query Monitor shows zero PHP errors
- [ ] Query Monitor shows zero warnings
- [ ] Query Monitor shows zero "Doing it Wrong" notices
- [ ] No deprecated function warnings
- [ ] No JavaScript console errors
- [ ] Debug log is clean (no xfn errors)
- [ ] Plugin Check passes all tests

### Browser Tests
- [ ] Chrome works
- [ ] Firefox works (if available)
- [ ] Safari works

### Theme Tests
- [ ] Twenty Twenty-Five works (default)
- [ ] Current active theme works

---

## Troubleshooting

### Plugin Doesn't Appear in Admin

**Check**:
```bash
cd ~/Local\ Sites/xfn-test/app/public
wp plugin list | grep xfn
```

**Should show**:
```
link-extension-for-xfn    1.0.4    active
```

**If not listed**:
```bash
# Verify files exist
ls -la wp-content/plugins/link-extension-for-xfn/

# Re-activate
wp plugin activate link-extension-for-xfn
```

### XFN Button Doesn't Show

**Causes**:
1. JavaScript not loading
2. Build files missing
3. Browser cache

**Fixes**:
1. Hard refresh: Cmd+Shift+R (Mac) or Ctrl+Shift+R (Windows)
2. Check Query Monitor → Scripts tab
3. Verify build files exist:
   ```bash
   ls -la ~/Local\ Sites/xfn-test/app/public/wp-content/plugins/link-extension-for-xfn/build/
   ```

### Query Monitor Shows Errors

**Red Badge - PHP Errors**:
1. Click Query Monitor
2. Go to PHP Errors tab
3. Read error message
4. Fix issue in plugin code
5. Re-upload plugin

**Orange Badge - Warnings**:
1. Usually safe to ignore if minor
2. Check if related to link-extension-for-xfn
3. Review code if XFN-related

### Debug Log Shows Errors

```bash
# Search for XFN-related errors
grep -i "xfn" ~/Local\ Sites/xfn-test/app/public/wp-content/debug.log

# View last 50 lines
tail -50 ~/Local\ Sites/xfn-test/app/public/wp-content/debug.log
```

---

## Quick Commands Reference

```bash
# Navigate to site
cd ~/Local\ Sites/xfn-test/app/public

# Check plugin status
wp plugin list

# View site info
wp core version
wp option get siteurl

# Check for errors
tail -f wp-content/debug.log

# Deactivate/reactivate plugin
wp plugin deactivate link-extension-for-xfn
wp plugin activate link-extension-for-xfn

# Clear cache
wp cache flush

# List all users
wp user list
```

---

## After Testing

### If Everything Works ✅

1. Take screenshots (see SCREENSHOT-CAPTURE-GUIDE.md)
2. Note: "Tested with Query Monitor - Zero errors"
3. Ready for WordPress.org submission!

### If Errors Found ❌

1. Document errors in Query Monitor
2. Check SECURITY-AUDIT-REPORT.md
3. Fix issues
4. Rebuild plugin ZIP
5. Re-test

---

## Clean Up (Optional)

When done testing:

```bash
# Deactivate testing plugins
cd ~/Local\ Sites/xfn-test/app/public
wp plugin deactivate query-monitor debug-bar plugin-check

# Keep XFN plugin active for screenshots
```

Or delete entire site:
1. Open Local
2. Right-click site
3. Choose "Delete"

---

## Next Steps

After successful testing:

1. ✅ **Take Screenshots**
   - Follow: SCREENSHOT-CAPTURE-GUIDE.md
   - Capture all 5 interfaces

2. ✅ **Create Graphics**
   - Follow: ICON-BANNER-QUICK-START.md
   - Icon and banners

3. ✅ **Submit to WordPress.org**
   - Upload: link-extension-for-xfn.zip
   - URL: https://wordpress.org/plugins/developers/add/

**You're ready! 🚀**
