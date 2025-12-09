# Plugin Naming Update Summary

## Issue
WordPress.org was showing incorrect references:
- Installation directory: `/wp-content/plugins/xfn-link-extension`
- Settings menu: "Settings → XFN Link Extension"

## Changes Made

### 1. Documentation Files

**readme.txt** (WordPress.org readme)
- Line 338: Installation directory corrected to `link-extension-for-xfn`
- Lines 37, 67, 78, 342: Settings menu references updated

**README.md** (GitHub readme)
- Settings menu references updated throughout

**docs/settings.md**
- Settings page title and menu references updated

**docs/README.md**
- Settings menu references updated

### 2. Code Files

**link-extension-for-xfn.php**
- Line 92: Settings page title: "Link Extension for XFN Settings"
- Line 93: Settings menu item: "Link Extension for XFN"
- Line 573: Activation error: "Link Extension for XFN requires..."
- Line 582: PHP error: "Link Extension for XFN requires..."

**src/index.js**
- Lines 1536, 1542: Console log messages updated
- Rebuilt to `build/index.js`

### 3. What Changed

**Before:**
- Settings → **XFN Link Extension**
- `/wp-content/plugins/**xfn-link-extension**`
- "**XFN Link Extension** requires WordPress..."

**After:**
- Settings → **Link Extension for XFN**
- `/wp-content/plugins/**link-extension-for-xfn**`
- "**Link Extension for XFN** requires WordPress..."

### 4. What Was NOT Changed

These remain as-is (intentionally):
- GitHub repository URLs (still `/xfn-link-extension/`)
- Plugin class name: `XFN_Link_Extension`
- Function prefixes: `xfn_link_extension_*`
- Text domain: `link-extension-for-xfn`
- Historical references in reports and documentation

### 5. User-Visible Changes

When users see the plugin:
- ✅ Settings menu shows: "Link Extension for XFN"
- ✅ Settings page title: "Link Extension for XFN Settings"
- ✅ Installation instructions show correct directory
- ✅ Error messages use correct plugin name
- ✅ Console logs reference correct settings path

## Commits

1. `5cf0682` - Fix plugin naming: use 'Link Extension for XFN' consistently
2. `05f49f2` - Update plugin name in PHP and JavaScript code

## Next Step

Deploy to WordPress.org to publish these corrections:

```bash
./deploy-to-wordpress-org.sh
```
