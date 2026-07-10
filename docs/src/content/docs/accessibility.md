---
title: Accessibility
description: "The accessibility behavior built into the XFN editor interface, the evidence in the repository, and what still needs testing."
---

Accessibility-relevant behavior in the editor and on the frontend, what the repo shows, and what still needs independent testing. This page describes evidence; it does not certify compliance.

## Editor interfaces

- The collapsible XFN sections (the link popover's Advanced panel and the toggle groups inside it) use `aria-expanded` on their toggles, and the readme documents keyboard operation with Tab, Space, Enter, and arrow keys.
- The Inspector Controls panel is built from standard WordPress components — `RadioControl` for the choose-one categories and `CheckboxControl` for the multi-select ones — which carry the block editor's built-in keyboard and screen reader behavior.
- Active selections are shown as text pills with a numeric count badge, not by color alone.

## Frontend tooltips (WordPress 7.0+ only)

When active, the relationship tooltip is keyboard-operable: it opens on focus as well as hover, closes on blur and on Escape, and the popover uses `role="tooltip"` with its visibility toggled through a hidden attribute binding.

Note: tooltips are gated to WordPress 7.0 or later by a feature flag. On WordPress 6.9 and earlier this behavior doesn't exist on the page, so it can't affect accessibility either way.

## Relationship Directory block

The directory's interactive controls use native semantics: the search field is an `<input type="search">`, the relationship filters are real `<button>` elements, and the results list is a `<ul>` with `role="list"`. Filtering hides non-matching items via the `hidden` attribute rather than visual-only tricks.

## What testing exists in the repo

- The readme and changelog claim WCAG 2.2 AA compliance and testing with NVDA, JAWS, and VoiceOver. These claims are self-asserted by the project; the repo contains no third-party audit artifact, so this documentation describes them without certifying them.
- The PHPUnit suite covers plugin logic (feature flags, meta mirror, blocks, interactivity) but there is no automated accessibility test suite.

## What still needs testing

- Independent screen reader testing of the link popover's injected XFN section, which is added to core's link control via DOM manipulation.
- Keyboard and screen reader testing of the frontend tooltip on WordPress 7.0+.
- Color contrast review of the relationship pills in both the editor and frontend styles.

If you hit an accessibility problem, report it on [GitHub Issues](https://github.com/courtneyr-dev/link-extension-for-xfn/issues).
