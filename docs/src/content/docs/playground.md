---
title: Playground preview
description: "Try Link Extension for XFN on a disposable WordPress site in your browser with WordPress Playground — no installation required."
---

The WordPress Playground Live Preview opens `/xfn-demo/`, a published page with five links that preserve their XFN `rel` values.

## The blueprints

- `assets/blueprints/blueprint.json` installs and activates the directory plugin, enables Inspector Controls, and publishes the demo page.
- `.wordpress-org/blueprints/blueprint.json` is the Live Preview asset used by the WordPress.org publishing workflows.
- The root `blueprint.json` installs the plugin and opens the new post editor.
- `screenshots/docs-blueprint.json` seeds demo content for `npm run screenshots:docs` and the local preview below.

## Run the preview locally

```text
npx @wp-playground/cli@latest server --auto-mount . --blueprint screenshots/docs-blueprint.json --login --port 9400
```

Then open `http://127.0.0.1:9400/xfn-demo/`. This boots your working copy (no Docker) with the same demo content the Live Preview uses. Verified working on WordPress 7.0.1.

## Verify the demo page

The Blueprint removes save filters before inserting the demo page so the four inline links and the button keep their `rel` attributes. Inspect those five links on `/xfn-demo/` and confirm the tooltips on WordPress 7.0 or later. See the [XFN testing guide](https://github.com/courtneyr-dev/link-extension-for-xfn/blob/main/docs/XFN-TESTING-GUIDE.md).

## WordPress.org Live Preview

The listing's Live Preview button uses the Blueprint served from the plugin's SVN assets. Keep the two Live Preview source copies in sync. Include the Blueprint in the next approved WordPress.org release or publish the Blueprint asset with separate approval.
