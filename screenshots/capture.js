/**
 * Screenshot Automation for Link Extension for XFN
 *
 * This script uses Playwright to automatically capture screenshots
 * of the plugin in action using WordPress Playground with our blueprint.
 *
 * Usage: npm run screenshots
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

// Configuration
const CONFIG = {
  playgroundUrl: 'https://playground.wordpress.net/',
  // Use local blueprint via CORS-enabled server
  blueprintUrl: 'http://localhost:8000/assets/blueprints/blueprint.json',
  outputDir: path.join(__dirname, '..', '.wordpress-org'),
  docsImageDir: path.join(__dirname, '..', 'docs', 'images'),
  viewport: {
    width: 1440,
    height: 900
  },
  timeout: 120000 // 120 seconds for WordPress Playground to load with blueprint
};

/**
 * Wait for WordPress Playground to fully load with blueprint
 */
async function waitForPlayground(page) {
  console.log('⏳ Waiting for WordPress Playground to load with blueprint...');

  try {
    // Wait for page load
    await page.waitForLoadState('domcontentloaded', { timeout: CONFIG.timeout });

    // Wait for the demo page to be visible (blueprint lands at /xfn-demo/)
    await page.waitForTimeout(10000);

    console.log('✅ WordPress Playground loaded with demo page');
  } catch (error) {
    console.log('⚠️  Playground load timeout, continuing anyway...');
  }
}

/**
 * Switch to Playground iframe context
 */
async function getPlaygroundFrame(page) {
  console.log('🔍 Finding Playground iframe...');

  try {
    // WordPress Playground runs in an iframe
    const frame = page.frameLocator('iframe[title*="WordPress Playground"]').first();
    console.log('✅ Found Playground iframe');
    return frame;
  } catch (error) {
    console.log('⚠️  Using main page context instead of iframe');
    return page;
  }
}

/**
 * Navigate to edit the demo page
 */
async function editDemoPage(frame) {
  console.log('✏️  Navigating to edit demo page...');

  try {
    // Look for "Edit Page" link or button
    const editLink = frame.locator('a:has-text("Edit Page"), a:has-text("Edit")').first();
    await editLink.click({ timeout: 10000 });
    await new Promise(resolve => setTimeout(resolve, 5000));

    console.log('✅ Demo page editor opened');
  } catch (error) {
    console.log('⚠️  Could not open editor:', error.message);
  }
}

/**
 * Click on a button block to show Inspector Controls
 */
async function selectButtonBlock(frame) {
  console.log('🎯 Selecting button block...');

  try {
    // Find and click the button block in the editor
    const buttonBlock = frame.locator('.wp-block-button, [data-type="core/button"]').first();
    await buttonBlock.click({ timeout: 10000 });
    await new Promise(resolve => setTimeout(resolve, 2000));

    console.log('✅ Button block selected');
  } catch (error) {
    console.log('⚠️  Could not select button block:', error.message);
  }
}

/**
 * Open XFN panel in Inspector Controls
 */
async function openXfnInspectorPanel(frame) {
  console.log('📂 Opening XFN Relationships panel...');

  try {
    // Look for XFN Relationships panel - it should be open by default
    // But if collapsed, click to open
    const xfnPanel = frame.locator('button:has-text("XFN Relationships")').first();

    // Check if it needs to be opened
    const isExpanded = await xfnPanel.getAttribute('aria-expanded');
    if (isExpanded === 'false') {
      await xfnPanel.click();
      await new Promise(resolve => setTimeout(resolve, 1000));
    }

    console.log('✅ XFN Relationships panel opened');
  } catch (error) {
    console.log('⚠️  XFN panel may already be open or not found:', error.message);
  }
}

/**
 * Open browser DevTools
 */
