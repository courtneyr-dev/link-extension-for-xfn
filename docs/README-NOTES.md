# Important Notes for WordPress.org Submission

## CRITICAL: readme.txt Contributors Field

**Current Issue**: The `Contributors` field in readme.txt currently says "WordPress Telex" which is NOT a valid format.

**Required Action**: Update line 3 of readme.txt with your actual WordPress.org username(s):

```
Contributors: your-wporg-username
```

**Important**:
- Contributors MUST be actual WordPress.org usernames (lowercase, hyphens only)
- You need to create a WordPress.org account first if you don't have one
- Go to https://login.wordpress.org/register to create an account
- Multiple contributors are comma-separated: `contributor1, contributor2, contributor3`

**Example**:
```
Contributors: john-doe, jane-smith
```

## Other Pre-Submission Checklist

### Required Before Submission

- [ ] Update Contributors field in readme.txt with actual WordPress.org username(s)
- [ ] Create WordPress.org assets (icon, banners, screenshots)
- [ ] Test plugin with Query Monitor active (zero errors/warnings)
- [ ] Test with default theme (Twenty Twenty-Four)
- [ ] Verify "Tested up to" is current WordPress version
- [ ] Ensure "Stable tag" matches plugin version (currently 1.0.0)
- [ ] Run through accessibility testing checklist
- [ ] Test on multiple browsers (Chrome, Firefox, Safari)
- [ ] Verify no trademark violations in plugin name

### Security Checklist

- [ ] All user input is sanitized
- [ ] All output is escaped
- [ ] Nonces are verified (check if plugin has forms/AJAX)
- [ ] Proper capability checks
- [ ] No direct file access (checked - plugin has ABSPATH check)

### Code Quality

- [ ] Run PHP CodeSniffer with WordPress standards
- [ ] Run ESLint on JavaScript files
- [ ] Check for deprecated WordPress functions
- [ ] Verify no minified code without source maps
- [ ] Test deactivation/reactivation

## Plugin Information

- **Plugin Slug**: xfn-link-extension
- **Version**: 1.0.0
- **Requires at least**: 6.4
- **Tested up to**: 6.8
- **Requires PHP**: 7.4
- **License**: GPLv2 or later

## WordPress.org Submission Process

1. **Before Submission**
   - Complete all items in checklist above
   - Build final ZIP file: `npm run plugin-zip`
   - Test the ZIP in a fresh WordPress install
   - Verify ZIP excludes development files (.wordpress-org, src, node_modules)

2. **Submit to WordPress.org**
   - Go to https://wordpress.org/plugins/developers/add/
   - Upload plugin ZIP file
   - Wait for automated checks
   - Plugin enters manual review queue (can take 2-14 days)

3. **After Approval**
   - Checkout SVN repository
   - Commit plugin files to trunk
   - Tag release version (1.0.0)
   - Upload assets to SVN /assets directory separately

## Asset Requirements

See `.wordpress-org/DESIGN-PROMPT.md` and `.wordpress-org/ASSETS-CHECKLIST.md` for detailed requirements.

**Required Assets**:
- icon-256x256.png (transparent background)
- banner-772x250.png
- banner-1544x500.png (retina banner)
- screenshot-1.png through screenshot-8.png (1390×864px recommended)

All assets must be under 1MB each.

## Tags Strategy

Current tags in readme.txt:
- xfn
- links
- relationships
- accessibility
- gutenberg

These are well-chosen:
- `xfn` - Primary feature (exact match)
- `links` - Core functionality
- `relationships` - Descriptive of purpose
- `accessibility` - Key differentiator (WCAG 2.2 AA)
- `gutenberg` - Platform integration

Maximum 5 tags allowed - current tags are optimal.

## Author Information to Update

Current placeholder information that needs to be updated:

1. **Author**: WordPress Telex (verify if this is correct)
2. **Author URI**: (currently not set in main plugin file line 10)
3. **Plugin URI**: Currently set to https://github.com/courtneyr-dev/xfn-link-extension
4. **Contributors in readme.txt**: Change from "WordPress Telex" to actual WordPress.org username

## Build Verification

Before creating final ZIP:

```bash
# Install dependencies
npm install

# Build production version
npm run build

# Verify build directory exists and contains:
ls -la build/
# Should show: index.js, index.css, editor.css, view.js, block.json

# Create plugin ZIP
npm run plugin-zip

# Verify ZIP contents (should NOT include):
unzip -l xfn-link-extension.zip | grep -E "(node_modules|src/|.wordpress-org|.git)"
# Should return nothing

# Verify ZIP DOES include:
unzip -l xfn-link-extension.zip | grep -E "(build/|readme.txt|xfn-link-extension.php)"
# Should show these files
```
