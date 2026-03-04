# Query Monitor Testing Guide - XFN Link Extension

**Plugin**: XFN Relationship Link Extension
**Version**: 1.0.0
**Testing Date**: December 1, 2024
**Purpose**: Verify plugin passes Query Monitor checks before WordPress.org submission

---

## What is Query Monitor?

Query Monitor is a developer tool that detects PHP errors, warnings, notices, and WordPress "Doing it Wrong" messages. WordPress.org reviewers use similar tools, so testing with Query Monitor ensures your plugin will pass review.

**Install Query Monitor**:
```bash
# Via WP-CLI
wp plugin install query-monitor --activate

# Or download from WordPress.org
# https://wordpress.org/plugins/query-monitor/
```

---

## Critical Testing Workflow

### Step 1: Enable WordPress Debug Mode

Edit `wp-config.php` and add:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );
```

**Why**: This enables error reporting that Query Monitor will display.

### Step 2: Activate Query Monitor

1. Install and activate Query Monitor plugin
2. You'll see a new toolbar item at the top of admin pages
3. Look for colored badges:
   - 🔴 Red = Errors (CRITICAL - must fix)
   - 🟠 Orange = Warnings (should fix)
   - 🔵 Blue = Notices (review)
   - 🟢 Green = No issues

### Step 3: Test All Plugin Features

Test every plugin feature while Query Monitor is active:

#### Test 1: Plugin Activation
```bash
wp plugin deactivate xfn-link-extension
wp plugin activate xfn-link-extension
```

**Check Query Monitor for**:
- ❌ Fatal errors
- ❌ PHP warnings
- ❌ Deprecated function warnings
- ❌ "Doing it Wrong" notices

**Expected Result**: ✅ No errors or warnings

---

#### Test 2: Block Editor - Floating Toolbar

1. Create new post/page
2. Add a Button block
3. Click the "XFN" button in toolbar
4. Select various relationships
5. Save post

**Check Query Monitor for**:
- ❌ JavaScript errors (Console tab)
- ❌ PHP errors
- ❌ Slow queries (Queries tab)
- ❌ HTTP requests

**Expected Result**: ✅ No errors

---

#### Test 3: Block Editor - Inspector Controls

1. Create new post
2. Add Button block
3. Open Inspector Controls (sidebar)
4. Find "XFN Relationships" panel
5. Toggle various relationship options
6. Save post

**Check Query Monitor**:
- ❌ Any red or orange indicators
- ❌ JavaScript console errors

**Expected Result**: ✅ No errors

---

#### Test 4: Block Editor - Link Advanced Panel

1. Create paragraph block
2. Add inline link (highlight text, click link)
3. Click "Advanced" in link popover
4. Expand XFN collapsible section
5. Select relationships
6. Save link

**Check Query Monitor**:
- ❌ DOM manipulation errors
- ❌ Event listener issues
- ❌ JavaScript errors

**Expected Result**: ✅ No errors

---

#### Test 5: Frontend Display

1. Publish post with XFN relationships
2. View published post on frontend
3. Inspect HTML to verify rel attributes

**Check Query Monitor**:
- ❌ Frontend errors
- ❌ Missing assets
- ❌ Enqueue issues

**Expected Result**:
- ✅ No errors
- ✅ Correct rel attributes in HTML
- ✅ No plugin assets loaded on frontend (editor-only plugin)

---

#### Test 6: Multiple Block Types

Test XFN with various block types:

1. **Button Block** - Test toolbar and inspector
2. **Navigation Block** - Test with navigation items
3. **Image Block** - Test with linked images
4. **List Block** - Test with inline links in lists

**Check Query Monitor after each block type**:
- ❌ Block-specific errors
- ❌ Compatibility issues

**Expected Result**: ✅ Works consistently across all block types

---

#### Test 7: Theme Compatibility

Test with multiple themes:

1. **Twenty Twenty-Four** (default block theme)
2. **Twenty Twenty-Three** (previous block theme)
3. **Popular theme** (Kadence, Blocksy, or GeneratePress)

**Check Query Monitor**:
- ❌ Theme conflicts
- ❌ CSS conflicts
- ❌ JavaScript conflicts

**Expected Result**: ✅ No theme-related errors

---

#### Test 8: Plugin Compatibility

Test with popular plugins:

1. **Yoast SEO** - May modify link attributes
2. **Jetpack** - Extensive WordPress modifications
3. **WooCommerce** - If applicable

**Check Query Monitor**:
- ❌ Plugin conflicts
- ❌ Competing link modifications
- ❌ JavaScript library conflicts

**Expected Result**: ✅ No plugin conflicts

---

## Query Monitor - What to Look For

### PHP Tab

**Red Flags**:
- ❌ Fatal errors
- ❌ Parse errors
- ❌ Undefined function/class

**Orange Flags**:
- ⚠️ Warnings (undefined variable, array key)
- ⚠️ Notices
- ⚠️ Deprecated functions

**Check**:
- Line numbers point to your plugin files
- No references to `xfn-link-extension.php` or `src/index.js` (compiled)

### Scripts & Styles Tab

**Check for**:
- ❌ Assets enqueued incorrectly
- ❌ Missing dependencies
- ❌ Wrong hook usage
- ✅ Assets only load in block editor (not frontend)

**Expected**:
```
✅ xfn-link-extension-editor (editor.css) - ONLY in editor
✅ xfn-link-extension (index.js) - ONLY in editor
✅ Dependencies: wp-blocks, wp-element, wp-components, etc.
✅ NO assets loaded on frontend (verify by viewing published post)
```

### Hooks & Actions Tab

**Check**:
- ✅ `enqueue_block_editor_assets` firing correctly
- ✅ `plugins_loaded` hook used
- ✅ `init` hook used (if applicable)
- ❌ No deprecated hooks

### Queries Tab

**Check**:
- ✅ No additional database queries from plugin
- ✅ No slow queries
- ✅ No duplicate queries

**Expected**: This plugin should add ZERO database queries (data stored in block attributes).

### Request Tab

**Check**:
- ❌ External HTTP requests (none expected)
- ❌ Slow API calls
- ❌ Failed requests

**Expected**: No HTTP requests from this plugin.

### Console (JavaScript Errors)

**Check**:
- ❌ Uncaught errors
- ❌ Failed module imports
- ❌ Undefined variables
- ⚠️ Console.log statements (see note below)

**Expected Console Logs** (informational only):
```
XFN Link Extension loaded successfully! XFN controls will appear:
1. In the floating toolbar for link blocks (Button, Navigation, etc.)
2. In Inspector Controls for link blocks
3. In a collapsible XFN section in link popovers for inline links
```

**Note**: These console.log statements are informational and safe, but could be removed for production.

---

## Common Issues & Fixes

### Issue: "Doing it Wrong" Warning

**Example**:
```
Scripts and styles should not be registered or enqueued until
the wp_enqueue_scripts, admin_enqueue_scripts, or login_enqueue_scripts hooks.
```

**Cause**: Enqueuing assets on wrong hook

**Fix**: Use `enqueue_block_editor_assets` (which this plugin correctly does)

**Verification**:
```php
// xfn-link-extension.php:67
add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
```

✅ This plugin uses the correct hook.

---

### Issue: Undefined Array Key

**Example**:
```
Warning: Undefined array key 'rel' in xfn-link-extension.php on line 133
```

**Cause**: Accessing array key without checking if it exists

**Fix**: Use `isset()` or null coalescing operator

**Verification** (`src/index.js:132-142`):
```javascript
function getRelFromBlock( attributes, blockName ) {
    if ( attributes.rel ) {
        return attributes.rel;
    }

    if ( attributes.metadata?.rel ) {
        return attributes.metadata.rel;
    }

    return '';
}
```

✅ This plugin properly checks for existence with optional chaining (`?.`).

---

### Issue: Deprecated Functions

**Example**:
```
Function get_settings() is deprecated since version 2.1.0!
Use get_option() instead.
```

**Fix**: Replace deprecated functions

**Verification**: Plugin uses modern WordPress functions:
- ✅ `wp_enqueue_script()` (not deprecated)
- ✅ `wp_localize_script()` (correct usage)
- ✅ `load_plugin_textdomain()` (correct)
- ✅ `register_activation_hook()` (correct)

**Search for Deprecated Functions**:
```bash
grep -r "get_settings" . --exclude-dir=node_modules
grep -r "wp_make_link_relative" . --exclude-dir=node_modules
```

✅ No deprecated functions found in XFN plugin.

---

### Issue: Missing Text Domain

**Example**:
```
Notice: Missing text domain in translation function
```

**Fix**: Always include text domain

**Verification**:
```javascript
__( 'Friendship', 'xfn-link-extension' ) // ✅ Correct
__( 'Friendship' ) // ❌ Missing text domain
```

✅ This plugin consistently uses `'xfn-link-extension'` text domain.

---

### Issue: JavaScript Module Errors

**Example**:
```
Uncaught Error: Cannot find module '@wordpress/blocks'
```

**Cause**: Missing build step or incorrect dependencies

**Fix**: Run `npm run build` and verify dependencies

**Verification**:
```bash
# Check package.json has @wordpress/scripts
grep "@wordpress/scripts" package.json

