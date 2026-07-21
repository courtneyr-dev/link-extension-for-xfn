#!/usr/bin/env node
/**
 * Capture user-documentation screenshots for Link Extension for XFN.
 *
 * Companion to capture.js (which drives playground.wordpress.net for the
 * WordPress.org listing assets). This script instead boots a LOCAL disposable
 * WordPress via WordPress Playground CLI — no Docker, no remote iframe — and
 * captures the screens listed in docs/src/content/docs/screenshots.md into
 * docs/src/assets/screenshots/.
 *
 * It reuses the demo content from assets/blueprints/blueprint.json via
 * screenshots/docs-blueprint.json (Inspector Controls enabled, published
 * /xfn-demo/ page with XFN-tagged links, plus published pages containing the
 * Blogroll, Relationship Badge, and Relationship Directory blocks).
 *
 * Prerequisites:
 *   - Node.js 18+
 *   - npm install (installs Playwright from devDependencies)
 *   - npx playwright install chromium (once, to download the browser)
 *
 * Usage:
 *   node screenshots/capture-docs.js
 *
 * Environment variables:
 *   WP_BASE_URL      Capture against an already-running WordPress instead of
 *                    launching Playground. No credentials are stored here.
 *   PLAYGROUND_PORT  Port for the disposable Playground server (default 9400).
 */

const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');

const REPO_ROOT = path.resolve(__dirname, '..');
const OUT_DIR = path.join(REPO_ROOT, 'docs', 'src', 'assets', 'screenshots');
const BLUEPRINT = path.join(__dirname, 'docs-blueprint.json');
const PORT = process.env.PLAYGROUND_PORT || '9400';
const EXTERNAL_URL = process.env.WP_BASE_URL || '';
const BASE = EXTERNAL_URL || `http://127.0.0.1:${PORT}`;

function resolveChromium() {
	try {
		return require('playwright').chromium;
	} catch (e) {
		try {
			return require('@playwright/test').chromium;
		} catch (e2) {
			console.error(
				'Playwright is not installed. Run `npm install` in the repo root, then `npx playwright install chromium`.'
			);
			process.exit(1);
		}
	}
}

async function waitForServer(url, timeoutMs) {
	const deadline = Date.now() + timeoutMs;
	while (Date.now() < deadline) {
		try {
			const res = await fetch(url, { redirect: 'manual' });
			if (res.status > 0 && res.status < 500) {
				return;
			}
		} catch (e) {
			// Not up yet.
		}
		await new Promise((r) => setTimeout(r, 2000));
	}
	throw new Error(`WordPress did not become reachable at ${url} within ${timeoutMs / 1000}s.`);
}

function launchPlayground() {
	console.log(`Starting WordPress Playground on port ${PORT} (downloads WordPress on first run)...`);
	const child = spawn(
		'npx',
		[
			'--yes',
			'@wp-playground/cli@latest',
			'server',
			'--auto-mount',
			REPO_ROOT,
			'--blueprint',
			BLUEPRINT,
			'--login',
			'--port',
			PORT,
		],
		{ stdio: ['ignore', 'pipe', 'pipe'] }
	);
	child.stdout.on('data', (d) => process.stdout.write(`[playground] ${d}`));
	child.stderr.on('data', (d) => process.stderr.write(`[playground] ${d}`));
	child.on('exit', (code) => {
		if (code && code !== 0 && !shuttingDown) {
			console.error(`Playground exited unexpectedly with code ${code}.`);
			process.exit(1);
		}
	});
	return child;
}

let shuttingDown = false;

/**
 * Dismiss the block editor welcome guide (and any other modal) so it never
 * appears in captures.
 */
