#!/usr/bin/env node
/**
 * Capture user-documentation screenshots for Link Extension for XFN.
 *
 * Companion to capture.js (which drives playground.wordpress.net for the
 * WordPress.org listing assets). This script instead boots a LOCAL disposable
 * WordPress via WordPress Playground CLI — no Docker, no remote iframe — and
 * captures the screens listed in docs/screenshots.md into
 * docs/assets/screenshots/.
 *
 * It reuses the demo content from assets/blueprints/blueprint.json via
 * screenshots/docs-blueprint.json (Inspector Controls enabled, published
 * /xfn-demo/ page with XFN-tagged links).
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
const OUT_DIR = path.join(REPO_ROOT, 'docs', 'assets', 'screenshots');
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

		const shoot = async (file) => {
			const target = path.join(OUT_DIR, file);
			await page.screenshot({ path: target });
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

		await browser.close();
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
