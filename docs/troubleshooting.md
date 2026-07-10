# Troubleshooting

Symptoms, likely causes, and fixes for common problems. When nothing here helps, see "When to open an issue" at the bottom.

## The XFN section doesn't appear in the link popover

**Likely causes:** the Advanced section isn't expanded; the plugin isn't active; a JavaScript error on the page.

**Fix:**

1. Click the link, then expand **Advanced** in the popover — the XFN section sits inside it, collapsed by default.
2. Confirm the plugin is active at Plugins.
3. Open the browser console (F12) and look for JavaScript errors; test with a default theme and other plugins disabled to rule out conflicts.

**Check next:** Settings → Link Extension for XFN loads? Then the plugin is running and the issue is editor-side.

## The Inspector Controls panel is missing

**Likely causes:** the setting is off (it's off by default); the selected block isn't a supported block-level link; the editor wasn't refreshed.

**Fix:**

1. Enable **Inspector Controls** at Settings → Link Extension for XFN and save.
2. Refresh the editor.
3. Select a supported block with a URL set — Button, Image, Navigation Link, Site Logo, Post Title, Query Title, or Embed. The panel never appears for inline paragraph links.
4. Scroll down in the sidebar; the panel may be below other panels.

## I remember a "Floating Toolbar Button" setting — where did it go?

**Cause:** removed in 1.1.1. The setting existed in 1.0.x–1.1.0 but the toolbar button it promised was never implemented, so the checkbox did nothing.

**Fix:** nothing to do — the two working interfaces cover the same relationships: the link popover's Advanced panel (inline links, always on) and the Inspector Controls panel (block-level links, enable in Settings). See [Settings](settings.md).

## Tooltips don't show on the frontend

**Likely cause:** frontend tooltips are gated to WordPress 7.0 or later by a feature flag. On WordPress 6.9 and earlier they do not appear at all.

**Fix:** none needed — your relationships are still saved and present in the `rel` attribute (verify with browser developer tools). Tooltips will activate on WordPress 7.0+. Note: version 1.1.0 also fixed a packaging bug where the tooltip script was missing entirely in 1.0.3, so update if you're on an older version.

## The Blogroll, Badge, or Directory block says "No XFN relationships found"

**Likely causes:** no published posts or pages contain XFN-tagged links yet; the scan cache hasn't refreshed; a Badge URL doesn't exactly match the tagged link.

**Fix:**

1. Publish at least one post or page containing a link with XFN values — the blocks scan published posts and pages only, not drafts.
2. Wait up to 5 minutes — scan results are cached and refresh automatically when the cache expires.
3. For the Relationship Badge, make sure the URL you entered matches the tagged link's `href` exactly, including scheme and trailing slash.
4. Inspect a tagged link on the frontend to confirm the `rel` attribute actually saved.

On very large sites, note the scan covers the 500 most recent published posts and pages that contain a `rel` attribute.

## Relationships disappear after saving

**Likely cause:** versions before 1.1.0 could drop relationships when saving while offline or when linking to hosts that don't resolve.

**Fix:** update to 1.1.0 or later, where this was fixed, then re-apply the lost relationships.

## Settings don't seem to take effect

**Fix:** click Save Changes, then refresh any open editor windows. Settings apply site-wide; every editor needs a refresh. See [Settings](settings.md#troubleshooting-settings).

## When to open an issue

If you've worked through the steps above:

1. Check the FAQ in the plugin readme and search existing reports.
2. Test with a default theme (such as Twenty Twenty-Four) and no other plugins active.
3. Report bugs on [GitHub Issues](https://github.com/courtneyr-dev/link-extension-for-xfn/issues), or ask usage questions on the [WordPress.org support forum](https://wordpress.org/support/plugin/link-extension-for-xfn/). Include your WordPress and PHP versions and any browser console errors — see [SUPPORT.md](../SUPPORT.md) for the full checklist.

---

[Documentation home](index.md) · Previous: [Common tasks](common-tasks.md) · Next: [FAQ](faq.md)