async function dismissEditorChrome(page) {
	// The welcome guide can render a beat after the canvas, and the
	// preferences store may not be ready on the first try — keep retrying
	// until no modal overlay is left.
	// On a cold boot the guide can render several seconds after the canvas,
	// so only proceed once no modal has been visible for a stable stretch.
	await page.waitForTimeout(1000);
	const deadline = Date.now() + 30000;
	let stableChecks = 0;
	while (stableChecks < 4) {
		await page
			.evaluate(() => {
				const prefs = window.wp?.data?.dispatch('core/preferences');
				if (prefs) {
					prefs.set('core/edit-post', 'welcomeGuide', false);
					prefs.set('core/edit-site', 'welcomeGuide', false);
					prefs.set('core', 'welcomeGuide', false);
				}
			})
			.catch(() => {});
		const overlay = page.locator('.components-modal__screen-overlay');
		if ((await overlay.count()) === 0) {
			stableChecks++;
		} else {
			stableChecks = 0;
			const close = overlay.locator('button[aria-label="Close"]').first();
			try {
				if ((await close.count()) > 0) {
					await close.click({ timeout: 1500 });
				}
			} catch (e) {
				// Modal already closing.
			}
		}
		if (Date.now() > deadline) {
			throw new Error('Could not dismiss the editor welcome guide.');
		}
		await page.waitForTimeout(500);
	}
}

/**
 * Open the page editor for a page found by slug and wait for the canvas.
 */
async function openPageInEditor(page, slug) {
	// Use the browser context's request (shares the logged-in cookies) —
	// a plain fetch loops on Playground's auto-login redirect.
	const res = await page.request.get(`${BASE}/wp-json/wp/v2/pages?slug=${slug}`);
	const pages = await res.json();
	if (!Array.isArray(pages) || !pages.length) {
		throw new Error(`Could not find the /${slug}/ page via REST.`);
	}
	await page.goto(`${BASE}/wp-admin/post.php?post=${pages[0].id}&action=edit`, {
		waitUntil: 'domcontentloaded',
	});
	const canvas = page.frameLocator('iframe[name="editor-canvas"]');
	await canvas.locator('.wp-block-post-content').waitFor({ timeout: 60000 });
	await dismissEditorChrome(page);
	// Give the plugin's link-control monitoring (1s startup delay) time to arm.
	await page.waitForTimeout(1500);
	return canvas;
}

/**
 * Make sure the settings sidebar is open in the editor.
 */
async function ensureSettingsSidebar(page) {
	const toggle = page.locator('button[aria-label="Settings"]').first();
	const pressed = await toggle.getAttribute('aria-pressed');
	const expanded = await toggle.getAttribute('aria-expanded');
	if (pressed !== 'true' && expanded !== 'true') {
		await toggle.click();
		await page.waitForTimeout(500);
	}
}

/**
 * Screenshot 3: link popover with Advanced expanded and the XFN section open
 * on the demo page's "friend met" paragraph link.
 */
async function captureLinkAdvanced(page, canvas, shoot) {
	// Scroll the paragraph to the top of the canvas so the link popover has
	// room to show the Advanced panel and the XFN section below the link.
	const link = canvas.locator('a', { hasText: "my friend John's website" }).first();
	await link.evaluate((el) => el.scrollIntoView({ block: 'start', behavior: 'instant' }));
	await page.waitForTimeout(400);
	await link.click();
	await page.waitForTimeout(800);

	// Enter the popover's edit mode if a preview with an Edit button is shown.
	const popover = page.locator('.components-popover .block-editor-link-control').first();
	if ((await popover.count()) === 0) {
		await link.click();
		await page.waitForTimeout(800);
	}
	const editButton = page
		.locator(
			'.block-editor-link-control button[aria-label="Edit link"], .block-editor-link-control button[aria-label="Edit"]'
		)
		.first();
	if ((await editButton.count()) > 0) {
		await editButton.click();
		await page.waitForTimeout(500);
	}

	// Expand the Advanced panel (the plugin injects its XFN section there).
	const advanced = page.locator('.block-editor-link-control__tools button').first();
	await advanced.waitFor({ timeout: 10000 });
	if ((await advanced.getAttribute('aria-expanded')) !== 'true') {
		await advanced.click();
	}
	const xfnToggle = page.locator('.xfn-section-toggle').first();
	try {
		await xfnToggle.waitFor({ timeout: 5000 });
	} catch (e) {
		// Re-trigger the injection by collapsing and re-expanding Advanced.
		await advanced.click();
		await page.waitForTimeout(300);
		await advanced.click();
		await xfnToggle.waitFor({ timeout: 10000 });
	}

	// Expand the XFN section itself.
	if ((await xfnToggle.getAttribute('aria-expanded')) !== 'true') {
		await xfnToggle.click();
	}
	await page.locator('.xfn-section-content').first().waitFor({ state: 'visible', timeout: 5000 });

	// The link carries rel="friend met": both buttons must show as pressed.
	const pressedCount = await page
		.locator('.xfn-section-content .xfn-button-group .components-button.is-pressed')
		.count();
	if (pressedCount < 2) {
		throw new Error(
			`Expected friend + met to be selected in the XFN popover section, found ${pressedCount} pressed buttons.`
		);
	}
	// Keep the count badge (on the XFN toggle) and the button groups in view.
	await xfnToggle.scrollIntoViewIfNeeded();
	await page.locator('.xfn-count-badge').first().waitFor({ timeout: 5000 });
	await page.waitForTimeout(400);
	await shoot('editor-link-advanced-xfn.png');

	// Close the popover so it does not linger over later captures.
	await page.keyboard.press('Escape');
	await page.keyboard.press('Escape');
	await page.waitForTimeout(300);
}

