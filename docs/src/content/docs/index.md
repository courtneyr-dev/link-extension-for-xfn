---
title: Link Extension for XFN
description: "User documentation for Link Extension for XFN: add XFN relationship metadata to links in the WordPress block editor."
---

Link Extension for XFN adds [XFN (XHTML Friends Network)](https://gmpg.org/xfn/) relationship options to the WordPress block editor's link tools, so you can say how you know the person behind a link. These docs help you install the plugin, tag your first link, and display relationships on your site.

## What the plugin does

XFN lets you describe how you know the person or site you're linking to — friend, colleague, spouse, met, and so on. The plugin stores those relationships in the link's standard HTML `rel` attribute, which is visible to anyone reading the page source and to tools that parse XFN. There are 18 values in 7 categories, and the plugin enforces the [XFN 1.1](https://gmpg.org/xfn/11) rules for which values can be combined.

It also ships three server-rendered blocks — **XFN Blogroll**, **Relationship Badge**, and **Relationship Directory** — that turn the relationships in your published content into browsable listings.

## Who it's for

Bloggers, IndieWeb users, and site owners who want semantic, human-readable relationships on their links — without a custom database table or a separate link manager.

## Before you install

As of version 1.0.4, the plugin requires:

- WordPress 6.9 or higher (frontend tooltips need WordPress 7.0 or later).
- PHP 8.2 or higher.
- The block editor — the plugin doesn't use or restore the classic Link Manager.

## Get the plugin

Link Extension for XFN is published in the [WordPress.org plugin directory](https://wordpress.org/plugins/link-extension-for-xfn/) — install it from **Plugins → Add New Plugin** in wp-admin. [Installation](/link-extension-for-xfn/installation/) also covers GitHub installs, and [Playground preview](/link-extension-for-xfn/playground/) lets you try it in your browser first.

## Get started

1. [Installation](/link-extension-for-xfn/installation/) — install and activate the plugin.
2. [Getting started](/link-extension-for-xfn/getting-started/) — add your first XFN relationship to a link.
3. [Settings](/link-extension-for-xfn/settings/) — decide which editor interfaces to enable.

## Compatibility notes

- No specific theme is required — `rel` attributes work with any theme.
- Frontend tooltips (a hover/focus popover showing a link's relationships) are gated to WordPress 7.0 or later by a feature flag; on 6.9 they don't appear. See [FAQ](/link-extension-for-xfn/faq/).
- The optional [Outpost](https://courtneyr-dev.github.io/outpost/) plugin's [Micropub](https://indieweb.org/Micropub) bridge can set relationships automatically; see [Common tasks](/link-extension-for-xfn/common-tasks/).

## Get help

- [Troubleshooting](/link-extension-for-xfn/troubleshooting/) — symptoms, causes, and fixes.
- [FAQ](/link-extension-for-xfn/faq/) — quick answers, including what happens when you remove the plugin.
- [Report an issue](https://github.com/courtneyr-dev/link-extension-for-xfn/issues) on GitHub.

## Source code

The plugin is developed in the open at [github.com/courtneyr-dev/link-extension-for-xfn](https://github.com/courtneyr-dev/link-extension-for-xfn). Developer and maintainer documents (QA guides, testing notes, historical reports) live in the repository, separate from these user docs.
