---
title: Privacy and data
description: "What Link Extension for XFN stores, what it publishes in your public markup, and what it never sends anywhere — verified against the source."
---

What the plugin stores, what it publishes, and what it doesn't do. Findings below are based on a source review of version 1.0.4.

## Think before you tag

XFN values describe real human relationships — friend, spouse, crush, co-resident, met, and so on. The plugin publishes them as standard `rel` attributes in your page HTML, which means they're visible to anyone who views your page source, to search engines, and to any tool that parses XFN. Only tag relationships you're comfortable stating publicly, about people who are comfortable having them stated.

## What the plugin stores

**In post content:** relationships are written into the `rel` attribute of links (or `data-xfn-rel` on Embed blocks without an internal anchor). There's no custom database table. Because the data lives in your content, it persists after the plugin is deactivated or deleted.

**Post meta:**

- `_xfn_relationships` — a mirror of each post's XFN links (URL plus relationship list), kept in sync with the content. It's registered with a REST schema, so it's readable through the WordPress REST API by logged-in users who can edit posts.
- `_xfn_meta_source` — bookkeeping for the mirror.
- `_outpost_xfn` — written by the separate Outpost plugin's Micropub bridge; this plugin reads it and applies the relationships to the matching link.

**Options:** `xfn_link_extension_options` (the two settings-page checkboxes) and `xfn_feature_flags` (internal feature toggles).

**Transients:** `xfn_rels_all` caches the site-wide scan of published posts and pages used by the Blogroll, Relationship Badge, and Relationship Directory blocks, for about 5 minutes.

**User meta:** none.

## External data transmission

None found. The plugin makes no outbound HTTP requests (no `wp_remote_*`, cURL, or similar in the source) and includes no analytics or tracking. The plugin stores link URLs but never fetches them.

## Frontend markup it adds

- `rel` values on tagged anchors, merged with any existing values like `nofollow` or `noopener`.
- `data-xfn-rel` on Embed block figures that have no internal anchor.
- On WordPress 7.0+, tooltip markup around XFN links: a wrapper element and a `role="tooltip"` popover listing the relationships. On WordPress 6.9 and earlier this markup isn't added.
- The three optional blocks render lists of your tagged URLs, the posts they came from, and their relationship pills.

## What it changes in WordPress data

- Post content: only the `rel`/`data-xfn-rel` attributes of links you tag (or that the Outpost bridge tags).
- Post meta and options as listed above.
- It does not touch comments, users, taxonomies, the classic Link Manager (`wp_links`), or other plugins' data.

## Verified and unverified

Verified from source: the storage locations above, the absence of external calls, and the REST visibility of `_xfn_relationships`. For anything beyond this — for example formal privacy review of the meta mirror's REST exposure — see the "Needs maintainer review" list in the [documentation plan](https://github.com/courtneyr-dev/link-extension-for-xfn/blob/main/docs/documentation-plan.md).
