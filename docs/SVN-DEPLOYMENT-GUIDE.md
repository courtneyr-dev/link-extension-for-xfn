# WordPress.org SVN Deployment Guide

Quick reference for deploying Link Extension for XFN to WordPress.org.

The plugin is live at https://wordpress.org/plugins/link-extension-for-xfn/. Released tags
are `1.0.0`, `1.0.1`, `1.0.3`, and `1.0.4` — `1.0.4` is the current stable tag. Examples below
use `1.0.4`; substitute the version you are shipping.

Deployment is manual. Nothing in `.github/workflows/ci.yml` publishes to WordPress.org — CI
runs lint, PHPStan, tests, and `composer audit` only. A merged pull request or a version bump
does not put anything on WordPress.org until someone runs the script below.

## Prerequisites

- ✅ SVN installed
- ✅ WordPress.org account with commit access to the plugin
- ✅ A generated WordPress.org SVN password (your account password will not authenticate)
- ✅ All files committed to Git, and `npm run build` output committed

## One-Command Deployment

Run the automated deployment script:

```bash
./deploy-to-wordpress-org.sh
```

The script will:
1. ✅ Checkout the SVN repository from WordPress.org
2. ✅ Copy plugin files to trunk (excluding dev files via .distignore)
3. ✅ Copy assets (screenshots, banners, icons) to assets directory
4. ✅ Add new files and remove deleted files automatically
5. ✅ Show you what will be committed for review
6. ✅ Commit to trunk with your approval
7. ✅ Create version tag (for example `1.0.4`)
8. ✅ Clean up temporary files

## What Gets Deployed

### Plugin Files (to trunk/)
Verified against the deployed `trunk/` on 2026-08-20:

- ✅ `link-extension-for-xfn.php` - Main plugin file
- ✅ `readme.txt` - WordPress.org readme
- ✅ `/build/` - Compiled JavaScript and CSS
- ✅ `/includes/` - PHP classes
- ✅ `/assets/blueprints/` - Playground blueprint
- ✅ `blueprint.json`, `LICENSE`
- ❌ `/src/` - **Excluded** via `.distignore`. The distributed plugin ships compiled
  `build/` output only; uncompiled source lives in the GitHub repository
- ❌ `/node_modules/` - Excluded
- ❌ `.git/`, `.github/` - Excluded
- ❌ Development docs (CHANGELOG.md, CONTRIBUTING.md, etc.) - Excluded

### Assets (to assets/)
- ✅ `screenshot-1.png` through `screenshot-7.png`
- ✅ `banner-772x250.png` - Small banner
- ✅ `banner-1544x500.png` - Large banner
- ✅ `icon-256x256.png`, `icon-128x128.png`, `icon.svg` - Plugin icons

Note: the deploy rsyncs all of `.wordpress-org/` into `assets/`, so the eight design and
checklist markdown files in that directory are currently published to SVN alongside the
images. Harmless, but they do not belong there — exclude them if you tidy the deploy script.

## Manual Deployment (If Needed)

If you prefer to do it manually:

### 1. Checkout SVN Repository

```bash
svn co https://plugins.svn.wordpress.org/link-extension-for-xfn /tmp/link-extension-for-xfn-svn
cd /tmp/link-extension-for-xfn-svn
```

### 2. Copy Plugin Files to Trunk

```bash
rsync -rc \
    --exclude-from="$OLDPWD/.distignore" \
    --exclude=".git" \
    --exclude=".github" \
    --delete \
    /path/to/plugin/ trunk/
```

### 3. Copy Assets

```bash
rsync -rc --delete /path/to/plugin/.wordpress-org/ assets/
```

### 4. Add New Files

```bash
svn status | grep '^?' | awk '{print $2}' | xargs -I{} svn add {}@
```

### 5. Remove Deleted Files

```bash
svn status | grep '^!' | awk '{print $2}' | xargs -I{} svn rm {}@
```

### 6. Review Changes

```bash
svn status
svn diff | less
```