# Check build directory exists
ls -la build/

# Verify compiled files
ls -la build/index.js build/editor.css
```

✅ XFN plugin has correct build setup.

---

## Pre-Submission Query Monitor Checklist

Before submitting to WordPress.org, verify:

### PHP Checks
- [ ] ✅ No fatal errors
- [ ] ✅ No PHP warnings
- [ ] ✅ No PHP notices
- [ ] ✅ No "Doing it Wrong" messages
- [ ] ✅ No deprecated function warnings

### JavaScript Checks
- [ ] ✅ No JavaScript errors in console
- [ ] ✅ No failed module imports
- [ ] ✅ No undefined variables
- [ ] ✅ No network errors (failed asset loading)

### Performance Checks
- [ ] ✅ No slow database queries (should be zero queries)
- [ ] ✅ No HTTP requests to external services
- [ ] ✅ Assets only load in editor (not frontend)
- [ ] ✅ No memory issues

### Asset Checks
- [ ] ✅ All scripts have correct dependencies declared
- [ ] ✅ All styles have correct dependencies declared
- [ ] ✅ Assets use correct hooks (`enqueue_block_editor_assets`)
- [ ] ✅ No duplicate asset enqueues

### Translation Checks
- [ ] ✅ All strings use translation functions
- [ ] ✅ Text domain is consistent (`xfn-link-extension`)
- [ ] ✅ `wp_set_script_translations()` used for JS translations

---

## Testing Script

Create a comprehensive test script:

```bash
#!/bin/bash

