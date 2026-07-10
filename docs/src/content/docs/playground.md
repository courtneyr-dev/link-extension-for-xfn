---
title: Playground preview
description: "Try Link Extension for XFN on a disposable WordPress site in your browser with WordPress Playground — no installation required."
---

How the WordPress Playground live preview works for this plugin, which blueprint is authoritative, and how to run it locally.

## The blueprints

The repo carries two blueprints with different jobs:

- **`assets/blueprints/blueprint.json`** — the authoritative Live Preview blueprint. It enables Inspector Controls and publishes a demo page at `/xfn-demo/` with XFN-tagged inline links, a button, and an embed. `deploy-blueprint-only.sh` publishes exactly this file to the WordPress.org SVN `assets/blueprints/` path, which is what the listing's Live Preview button loads. (`blueprint-improved.json` in the same directory is an identical working copy.)
- **`blueprint.json`** (repo root) — an older variant that installs the plugin and lands on a new draft post instead of the demo page. Kept for reference; not deployed. Reconciling the two is flagged in the [documentation plan](https://github.com/courtneyr-dev/link-extension-for-xfn/blob/main/docs/documentation-plan.md).

There is also `screenshots/docs-blueprint.json`, a derivative used only by the documentation capture script (`npm run screenshots:docs`).

## Run the preview locally

```text
npx @wp-playground/cli@latest server --auto-mount . --blueprint screenshots/docs-blueprint.json --login --port 9400
```

Then open `http://127.0.0.1:9400/xfn-demo/`. This boots your working copy (no Docker) with the same demo content the Live Preview uses. Verified working on WordPress 7.0.1.

## Known issue with the demo page

When the demo steps run on WordPress 7.0.1 locally, the published `/xfn-demo/` page renders its links **without** `rel` attributes — so the demo doesn't actually demonstrate the plugin's output, and frontend tooltips (a WordPress 7.0+ feature) can't appear there either. Whether the blueprint content or a save-time filter strips them needs maintainer investigation; it's item 12 in the [documentation plan](https://github.com/courtneyr-dev/link-extension-for-xfn/blob/main/docs/documentation-plan.md). Until then, the most reliable way to verify output is the [testing guide](https://github.com/courtneyr-dev/link-extension-for-xfn/blob/main/docs/XFN-TESTING-GUIDE.md).

## WordPress.org Live Preview (maintainer actions)

The live listing already shows a Live Preview button, currently serving the blueprint deployed with 1.0.3. To refresh it:

1. Fix the demo-page `rel` issue above (otherwise the preview shows plain links).
2. Run `deploy-blueprint-only.sh` to publish `assets/blueprints/blueprint.json` to SVN without a full release — or include it in the next full deploy. Note the deploy scripts still hardcode old versions/slugs (flagged in the documentation plan); review them before running.
