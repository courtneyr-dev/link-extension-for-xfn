# Link Extension for XFN documentation

Link Extension for XFN adds XFN (XHTML Friends Network) relationship options to the WordPress block editor's link tools, so you can say how you know the person behind a link. This page is the navigation hub for all user documentation.

## What the plugin does

XFN lets you describe how you know the person or site you're linking to — friend, colleague, spouse, met, and so on. The plugin stores those relationships in the link's standard HTML `rel` attribute, which is visible to anyone reading the page source and to tools that parse XFN. There are 18 values in 7 categories, and the plugin enforces the XFN 1.1 rules for which values can be combined.

It's built for bloggers, IndieWeb users, and site owners who want semantic, human-readable relationships on their links — without a custom database table or a separate link manager.

As of version 1.1.0 (plugin header), the plugin requires WordPress 6.9 or higher and PHP 8.2 or higher.

## Start here

1. [Installation](installation.md) — install and activate the plugin.
2. [Getting started](getting-started.md) — add your first XFN relationship to a link.
3. [Settings](settings.md) — decide which editor interfaces to enable.

## All pages

- [Installation](installation.md)
- [Getting started](getting-started.md)
- [Settings](settings.md)
- [Common tasks](common-tasks.md)
- [Troubleshooting](troubleshooting.md)
- [FAQ](faq.md)
- [Privacy and data](privacy-and-data.md)
- [Accessibility](accessibility.md)
- [Screenshots](screenshots.md)
- [Playground preview](playground.md)
- [Documentation plan](documentation-plan.md)

## Detailed guides

These guides pre-date the standard pages above and go deeper on specific link types.

*Note: the floating toolbar button some older material referenced was removed in 1.1.1 — it was advertised but never functional. The two working interfaces are the link popover's Advanced panel and the optional Inspector Controls panel; see [Settings](settings.md).*

- [User guide](USER-GUIDE.md) — the full end-user walkthrough.
- [Paragraph links](paragraph-links.md) — XFN on inline text links.
- [Button links](button-links.md) — XFN on Button blocks.
- [Image links](image-links.md) — XFN on clickable images.
- [Other block links](other-block-links.md) — Navigation, Site Logo, Post Title, and Query Title links.
- [XFN testing guide](XFN-TESTING-GUIDE.md) — verifying `rel` output on the frontend.
- [Why this plugin exists](blog-post.md) — background essay on XFN and the IndieWeb.

## Compatibility notes

- Works in the block editor only. It does not use or restore the classic Link Manager (`wp_links`).
- No specific theme is required — `rel` attributes work with any theme.
- Frontend tooltips (a hover/focus popover showing a link's relationships) are gated to WordPress 7.0 or later by a feature flag. On WordPress 6.9 and earlier they do not appear. See [FAQ](faq.md).
- The optional Outpost plugin's Micropub bridge can set relationships automatically; see [Common tasks](common-tasks.md).

## For developers and maintainers

Developer- and maintainer-facing documents in this directory:

- [QA testing guide](QA-TESTING-GUIDE.md)
- [Query Monitor testing guide](QUERY-MONITOR-TESTING-GUIDE.md)
- [Manual local setup](MANUAL-LOCAL-SETUP.md)
- [Screenshot automation](SCREENSHOT-AUTOMATION.md)
- [SVN deployment guide](SVN-DEPLOYMENT-GUIDE.md)
- [1.0.3 deployment notes](DEPLOY-1.0.3.md)
- [Pre-submission checklist](PRE-SUBMISSION-CHECKLIST.md)
- [Plugin Check fixes](PLUGIN-CHECKER-FIXES.md)
- [Coding standards report](CODING-STANDARDS-REPORT.md)
- [Security audit report](SECURITY-AUDIT-REPORT.md)
- [Translation readiness report](TRANSLATION-READINESS-REPORT.md)
- [Naming update summary](NAMING-UPDATE-SUMMARY.md)
- [Markdown files review](MARKDOWN-FILES-REVIEW.md)
- [readme.txt notes](README-NOTES.md)

Contribution and support policies live in the repo root: [CONTRIBUTING.md](../CONTRIBUTING.md), [SUPPORT.md](../SUPPORT.md), [SECURITY.md](../SECURITY.md), [CHANGELOG.md](../CHANGELOG.md).

---

[Documentation home](index.md) · Next: [Installation](installation.md)
