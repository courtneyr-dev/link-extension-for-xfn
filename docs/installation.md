# Installation

How to install and activate Link Extension for XFN, and how to confirm it's working.

## Requirements

- WordPress 6.4 or higher
- PHP 8.2 or higher (per the plugin header, as of version 1.1.0)

If your site doesn't meet the WordPress minimum, activation stops with an error message ("Link Extension for XFN requires WordPress 6.4 or higher") and the plugin deactivates itself. There are no required companion plugins. The optional Outpost plugin adds a Micropub integration but isn't needed for anything else.

## Install from a ZIP file

1. Download the plugin ZIP — from a [GitHub release](https://github.com/courtneyr-dev/link-extension-for-xfn/releases), or from the repository page via Code → Download ZIP.
2. In wp-admin, go to Plugins → Add New Plugin → Upload Plugin.
3. Choose the ZIP file and click Install Now.
4. Click Activate.

No build step is needed: the compiled editor and block assets in `build/` are committed to the repository, so a downloaded ZIP already contains everything the plugin needs.

The plugin's readme and README also link to a WordPress.org plugin page (`wordpress.org/plugins/link-extension-for-xfn`), including a Playground live preview. If the directory listing is available for your site, you can also search for "Link Extension for XFN" under Plugins → Add New Plugin.

## Install from GitHub (clone)

1. Clone the repository into your plugins directory:

   ```
   cd wp-content/plugins
   git clone https://github.com/courtneyr-dev/link-extension-for-xfn.git
   ```

2. In wp-admin, go to Plugins and activate "Link Extension for XFN".

Because `build/` is committed, a fresh clone activates without running `npm install` or `npm run build`. You only need the build tooling if you're changing files in `src/`.

## Confirm it's working

1. Edit any post or page in the block editor.
2. Select some text in a Paragraph block and add a link (Cmd/Ctrl+K).
3. Click the link, then expand the Advanced section of the link popover.
4. You should see a collapsible "XFN" section with relationship options grouped by category.

You can also confirm the settings page exists: go to Settings → Link Extension for XFN. If the page is there, the plugin is active.

(Screenshot planned: see [screenshot inventory](screenshots.md).)

## Next step

Head to [Getting started](getting-started.md) to add your first relationship.

---

[Documentation home](index.md) · Previous: [Home](index.md) · Next: [Getting started](getting-started.md)
