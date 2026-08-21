# WordPress.org listing notes

Reference notes for the Link Extension for XFN listing on WordPress.org. The original
submission is complete — the plugin has been live since the 1.0.0 release, and this file now
records the current listing facts and the checks worth repeating at each release.

For the deployment procedure itself see [SVN-DEPLOYMENT-GUIDE.md](SVN-DEPLOYMENT-GUIDE.md).
For the per-release checklist see [PRE-SUBMISSION-CHECKLIST.md](PRE-SUBMISSION-CHECKLIST.md).

## Plugin information

| Field | Value |
|---|---|
| Plugin name | Link Extension for XFN |
| Slug | `link-extension-for-xfn` |
| Main file | `link-extension-for-xfn.php` |
| Text domain | `link-extension-for-xfn` (matches the slug) |
| Version / Stable tag | 1.0.4 |
| Requires at least | 6.9 |
| Tested up to | 7.1 |
| Requires PHP | 8.2 |
| Contributors | `courane01` |
| Author | Courtney Robertson |
| License | GPLv2 or later |

Listing: https://wordpress.org/plugins/link-extension-for-xfn/

**Released tags:** 1.0.0, 1.0.1, 1.0.3, 1.0.4. There is no 1.0.2 tag on SVN despite a 1.0.2
entry in the changelog, and 1.1.0 was a GitHub-only tag that never reached WordPress.org.
Public numbering continues from 1.0.4 — don't cite 1.1.0 or 1.1.1 as released versions.

## Checks worth repeating each release

- [ ] `Tested up to` reflects the newest WordPress actually tested against, not the newest released
- [ ] Stable tag matches the version in `link-extension-for-xfn.php`, its `XFN_LINK_EXTENSION_VERSION`
      constant, and `package.json`
- [ ] `npm run build` output is committed to Git, not merely built locally — every git-based
      install ships whatever `build/` contains
- [ ] `composer phpcs`, `composer phpstan`, and `composer test` all pass
- [ ] `composer audit` reports no advisories
- [ ] Tested against a clean install with `WP_DEBUG` on and no notices in `debug.log`
- [ ] Screenshot captions in `readme.txt` still match the files in `.wordpress-org/`

## Tags

Current tags: `xfn`, `links`, `relationships`, `accessibility`, `gutenberg`.

Five is the maximum WordPress.org allows, and these five are already at that limit:

- `xfn` — primary feature, exact match
- `links` — core functionality
- `relationships` — describes the purpose in plain words
- `accessibility` — a real differentiator; the plugin targets WCAG 2.2 AA
- `gutenberg` — platform integration

Changing one means dropping another. Don't add a sixth; WordPress.org silently ignores the overflow.

## Assets

Assets live in `.wordpress-org/` and are deployed to the SVN `assets/` directory, not to `trunk/`.
All of the required images exist:

- `icon-256x256.png`, `icon-128x128.png`, `icon.svg`
- `banner-772x250.png`, `banner-1544x500.png`
- `screenshot-1.png` through `screenshot-7.png`

Each must stay under 1MB. Specifications and capture instructions are in
`.wordpress-org/DESIGN-PROMPT.md`, `.wordpress-org/ASSETS-CHECKLIST.md`, and
`.wordpress-org/SCREENSHOT-CAPTURE-GUIDE.md`.

Note: the deploy currently rsyncs the whole `.wordpress-org/` directory, so those markdown
files are published to SVN alongside the images. Harmless, but they don't belong there.

## Build verification

```bash
# Install dependencies
npm install
composer install --prefer-dist --no-progress

# Build production output
npm run build

# Verify build directory contents
ls -la build/
# index.js, index.css, index-rtl.css, view.js, *.asset.php,
# blocks/{blogroll,relationship-badge,relationship-directory}/, interactivity/

# Create plugin ZIP
npm run plugin-zip

# Verify the ZIP excludes development files
unzip -l link-extension-for-xfn.zip | grep -E "(node_modules|src/|\.wordpress-org|\.git)"
# Should return nothing — /src is excluded by .distignore

# Verify the ZIP includes what it should
unzip -l link-extension-for-xfn.zip | grep -E "(build/|includes/|readme\.txt|link-extension-for-xfn\.php)"
```

## Translations

There is no `/languages` directory in the repository and no `Domain Path` header. The plugin does
not call `load_plugin_textdomain()`, which is correct — WordPress loads translations for
WordPress.org-hosted plugins automatically from the text domain.

`wp_set_script_translations()` (in `link-extension-for-xfn.php`) passes
`XFN_LINK_EXTENSION_PLUGIN_PATH . 'languages'` as its path argument, and that directory does not
exist. That is not an error: with no local JSON files present, WordPress falls back to the
translations shipped from WordPress.org. Only add a `languages/` directory (and a matching
`Domain Path` header) if you decide to bundle translation files with the plugin.