/**
 * Screenshots 4 + 5: Inspector Controls XFN panel on the demo Button block
 * (rel="friend met colleague"), and the pill summary close-up.
 */
async function captureInspectorControls(page, canvas, shoot) {
	await ensureSettingsSidebar(page);

	// Select the Button block programmatically so no caret lands in the
	// button text (a caret would open its link preview popover over the
	// canvas).
	const selected = await page.evaluate(() => {
		const select = window.wp?.data?.select('core/block-editor');
		const dispatch = window.wp?.data?.dispatch('core/block-editor');
		if (!select || !dispatch) {
			return false;
		}
		const findButton = (blocks) => {
			for (const block of blocks) {
				if (block.name === 'core/button') {
					return block.clientId;
				}
				const inner = findButton(block.innerBlocks || []);
				if (inner) {
					return inner;
				}
			}
			return null;
		};
		const clientId = findButton(select.getBlocks());
		if (!clientId) {
			return false;
		}
		dispatch.selectBlock(clientId);
		return true;
	});
	if (!selected) {
		throw new Error('Could not select the demo Button block.');
	}
	await page.waitForTimeout(600);

	const panel = page.locator('.xfn-inspector-panel').first();
	await panel.waitFor({ timeout: 15000 });

	// Open the panel if it is collapsed.
	const panelToggle = panel.locator('button.components-panel__body-toggle').first();
	if ((await panelToggle.count()) > 0 && (await panelToggle.getAttribute('aria-expanded')) !== 'true') {
		await panelToggle.click();
	}

	const summary = page.locator('.xfn-selected-summary').first();
	await summary.waitFor({ timeout: 10000 });
	const pillCount = await summary.locator('.xfn-pill').count();
	if (pillCount < 3) {
		throw new Error(
			`Expected at least 3 relationship pills on the Button block, found ${pillCount}.`
		);
	}

	// Pin the panel to the top of the sidebar so the title and the selected
	// radio and checkbox groups (Friend, Met, Colleague) are visible; back
	// off slightly so the sticky tab header does not cover the panel title.
	await panel.evaluate((el) => {
		el.scrollIntoView({ block: 'start', behavior: 'instant' });
		let scroller = el.parentElement;
		while (scroller && scroller.scrollHeight <= scroller.clientHeight) {
			scroller = scroller.parentElement;
		}
		if (scroller) {
			scroller.scrollBy(0, -70);
		}
	});
	await page.waitForTimeout(400);
	await shoot('editor-inspector-controls-button.png');

	// Bring the pill summary back into view for the close-up shot.
	await summary.scrollIntoViewIfNeeded();
	await page.waitForTimeout(400);

	// Close-up of the pill summary for the active-pills shot.
	const box = await summary.boundingBox();
	if (!box) {
		throw new Error('Could not measure the pill summary for the close-up shot.');
	}
	const pad = 16;
	await shoot('editor-active-pills.png', {
		clip: {
			x: Math.max(0, box.x - pad),
			y: Math.max(0, box.y - pad),
			width: Math.min(1280 - Math.max(0, box.x - pad), box.width + pad * 2),
			height: Math.min(800 - Math.max(0, box.y - pad), box.height + pad * 2),
		},
	});
}