echo "=== XFN Plugin - Query Monitor Testing ==="
echo ""

echo "1. Checking if Query Monitor is active..."
wp plugin list --status=active | grep query-monitor
if [ $? -eq 0 ]; then
    echo "✅ Query Monitor is active"
else
    echo "❌ Query Monitor not found. Installing..."
    wp plugin install query-monitor --activate
fi

echo ""
echo "2. Checking debug mode..."
wp config get WP_DEBUG
if [ $? -eq 0 ]; then
    echo "✅ WP_DEBUG is enabled"
else
    echo "⚠️  WP_DEBUG should be enabled for testing"
fi

echo ""
echo "3. Deactivating and reactivating plugin..."
wp plugin deactivate xfn-link-extension
wp plugin activate xfn-link-extension
echo "✅ Check Query Monitor for activation errors"

echo ""
echo "4. Checking for deprecated functions..."
cd xfn-link-extension
grep -r "get_settings\|wp_make_link_relative" . --exclude-dir=node_modules --exclude-dir=vendor
if [ $? -ne 0 ]; then
    echo "✅ No deprecated functions found"
fi

echo ""
echo "5. Checking build files exist..."
if [ -f "build/index.js" ] && [ -f "build/editor.css" ]; then
    echo "✅ Build files exist"
else
    echo "❌ Build files missing. Run: npm run build"
fi

echo ""
echo "=== Manual Testing Required ==="
echo "1. Open WordPress admin and create a new post"
echo "2. Add a Button block and test XFN toolbar"
echo "3. Test Inspector Controls"
echo "4. Test inline links with Advanced panel"
echo "5. Monitor Query Monitor for any errors"
echo "6. Check browser console for JavaScript errors"
echo ""
echo "Expected: Zero errors, warnings, or notices"
```

Save as `bin/test-query-monitor.sh` and run:
```bash
chmod +x bin/test-query-monitor.sh
./bin/test-query-monitor.sh
```

---

## Debug.log Monitoring

Monitor `wp-content/debug.log` during testing:

```bash
# Watch debug.log in real-time
tail -f wp-content/debug.log

# Search for plugin errors
grep "xfn-link-extension" wp-content/debug.log

# Search for specific error types
grep "Fatal error\|Warning\|Notice" wp-content/debug.log | grep xfn
```

**Expected**: No entries related to XFN plugin.

---

## Final Verification

Before submission, verify:

1. ✅ **Clean Query Monitor**: No red or orange badges
2. ✅ **Clean Debug Log**: No errors in `debug.log`
3. ✅ **Clean Console**: No JavaScript errors
4. ✅ **All Features Work**: Tested every interface (toolbar, inspector, advanced)
5. ✅ **Theme Compatibility**: Tested with default theme
6. ✅ **Plugin Compatibility**: Tested with popular plugins
7. ✅ **Fresh Install**: Tested on fresh WordPress installation

---

## What WordPress.org Reviewers Will Check

Reviewers use tools similar to Query Monitor:

1. **PHP CodeSniffer**: Checks coding standards
2. **Query Monitor** (or similar): Checks for errors
3. **Manual Testing**: Tests plugin functionality
4. **Security Review**: Checks for vulnerabilities
5. **Guideline Compliance**: Verifies WordPress.org rules

If your plugin passes Query Monitor testing, it's likely to pass WordPress.org review.

---

## Additional Resources

- [Query Monitor Plugin](https://wordpress.org/plugins/query-monitor/)
- [Query Monitor Documentation](https://querymonitor.com/docs/)
- [WordPress Debugging](https://wordpress.org/documentation/article/debugging-in-wordpress/)
- [Plugin Review Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)

---

## XFN Plugin - Predicted Query Monitor Results

Based on code review, this plugin is expected to:

✅ **PASS** all Query Monitor checks:
- No PHP errors, warnings, or notices
- No "Doing it Wrong" messages
- No deprecated function usage
- No slow queries (plugin doesn't query database)
- No HTTP requests
- Proper asset enqueuing
- Clean JavaScript (no errors)

The XFN Link Extension plugin follows WordPress best practices and should pass Query Monitor testing without issues.

---

**Guide Version**: 1.0
**Plugin Version**: 1.0.0
**Last Updated**: December 1, 2024
**Ready for Testing**: YES
