# Screenshot Automation

This directory contains automated screenshot generation for the Link Extension for XFN plugin using WordPress Playground and our working blueprint.

## Overview

The `capture.js` script uses Playwright to automate screenshot capture by:

1. Loading WordPress Playground with our local blueprint via CORS server
2. Using the pre-configured demo page at `/xfn-demo/`
3. Navigating through the editor to show XFN features
4. Capturing high-quality screenshots
5. Saving them to both `.wordpress-org/` and `docs/images/` directories

**Key Features:**
- Uses our working blueprint with demo content already included
- No manual content creation needed
- Captures multiple screenshots showing different aspects of the plugin
- Saves to both WordPress.org and documentation directories

## Prerequisites

Make sure you have Node.js and npm installed, then install dependencies:

```bash
npm install
```

This will install Playwright and its browser binaries (Chromium).

## Usage

### Step 1: Start the CORS Server

First, start the CORS-enabled HTTP server in a separate terminal:

```bash
cd /Users/crobertson/Downloads/xfn/link-extension-for-xfn
python3 cors-server.py
```

This serves the blueprint at `http://localhost:8000` with proper CORS headers.

### Step 2: Generate Screenshots

In another terminal, run the automated screenshot capture:

```bash
npm run screenshots
```

This will:
- Launch Chromium in visible mode (you can watch it work)
- Load WordPress Playground with our blueprint from localhost:8000
- Automatically land at the demo page (`/xfn-demo/`)
- Navigate through the editor showing XFN features
- Capture multiple screenshots automatically
- Save to both `.wordpress-org/` and `docs/images/`

The process takes about 60-120 seconds.

### Screenshots Generated

1. **screenshot-1.png**: Demo page showing XFN examples on the frontend
2. **screenshot-2.png**: Inspector Controls with XFN Relationships panel
3. **screenshot-3.png**: XFN interface showing relationship selections

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

### "Failed to fetch" or CORS errors

Make sure the CORS server is running at http://localhost:8000. Start it with:
```bash
python3 cors-server.py
```

### Playground doesn't load

- Check that `assets/blueprints/blueprint.json` exists
- Increase timeout values in `capture.js` CONFIG section
- Try running with slower internet connection settings

### Screenshots look wrong

- Increase wait times (`setTimeout` values) if elements aren't loaded yet
- Adjust `viewport` dimensions in CONFIG for different screen sizes
- Check browser console for JavaScript errors during capture

### Playwright installation issues

If Playwright browsers don't install automatically:

```bash
npx playwright install chromium
```

## Updating Screenshots

When you make UI changes to the plugin:

1. Make your code changes
2. Run `npm run build` to compile
3. Update the blueprint if demo content needs changes
4. Start CORS server: `python3 cors-server.py`
5. Run `npm run screenshots` to regenerate
6. Review the new screenshots in both `.wordpress-org/` and `docs/images/`
7. Commit the updated screenshots

## Files Generated

Screenshots are saved to **both** directories:

**WordPress.org assets** (for plugin directory):
- `.wordpress-org/screenshot-1.png` - Demo page frontend view
- `.wordpress-org/screenshot-2.png` - Inspector Controls with XFN panel
- `.wordpress-org/screenshot-3.png` - XFN relationship selections

**Documentation images** (for GitHub docs):
- `docs/images/screenshot-1.png` - Same as above
- `docs/images/screenshot-2.png` - Same as above
- `docs/images/screenshot-3.png` - Same as above

## Notes

- Screenshots are automatically excluded from the plugin ZIP via `.distignore`
- The `.wordpress-org/` directory is tracked in git but excluded from distribution
- Uses local blueprint via CORS server for testing
- Once deployed to WordPress.org, the blueprint will load from the plugin repository
- Blueprint includes demo content, so no manual setup needed

## Further Customization

To capture additional screenshots:

1. Add new functions in `capture.js` for different scenarios
2. Call `captureScreenshot(page, 'screenshot-2.png')`
3. Update WordPress.org plugin page to reference new screenshots

## Learn More

- [Playwright Documentation](https://playwright.dev/)
- [WordPress Playground](https://developer.wordpress.org/playground/)
- [Blueprint API](https://wordpress.github.io/wordpress-playground/blueprints-api/index)