/**
 * Frontend block pages published by the blueprint.
 */
async function captureFrontendBlocks(page, shoot) {
	await page.goto(`${BASE}/xfn-blogroll-demo/`, { waitUntil: 'networkidle' });
	await page.locator('.xfn-blogroll__group-title').first().waitFor({ timeout: 15000 });
	await shoot('frontend-blogroll-block.png');

	await page.goto(`${BASE}/xfn-badge-demo/`, { waitUntil: 'networkidle' });
	// The tooltip filter injects hidden pills into the badge's anchor, so
	// wait for a *visible* pill.
	await page.locator('.xfn-relationship-badge .xfn-pill:visible').first().waitFor({ timeout: 15000 });
	await shoot('frontend-relationship-badge.png');

	await page.goto(`${BASE}/xfn-directory-demo/`, { waitUntil: 'networkidle' });
	await page.locator('.xfn-directory__filter-btn').first().waitFor({ timeout: 15000 });
	await shoot('frontend-relationship-directory.png');
}

/**
 * Best-effort capture of real browser DevTools inspecting a demo-page anchor.
 *
 * Launches a headed Chromium with --auto-open-devtools-for-tabs and a remote
 * debugging port, then drives the DevTools frontend itself over the Chrome
 * DevTools Protocol: an Elements-panel search for the anchor's CSS selector
 * reveals and highlights the node (rel attribute visible), and
 * Page.captureScreenshot grabs the real DevTools UI. No compositing.
 *
 * Returns true when the capture succeeded.
 */
async function captureDevtools(chromium) {
	const DEBUG_PORT = 9401;
	const browser = await chromium.launch({
		headless: false,
		args: [
			'--auto-open-devtools-for-tabs',
			`--remote-debugging-port=${DEBUG_PORT}`,
			'--window-size=1500,1000',
		],
	});
	try {
		const ctx = await browser.newContext({ viewport: null });
		const page = await ctx.newPage();
		// The unauthenticated first request follows Playground's auto-login
		// redirect and lands back on the page.
		await page.goto(`${BASE}/xfn-demo/`, { waitUntil: 'networkidle' });
		await page.waitForTimeout(2000);

		// Find the DevTools frontend target for the demo-page tab.
		const listRes = await fetch(`http://127.0.0.1:${DEBUG_PORT}/json`);
		const targets = await listRes.json();
		const devtoolsTarget = targets.find((t) => (t.url || '').startsWith('devtools://'));
		if (!devtoolsTarget || !devtoolsTarget.webSocketDebuggerUrl) {
			throw new Error('No DevTools frontend target found.');
		}

		const ws = new WebSocket(devtoolsTarget.webSocketDebuggerUrl);
		let msgId = 0;
		const pending = new Map();
		const send = (method, params = {}) =>
			new Promise((resolve, reject) => {
				const id = ++msgId;
				pending.set(id, { resolve, reject });
				ws.send(JSON.stringify({ id, method, params }));
			});
		await new Promise((resolve, reject) => {
			ws.onopen = resolve;
			ws.onerror = () => reject(new Error('Could not connect to the DevTools frontend.'));
		});
		ws.onmessage = (event) => {
			const msg = JSON.parse(event.data);
			if (msg.id && pending.has(msg.id)) {
				const { resolve, reject } = pending.get(msg.id);
				pending.delete(msg.id);
				if (msg.error) {
					reject(new Error(msg.error.message));
				} else {
					resolve(msg.result);
				}
			}
		};

		const key = async (keyName, code, keyCode, modifiers = 0, text = '') => {
			await send('Input.dispatchKeyEvent', {
				type: text ? 'keyDown' : 'rawKeyDown',
				key: keyName,
				code,
				windowsVirtualKeyCode: keyCode,
				nativeVirtualKeyCode: keyCode,
				modifiers,
				text,
			});
			await send('Input.dispatchKeyEvent', {
				type: 'keyUp',
				key: keyName,
				code,
				windowsVirtualKeyCode: keyCode,
				nativeVirtualKeyCode: keyCode,
				modifiers,
			});
		};

		// Open the Elements panel search (Cmd/Ctrl+F) and search by CSS
		// selector, which reveals + highlights the matching anchor.
		const meta = process.platform === 'darwin' ? 4 : 2;
		await key('f', 'KeyF', 70, meta);
		await new Promise((r) => setTimeout(r, 800));
		await send('Input.insertText', { text: 'a[rel="colleague met co-worker"]' });
		await new Promise((r) => setTimeout(r, 800));
		await key('Enter', 'Enter', 13);
		await new Promise((r) => setTimeout(r, 1200));

		// The docked DevTools surface spans the whole window with a blank
		// area where the page shows through — clip to the docked region,
		// which starts where the page viewport ends.
		let clip;
		try {
			const pageWidth = await page.evaluate(() => window.innerWidth);
			const win = await send('Runtime.evaluate', {
				expression: 'JSON.stringify({ w: window.innerWidth, h: window.innerHeight })',
				returnByValue: true,
			});
			const { w, h } = JSON.parse(win.result.value);
			if (w - pageWidth > 200) {
				clip = { x: pageWidth, y: 0, width: w - pageWidth, height: h, scale: 2 };
			}
		} catch (e) {
			// Fall back to a full capture.
		}
		const shot = await send('Page.captureScreenshot', clip ? { format: 'png', clip } : { format: 'png' });
		ws.close();
		const target = path.join(OUT_DIR, 'frontend-rel-devtools.png');
		fs.writeFileSync(target, Buffer.from(shot.data, 'base64'));
		console.log(`captured ${path.relative(REPO_ROOT, target)}`);
		return true;
	} finally {
		await browser.close().catch(() => {});
	}
}