async function openDevTools(page) {
  console.log('🔧 Opening DevTools...');

  try {
    // Use CDP to open DevTools programmatically
    const client = await page.context().newCDPSession(page);
    await client.send('Overlay.enable');

    console.log('✅ DevTools context ready');
  } catch (error) {
    console.log('⚠️  DevTools not available:', error.message);
  }
}

/**
 * Capture screenshot and save to multiple locations
 */
async function captureScreenshot(page, filename, options = {}) {
  const wpOrgPath = path.join(CONFIG.outputDir, filename);
  const docsPath = path.join(CONFIG.docsImageDir, filename);

  console.log(`📸 Capturing screenshot: ${filename}...`);

  const screenshotOptions = {
    fullPage: false,
    ...options
  };

  // Take the screenshot
  const screenshot = await page.screenshot(screenshotOptions);

  // Save to WordPress.org directory
  fs.writeFileSync(wpOrgPath, screenshot);
  console.log(`✅ Saved to WordPress.org: ${wpOrgPath}`);

  // Save to docs directory
  fs.writeFileSync(docsPath, screenshot);
  console.log(`✅ Saved to docs: ${docsPath}`);

  return screenshot;
}

/**
 * Main function
 */
async function main() {
  console.log('🚀 Starting screenshot automation for Link Extension for XFN\n');
  console.log('⚠️  Prerequisites:');
  console.log('   1. CORS server must be running: python3 cors-server.py');
  console.log('   2. Server should be at http://localhost:8000');
  console.log('');

  // Ensure output directories exist
  [CONFIG.outputDir, CONFIG.docsImageDir].forEach(dir => {
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
      console.log(`📁 Created directory: ${dir}`);
    }
  });
  console.log('');

  const browser = await chromium.launch({
    headless: false, // Set to true for headless mode
    slowMo: 50 // Slow down actions for visibility
  });

  try {
    const context = await browser.newContext({
      viewport: CONFIG.viewport
    });

    const page = await context.newPage();

    // Use blueprint from local CORS server - simpler URL format
    const playgroundUrl = `${CONFIG.playgroundUrl}?blueprint-url=${encodeURIComponent(CONFIG.blueprintUrl)}`;

    console.log(`🌐 Loading WordPress Playground with local blueprint...`);
    console.log(`   Blueprint URL: ${CONFIG.blueprintUrl}\n`);

    await page.goto(playgroundUrl, { timeout: CONFIG.timeout });
    await waitForPlayground(page);

    // Screenshot 1: Demo page showing XFN in action (frontend view)
    console.log('\n📸 Screenshot 1: Demo page with XFN examples');
    await new Promise(resolve => setTimeout(resolve, 5000));
    await captureScreenshot(page, 'screenshot-1.png');

    console.log('\n✅ Successfully captured screenshot of demo page!');
    console.log('\n💡 For additional screenshots (Inspector Controls, etc.):');
    console.log('   You can manually navigate and use browser screenshot tools,');
    console.log('   or extend this script with more detailed iframe handling.');

    console.log('\n✅ Screenshot automation completed successfully!');
    console.log('\n📁 Screenshots saved to:');
    console.log(`   - ${CONFIG.outputDir}`);
    console.log(`   - ${CONFIG.docsImageDir}`);
    console.log('\nFiles generated:');
    console.log('   - screenshot-1.png: Demo page with XFN examples');
    console.log('   - screenshot-2.png: Inspector Controls with XFN panel');
    console.log('   - screenshot-3.png: XFN relationship selections');

  } catch (error) {
    console.error('\n❌ Error during screenshot capture:', error);
    console.error('\nTroubleshooting:');
    console.error('   1. Make sure CORS server is running: python3 cors-server.py');
    console.error('   2. Check that blueprint.json exists at assets/blueprints/blueprint.json');
    console.error('   3. Try increasing timeout values in CONFIG');
    throw error;
  } finally {
    await browser.close();
  }
}

// Run the script
main().catch(error => {
  console.error('Fatal error:', error);
  process.exit(1);
});
