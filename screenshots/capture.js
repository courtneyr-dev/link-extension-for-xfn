/**
 * Screenshot Automation for Link Extension for XFN
 *
 * This script uses Playwright to automatically capture screenshots
 * of the plugin in action using WordPress Playground.
 *
 * Usage: npm run screenshots
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

// Configuration
const CONFIG = {
  playgroundUrl: 'https://playground.wordpress.net/',
  blueprintUrl: 'https://raw.githubusercontent.com/courtneyr-dev/link-extension-for-xfn/main/blueprint.json',
  outputDir: path.join(__dirname, '..', '.wordpress-org'),
  viewport: {
    width: 1280,
    height: 720
  },
  timeout: 90000 // 90 seconds for WordPress Playground to load
};

/**
 * Wait for WordPress Playground to fully load
 */
async function waitForPlayground(page) {
  console.log('⏳ Waiting for WordPress Playground to load...');

  // Wait for the playground iframe to be ready
  await page.waitForLoadState('networkidle', { timeout: CONFIG.timeout });

  // Additional wait for WordPress editor to be fully ready
  await page.waitForTimeout(5000);

  console.log('✅ WordPress Playground loaded');
}

/**
 * Wait for WordPress editor to be ready
 */
async function waitForEditor(page) {
  console.log('⏳ Waiting for WordPress editor...');

  try {
    // Wait for the editor canvas to be present
    await page.waitForSelector('.edit-post-visual-editor', { timeout: 30000 });
    await page.waitForTimeout(2000);
    console.log('✅ Editor ready');
  } catch (error) {
    console.log('⚠️  Editor selector not found, continuing anyway...');
  }
}

/**
 * Create a link and open the link popover
 */
async function createLinkAndOpenPopover(page) {
  console.log('🔗 Creating link and opening popover...');

  try {
    // Click in the editor to focus
    await page.click('.edit-post-visual-editor');
    await page.waitForTimeout(1000);

    // Type some text
    await page.keyboard.type('Visit my website');

    // Select the text (Cmd/Ctrl+A)
    const modifier = process.platform === 'darwin' ? 'Meta' : 'Control';
    await page.keyboard.press(`${modifier}+a`);
    await page.waitForTimeout(500);

    // Create link (Cmd/Ctrl+K)
    await page.keyboard.press(`${modifier}+k`);
    await page.waitForTimeout(1500);

    // Type a URL
    const linkInput = await page.locator('input[placeholder*="Search"], input[type="text"]').first();
    await linkInput.fill('https://example.com');
    await page.waitForTimeout(500);

    // Press Enter or click Submit
    await page.keyboard.press('Enter');
    await page.waitForTimeout(2000);

    // Click on the link to open the popover
    await page.click('a:has-text("Visit my website")');
    await page.waitForTimeout(1500);

    console.log('✅ Link popover opened');
  } catch (error) {
    console.error('❌ Error creating link:', error.message);
    throw error;
  }
}

/**
 * Open the Advanced section in link popover
 */
async function openAdvancedSection(page) {
  console.log('📂 Opening Advanced section...');

  try {
    // Look for "Advanced" button/toggle
    const advancedButton = await page.locator('button:has-text("Advanced"), .components-toggle-control:has-text("Advanced")').first();
    await advancedButton.click();
    await page.waitForTimeout(1000);

    console.log('✅ Advanced section opened');
  } catch (error) {
    console.error('❌ Error opening Advanced section:', error.message);
    throw error;
  }
}

/**
 * Open the XFN collapsible section
 */
async function openXfnSection(page) {
  console.log('🎯 Opening XFN section...');

  try {
    // Look for XFN toggle button
    const xfnToggle = await page.locator('button:has-text("XFN"), .components-panel__body-toggle:has-text("XFN")').first();
    await xfnToggle.click();
    await page.waitForTimeout(1500);

    console.log('✅ XFN section opened');
  } catch (error) {
    console.error('❌ Error opening XFN section:', error.message);
    throw error;
  }
}

/**
 * Select some XFN relationships
 */
async function selectRelationships(page) {
  console.log('✨ Selecting XFN relationships...');

  try {
    // Select "friend" relationship
    await page.click('button:has-text("friend"), input[value="friend"]');
    await page.waitForTimeout(500);

    // Select "met" relationship
    await page.click('button:has-text("met"), input[value="met"]');
    await page.waitForTimeout(500);

    // Select "colleague" relationship
    await page.click('button:has-text("colleague"), input[value="colleague"]');
    await page.waitForTimeout(1000);

    console.log('✅ Relationships selected');
  } catch (error) {
    console.log('⚠️  Some relationships may not have been selected:', error.message);
  }
}

/**
 * Capture screenshot
 */
async function captureScreenshot(page, filename) {
  const outputPath = path.join(CONFIG.outputDir, filename);

  console.log(`📸 Capturing screenshot: ${filename}...`);

  await page.screenshot({
    path: outputPath,
    fullPage: false
  });

  console.log(`✅ Screenshot saved: ${outputPath}`);
}

/**
 * Main function
 */
async function main() {
  console.log('🚀 Starting screenshot automation for Link Extension for XFN\n');

  // Ensure output directory exists
  if (!fs.existsSync(CONFIG.outputDir)) {
    fs.mkdirSync(CONFIG.outputDir, { recursive: true });
    console.log(`📁 Created output directory: ${CONFIG.outputDir}\n`);
  }

  const browser = await chromium.launch({
    headless: false, // Set to true for headless mode
    slowMo: 100 // Slow down actions for visibility
  });

  try {
    const context = await browser.newContext({
      viewport: CONFIG.viewport
    });

    const page = await context.newPage();

    // Use blueprint from GitHub
    const playgroundUrl = `${CONFIG.playgroundUrl}?blueprint-url=${encodeURIComponent(CONFIG.blueprintUrl)}`;

    console.log(`🌐 Loading WordPress Playground with blueprint from GitHub...\n`);
    await page.goto(playgroundUrl, { waitUntil: 'networkidle', timeout: CONFIG.timeout });

    await waitForPlayground(page);
    await waitForEditor(page);

    // Workflow: Create link and show XFN interface
    await createLinkAndOpenPopover(page);
    await openAdvancedSection(page);
    await openXfnSection(page);
    await selectRelationships(page);

    // Capture the screenshot
    await captureScreenshot(page, 'screenshot-1.png');

    console.log('\n✅ Screenshot automation completed successfully!');

  } catch (error) {
    console.error('\n❌ Error during screenshot capture:', error);
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
