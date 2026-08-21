# WordPress.org release checklist — Link Extension for XFN

**Plugin**: Link Extension for XFN
**Slug**: `link-extension-for-xfn`
**Current version**: 1.0.4
**Author**: Courtney Robertson (courane01)

The initial WordPress.org submission is complete — the plugin was approved and has shipped
1.0.0, 1.0.1, 1.0.3, and 1.0.4. This document started life as the pre-submission checklist for
that first review; it now covers what has to be verified **before each release**.

Listing: https://wordpress.org/plugins/link-extension-for-xfn/
Deployment procedure: [SVN-DEPLOYMENT-GUIDE.md](SVN-DEPLOYMENT-GUIDE.md)
Listing facts and tags: [README-NOTES.md](README-NOTES.md)

> Deployment is manual. CI never publishes. A merged pull request or a version bump does not
> put anything on WordPress.org until someone runs `./deploy-to-wordpress-org.sh` by hand.

---

## PHASE 1: Version consistency

The single most common release defect. Four places must agree:

- [ ] `link-extension-for-xfn.php` header `Version:`
- [ ] `link-extension-for-xfn.php` `XFN_LINK_EXTENSION_VERSION` constant
- [ ] `readme.txt` `Stable tag:`
- [ ] `package.json` `version`

These have drifted before — `package.json` sat at `1.1.0` after the `1.1.1 → 1.0.4` renumber
until it was corrected. Check all four every time.

Public numbering runs 1.0.0 → 1.0.1 → 1.0.3 → 1.0.4. There is no 1.0.2 SVN tag, and 1.1.0 was a
GitHub-only tag that never reached WordPress.org. Never cite 1.1.0 or 1.1.1 as released versions
in user-facing documentation.

### Header fields

- [ ] **Plugin Name**: Link Extension for XFN
- [ ] **Text Domain**: `link-extension-for-xfn` (matches the slug)
- [ ] **Requires at least**: 6.9
- [ ] **Tested up to**: reflects the newest WordPress you have *actually tested against*
- [ ] **Requires PHP**: 8.2
- [ ] **License**: GPLv2 or later

**Verify**: `link-extension-for-xfn.php` lines 1–17.

---

## PHASE 2: readme.txt

- [ ] Header block: Contributors, Tags, Tested up to, Stable tag, Requires at least, Requires PHP, License
- [ ] **Contributors**: `courane01` (a real WordPress.org username)
- [ ] **Tags**: `xfn, links, relationships, accessibility, gutenberg` — five is the maximum; adding a
      sixth means WordPress.org silently ignores the overflow
- [ ] Short description under 150 characters
- [ ] Changelog has an entry for the version being shipped
- [ ] Screenshot captions match the files in `.wordpress-org/`
- [ ] Historical changelog entries left untouched — they record what each release was tested
      against and must not be rewritten

**Validate**: https://wordpress.org/plugins/developers/readme-validator/

---

## PHASE 3: Code quality gates

These are the real CI gates from `.github/workflows/ci.yml`. None use `continue-on-error`.

```bash
composer phpcs                              # WordPress standards + PHPCompatibilityWP (testVersion 8.2-)
composer phpstan -- --memory-limit=1G       # level 5, scoped to includes/ and the main file
composer test                               # full PHPUnit suite (unit + integration)
composer audit                              # dependency advisories
npm run lint:js
npm run lint:css
```

- [ ] PHPCS reports zero **errors** (warnings are acceptable)
- [ ] PHPStan reports no errors
- [ ] Full PHPUnit suite green
- [ ] `composer audit` reports no advisories
- [ ] JS and CSS lint clean

CI runs the test suite across PHP 8.2 / 8.3 / 8.4 against WordPress `latest` and `trunk`
(PHP 8.4 + `latest` is excluded from the matrix). Because the matrix tracks `latest`, a new
WordPress release is picked up automatically — but that does not update the `Tested up to`
header, which is a human claim and has to be earned by actually testing.

The lint ruleset (`phpcs.xml.dist`) and the PHPStan configuration (`phpstan.neon`) are settled
canon, decided in PRs #2/#3 to match the sibling plugins. Do not modernize them as part of a
release.

### Security

- [ ] All files check `ABSPATH` before doing anything
- [ ] Input sanitized, output escaped, nonces verified, capability checks present

See `SECURITY-AUDIT-REPORT.md` and `CODING-STANDARDS-REPORT.md` for the standing audits.

---

## PHASE 4: Assets

All required assets exist in `.wordpress-org/`:

- [x] `icon-256x256.png`, `icon-128x128.png`, `icon.svg`
- [x] `banner-772x250.png`, `banner-1544x500.png`
- [x] `screenshot-1.png` through `screenshot-7.png`

