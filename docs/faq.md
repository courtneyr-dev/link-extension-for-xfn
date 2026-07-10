# FAQ

Answers to real questions about how Link Extension for XFN behaves.

## What is XFN?

XFN (XHTML Friends Network) is a way to say how you know the person behind a link, using values like `friend`, `colleague`, `spouse`, or `met` in the link's standard HTML `rel` attribute. The plugin supports all 18 values of the XFN 1.1 specification across 7 categories, and enforces the spec's rules — you can pick only one Friendship, one Geographical, and one Family value per link, while the other categories allow multiples.

## Does this restore the classic Link Manager?

No. The plugin doesn't use or restore the classic WordPress Link Manager (`wp_links`). It works entirely inside the block editor and stores relationships in the `rel` attribute of links in your post content.

## Where do I set relationships?

Two places:

- The link popover's **Advanced** panel has an **XFN** section for inline text links. It's always on.
- An optional **XFN Relationships** sidebar panel handles block-level links (Button, Image, Navigation Link, Site Logo, Post Title, Query Title, Embed). Enable it at Settings → Link Extension for XFN.

## Why don't I see the floating toolbar button?

The settings page has a "Floating Toolbar Button" checkbox, but in version 1.1.0 the button may not appear in the editor even when enabled — the feature is under maintainer review. Use the other two interfaces instead.

## Why don't tooltips appear on my links?

Frontend tooltips — the hover/focus popover that shows a link's relationships to visitors — are gated to WordPress 7.0 or later by a feature flag. On WordPress 6.9 and earlier they don't appear. Your `rel` attributes are still saved and visible in the page source.

## Are my relationships private?

No. Relationships are published as standard `rel` attributes in your page HTML, visible to anyone who views the source and to any tool that parses XFN. Think before tagging values like `spouse`, `crush`, or `co-resident`. See [Privacy and data](privacy-and-data.md).

## What happens to my relationships if I deactivate the plugin?

They stay. Relationships live in your post content as `rel` attributes, not in a plugin table, so deactivating or deleting the plugin leaves existing links untouched. You just lose the editor controls, blocks, and tooltips.

## Why is my XFN Blogroll or Relationship Directory empty?

Those blocks build their lists by scanning your published posts and pages for XFN-tagged links, with results cached for about 5 minutes. They need existing tagged links in published content to show anything. See [Troubleshooting](troubleshooting.md).

## Does the plugin work with my theme?

Yes — `rel` attributes are part of standard HTML and don't depend on the theme. No specific theme is required.

## Does the plugin send data anywhere or track anything?

No. It makes no external requests and includes no analytics. See [Privacy and data](privacy-and-data.md).

## Does it integrate with the IndieWeb?

Yes, in a few ways: `rel="me"` identity links, support for blocks that expose an event URL (RSVP cards), and an automatic bridge for the separate Outpost plugin — relationships posted by a Micropub client through Outpost are applied to the matching links in your content. See [Common tasks](common-tasks.md).

## Can I add custom relationship values?

No. The plugin validates against the fixed XFN 1.1 list of 18 values.

---

[Documentation home](index.md) · Previous: [Troubleshooting](troubleshooting.md) · Next: [Privacy and data](privacy-and-data.md)
