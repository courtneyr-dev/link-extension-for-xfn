# Screenshot Automation

This directory contains automated screenshot generation for the Link Extension for XFN plugin.

> **⚠️ Note:** Screenshot automation requires the plugin to be published on WordPress.org. The `blueprint.json` file references the plugin from the WordPress.org directory. Once the plugin is approved, run `npm run screenshots` to generate screenshots automatically.

## Overview

The `capture.js` script uses Playwright to automate screenshot capture by:

1. Loading WordPress Playground with your plugin pre-installed from WordPress.org
2. Creating test content and links
3. Opening the XFN interface
4. Capturing high-quality screenshots
5. Saving them to `.wordpress-org/` directory

## Prerequisites

Make sure you have Node.js and npm installed, then install dependencies:

```bash
npm install
```

This will install Playwright and its browser binaries (Chromium).

## Usage

### Generate Screenshots

Run the automated screenshot capture:

```bash
npm run screenshots
```

This will:
- Launch Chromium in visible mode (you can watch it work)
- Load WordPress Playground with the plugin
- Navigate through the XFN interface
- Capture screenshots automatically
- Save to `.wordpress-org/screenshot-1.png`

The process takes about 60-90 seconds.

### Headless Mode

To run without opening a browser window, edit `capture.js` and change:

```javascript
headless: false, // Change to true
```

## Configuration

Edit `capture.js` to customize:

- **viewport**: Change screenshot dimensions (default: 1280x720)
- **timeout**: Adjust wait times for slower connections
- **outputDir**: Change where screenshots are saved
- **slowMo**: Speed up or slow down automation

## Troubleshooting

### "Plugin not found" error

The script installs from WordPress.org. **This is expected if your plugin isn't approved yet.**

Wait for WordPress.org approval, then the screenshot automation will work automatically. No code changes needed - the blueprint will work once the plugin is live on WordPress.org.

### Screenshots look wrong

- Increase `waitForTimeout` values if elements aren't loaded
- Adjust `viewport` dimensions for different screen sizes
- Check that selectors match your plugin's HTML structure

### Playwright installation issues

If Playwright browsers don't install automatically:

```bash
npx playwright install chromium
```

## Updating Screenshots

When you make UI changes to the plugin:

1. Make your code changes
2. Run `npm run build` to compile
3. Run `npm run screenshots` to regenerate
4. Review the new screenshots in `.wordpress-org/`
5. Commit the updated screenshots

## Files Generated

- `.wordpress-org/screenshot-1.png` - Main plugin interface screenshot
- Additional screenshots can be added by modifying `capture.js`

## Notes

- Screenshots are automatically excluded from the plugin ZIP via `.distignore`
- The `.wordpress-org/` directory is tracked in git but excluded from distribution
- WordPress Playground loads from WordPress.org, so published changes appear immediately
- **Screenshot automation will work automatically once plugin is approved on WordPress.org**

## Further Customization

To capture additional screenshots:

1. Add new functions in `capture.js` for different scenarios
2. Call `captureScreenshot(page, 'screenshot-2.png')`
3. Update WordPress.org plugin page to reference new screenshots

## Learn More

- [Playwright Documentation](https://playwright.dev/)
- [WordPress Playground](https://developer.wordpress.org/playground/)
- [Blueprint API](https://wordpress.github.io/wordpress-playground/blueprints-api/index)