- [ ] Any new or changed screenshots are under 1MB and show real functionality, not mockups
- [ ] Screenshot count matches the captions in `readme.txt`

Assets deploy to the SVN `assets/` directory, **not** to `trunk/`. They are excluded from the
plugin ZIP by `.distignore`.

---

## PHASE 5: Testing

- [ ] Tested on a clean WordPress install at the version named in `Tested up to`
- [ ] Tested with the default theme (Twenty Twenty-Five as of WordPress 7.1)
- [ ] `WP_DEBUG` and `WP_DEBUG_LOG` on, and `debug.log` is clean — no notices, warnings, or deprecations
- [ ] No JavaScript console errors in the editor
- [ ] Feature pass:
  - [ ] Inspector Controls panel
  - [ ] Link Advanced panel integration
  - [ ] XFN relationship selection across supported blocks
  - [ ] Save and publish
  - [ ] `rel` attributes present in the rendered front-end HTML
  - [ ] Front-end tooltips (WordPress 7.0+ only — gated behind the `interactivity` feature flag)
  - [ ] The three blocks: XFN Blogroll, Relationship Badge, Relationship Directory
  - [ ] Abilities registration — all nine `xfn/*` abilities present in `wp_get_abilities()`
- [ ] Deactivate and delete cleanly

The "Floating Toolbar XFN button" was removed in 1.0.4 — the button was advertised in
1.0.0–1.0.3 but never implemented. Do not test for it.

### Accessibility

- [ ] Keyboard navigation (Tab, Enter, Space, Escape)
- [ ] Screen reader pass (VoiceOver, NVDA, or JAWS)
- [ ] Visible focus indicators
- [ ] WCAG 2.2 AA

### Browsers

- [ ] Chrome · [ ] Firefox · [ ] Safari · [ ] Edge

---

## PHASE 6: Build and package

```bash
npm install
composer install --prefer-dist --no-progress
npm run build
npm run plugin-zip
```

- [ ] `npm run build` completes with no errors
- [ ] **Built output is committed to Git.** Every git-based install ships whatever `build/`
      contains. This has bitten before: `build/interactivity/tooltip.js` was never built or
      committed, and `@wordpress/interactivity` was missing from `package-lock.json` entirely,
      so the enqueued module 404'd and front-end tooltips were dead on every install.
- [ ] `build/` contains `index.js`, `index.css`, `index-rtl.css`, `view.js`, the `*.asset.php`
      files, `blocks/`, and `interactivity/`

### Verify the ZIP

```bash
# Should return nothing — /src is excluded by .distignore
unzip -l link-extension-for-xfn.zip | grep -E "(node_modules|src/|\.wordpress-org|\.git)"

# Should list these
unzip -l link-extension-for-xfn.zip | grep -E "(build/|includes/|readme\.txt|link-extension-for-xfn\.php)"
```

- [ ] No `/src`, `/node_modules`, `.wordpress-org/`, or Git files
- [ ] `build/`, `includes/`, `readme.txt`, and the main plugin file present
- [ ] ZIP under 5MB

---

## PHASE 7: Guidelines

- [ ] GPL v2 or later
- [ ] No trademark misuse (no "WordPress" in the plugin name)
- [ ] No obfuscated code
- [ ] No external service calls — the plugin makes no outbound requests
- [ ] No tracking
- [ ] Extends core rather than competing with it
- [ ] Namespaced: `XFN_` classes, `xfn_` functions, `xfn/` block and ability names
- [ ] Version in readme matches the plugin header

https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/

---

## PHASE 8: Deploy and verify

Follow [SVN-DEPLOYMENT-GUIDE.md](SVN-DEPLOYMENT-GUIDE.md). Deployment needs a **generated
WordPress.org SVN password** — an account password will not authenticate.

After deploying:

- [ ] Plugin page shows the new version: https://wordpress.org/plugins/link-extension-for-xfn/
- [ ] `Tested up to` shows the value you set
- [ ] Icon, banner, and screenshots display
- [ ] Download works and the ZIP installs cleanly
- [ ] Verify against SVN directly rather than the API — the plugin information API is cached and
      lags behind a fresh deploy:

```bash
svn ls https://plugins.svn.wordpress.org/link-extension-for-xfn/tags/
svn cat https://plugins.svn.wordpress.org/link-extension-for-xfn/trunk/readme.txt | head -12
```

---

## Reference

- Plugin Handbook: https://developer.wordpress.org/plugins/
- Using Subversion: https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/
- Plugin assets: https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/
- readme.txt validator: https://wordpress.org/plugins/developers/readme-validator/

---

**Last updated**: 2026-08-20 (WordPress 7.1 compatibility pass)
**Plugin version at last update**: 1.0.4