### 7. Commit to Trunk

```bash
svn ci -m "Deploy version 1.0.4 to trunk"
```

### 8. Create Tag

```bash
svn cp trunk tags/1.0.4
svn ci tags/1.0.4 -m "Tagging version 1.0.4"
```

## After Deployment

### Verify on WordPress.org

1. **Plugin Page**: https://wordpress.org/plugins/link-extension-for-xfn/
2. **SVN Browser**: https://plugins.svn.wordpress.org/link-extension-for-xfn/

### Check These Items

- ✅ Screenshots appear correctly
- ✅ Banner and icon display properly
- ✅ Version number matches the tag you just pushed (currently 1.0.4)
- ✅ Download button works
- ✅ "Tested up to" shows WordPress 7.1
- ✅ Installation instructions are clear

## Troubleshooting

### Authentication Failed

```bash
# SVN stores credentials, you may need to re-enter them
svn --username your-wordpress-username co https://plugins.svn.wordpress.org/link-extension-for-xfn/
```

### Files Not Showing Up

- Check `.distignore` to ensure files aren't accidentally excluded
- Make sure you ran `svn add` for new files
- Verify files are in trunk/ directory before committing

### Assets Not Displaying

- Assets must be in the `assets/` directory (not trunk/assets/)
- File names must match exactly: `screenshot-1.png`, `banner-772x250.png`, etc.
- Images should be optimized (< 1MB each)

### Wrong Version Tag

```bash
# Delete a tag if needed
svn rm https://plugins.svn.wordpress.org/link-extension-for-xfn/tags/1.0.4 -m "Remove incorrect tag"

# Then recreate it
svn cp https://plugins.svn.wordpress.org/link-extension-for-xfn/trunk \
       https://plugins.svn.wordpress.org/link-extension-for-xfn/tags/1.0.4 \
       -m "Tagging version 1.0.4"
```

## Important Notes

### Don't Commit These

- ❌ `node_modules/`
- ❌ `.git/` directory
- ❌ Development documentation
- ❌ Test files
- ❌ Build configuration files

### Do Commit These

- ✅ `/build/` directory (compiled assets — these must be committed to Git, not just built locally)
- ✅ `/includes/` directory (PHP classes)
- ✅ `readme.txt` (WordPress.org format)
- ✅ Main plugin PHP file
- ✅ Assets directory with screenshots/banners

### Version Numbers

For the current release these are all `1.0.4`:

- Version in `link-extension-for-xfn.php` (header **and** the `XFN_LINK_EXTENSION_VERSION` constant)
- Stable tag in `readme.txt`
- Version in `package.json`
- SVN tag: `tags/1.0.4`

**These must all match.** They have drifted before: `package.json` sat at `1.1.0` after the
`1.1.1 → 1.0.4` renumber until it was corrected. Check all four.

Public numbering runs 1.0.0 → 1.0.1 → 1.0.3 → 1.0.4. There is no 1.0.2 tag on SVN, and
1.1.0 was a GitHub-only tag that never reached WordPress.org — do not cite either as a
released version.

## Future Updates

For each subsequent release:

1. Update version numbers in:
   - `link-extension-for-xfn.php` (header and `XFN_LINK_EXTENSION_VERSION` constant)
   - `readme.txt` (Stable tag)
   - `package.json` (for consistency)
   - Confirm `Tested up to:` reflects the newest WordPress you have actually tested against

2. Run the deployment script again:
   ```bash
   ./deploy-to-wordpress-org.sh
   ```

3. Script will automatically create new tag

## Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Using Subversion with WordPress.org](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/)
- [Plugin Assets](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/)

## Quick Commands Reference

```bash
# Check SVN status
svn status

# See what changed
svn diff | less

# Revert a file
svn revert path/to/file

# Update from remote
svn up

# View commit history
svn log --limit 5

# View a specific tag
svn ls https://plugins.svn.wordpress.org/link-extension-for-xfn/tags/
```
