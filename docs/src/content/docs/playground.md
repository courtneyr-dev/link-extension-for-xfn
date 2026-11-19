---
title: Playground preview
description: "Try Link Extension for XFN on a disposable WordPress site in your browser with WordPress Playground — no installation required."
---

How the WordPress Playground live preview works for this plugin, which blueprint is authoritative, and how to run it locally.

## The blueprints

The repo carries two blueprints with different jobs:

- **`assets/blueprints/blueprint.json`** — the authoritative Live Preview blueprint. It enables Inspector Controls and publishes a demo page at `/xfn-demo/` with XFN-tagged inline links, a button, and an embed. `deploy-blueprint-only.sh` publishes exactly this file to the WordPress.org SVN `assets/blueprints/` path, which is what the listing's Live Preview button loads.
- **`blueprint.json`** (repo root) — an older variant that installs the plugin and lands on a new draft post instead of the demo page. Kept for reference; not deployed. Reconciling the two is flagged in the [documentation plan](https://github.com/courtneyr-dev/link-extension-for-xfn/blob/main/docs/documentation-plan.md).

There is also `screenshots/docs-blueprint.json`, a derivative used only by the documentation capture script (`npm run screenshots:docs`).

## Run the preview locally

```text
npx @wp-playground/cli@latest server --auto-mount . --blueprint screenshots/docs-blueprint.json --login --port 9400
```

Then open `http://127.0.0.1:9400/xfn-demo/`. This boots your working copy (no Docker) with the same demo content the Live Preview uses. Verified working on WordPress 7.0.1.

## Verify the demo page

The published `/xfn-demo/` page renders XFN relationship values on five links. Check the links on the frontend and confirm the tooltips on WordPress 7.0 or later. See the [XFN testing guide](https://github.com/courtneyr-dev/link-extension-for-xfn/blob/main/docs/XFN-TESTING-GUIDE.md).

## WordPress.org Live Preview

The listing's Live Preview button uses the Blueprint served from the plugin's SVN assets. Keep the two Live Preview source copies in sync. Include the Blueprint in the next approved WordPress.org release or publish the Blueprint asset with separate approval.