(async () => {
	fs.mkdirSync(OUT_DIR, { recursive: true });
	const chromium = resolveChromium();

	let playground = null;
	if (!EXTERNAL_URL) {
		playground = launchPlayground();
	}

	try {
		await waitForServer(BASE + '/', 240000);
		console.log(`WordPress is up at ${BASE}`);

		const browser = await chromium.launch();
		const ctx = await browser.newContext({
			viewport: { width: 1280, height: 800 },
			deviceScaleFactor: 2,
		});
		const page = await ctx.newPage();

		// Prime the logged-in admin session.
		await page.goto(BASE + '/wp-admin/', { waitUntil: 'networkidle' });
		if (!/wp-admin/.test(page.url()) || /wp-login/.test(page.url())) {
			throw new Error(
				`Could not reach a logged-in wp-admin at ${BASE}. If you passed WP_BASE_URL, make sure the session does not require interactive login.`
			);
		}

		const shoot = async (file, options = {}) => {
			const target = path.join(OUT_DIR, file);
			await page.screenshot({ path: target, ...options });
			console.log(`captured ${path.relative(REPO_ROOT, target)}`);
		};

		// 1. Settings → Link Extension for XFN.
		await page.goto(BASE + '/wp-admin/options-general.php?page=xfn-link-extension', {
			waitUntil: 'networkidle',
		});
		await shoot('admin-settings-overview.png');

		// 2. Frontend demo page with XFN-tagged links (published by the blueprint).
		await page.goto(BASE + '/xfn-demo/', { waitUntil: 'networkidle' });
		await shoot('frontend-xfn-demo-page.png');

		// 3–5. Editor captures on the demo page.
		const canvas = await openPageInEditor(page, 'xfn-demo');
		await captureLinkAdvanced(page, canvas, shoot);
		await captureInspectorControls(page, canvas, shoot);

		// 6–8. Frontend block pages (Blogroll, Relationship Badge, Directory).
		await captureFrontendBlocks(page, shoot);

		await browser.close();

		// 9. Best-effort: real DevTools inspecting a demo-page anchor.
		try {
			await captureDevtools(chromium);
		} catch (e) {
			console.warn(
				`Skipping frontend-rel-devtools.png (DevTools capture failed): ${e.message || e}`
			);
		}

		console.log(`Done. Screenshots are in ${path.relative(REPO_ROOT, OUT_DIR)}/`);
	} finally {
		if (playground) {
			shuttingDown = true;
			playground.kill('SIGTERM');
		}
	}
})().catch((e) => {
	console.error(e.message || e);
	process.exit(1);
});
