---
name: dev-link-extension-for-xfn
description: Use when working in the link-extension-for-xfn WordPress plugin repo — build, test, lint, and release workflow for this repo specifically.
---

# Dev workflow: Link Extension for XFN

## Setup

```bash
git clone https://github.com/courtneyr-dev/link-extension-for-xfn.git
cd link-extension-for-xfn
npm install
composer install --prefer-dist --no-progress
npm run build
```

For active JS development with auto-rebuild: `npm run start` (alias: `npm run dev`).

## Build

- `npm run build` — runs `wp-scripts build --webpack-copy-php`
- `npm run start` — `wp-scripts start --blocks-manifest`, watches and rebuilds

## Test

CI (`.github/workflows/ci.yml`) runs three PHP versions against two WP versions:

- PHP 8.2, 8.3, 8.4 x WP `latest`, `trunk` (PHP 8.4 + WP `latest` excluded from the matrix)
- Test command: `composer install --prefer-dist --no-progress`, then `bash bin/install-wp-tests.sh wordpress_test root root 127.0.0.1 "$WP_VERSION" true` to install the WP test suite, then:
  - `composer test:unit` → `phpunit --testsuite=unit` (`tests/phpunit/unit`)
  - `composer test:integration` → `phpunit --testsuite=integration` (`tests/phpunit/integration`)
- PHPUnit version: `^9.6` (via `phpunit/phpunit` in composer.json, `yoast/phpunit-polyfills ^4.0`)
- MySQL 8.0 service container backs the integration suite.

Locally: `composer test` runs the full PHPUnit suite (both testsuites); `composer test:unit` / `composer test:integration` run one at a time.

## Lint

- `composer phpcs` — PHPCS against `phpcs.xml.dist` (canonical WordPress ruleset, tabs, PHPCompatibilityWP for PHP 8.2+, security sniffs as errors). Runs on PHP 8.2 in CI's `lint` job.
- `composer phpcbf` — auto-fix what PHPCS can fix.
- `composer phpstan -- --memory-limit=1G` — PHPStan level 5 (see `phpstan.neon`), scoped to `includes/` and the main plugin file, using `szepeviktor/phpstan-wordpress`. This gate is real — CI does not use `continue-on-error`.
- `npm run lint:js` — `wp-scripts lint-js`
- `npm run lint:css` — `wp-scripts lint-style`
- `npm run format` — `wp-scripts format`
- `composer audit` — dependency vulnerability scan, runs as CI's separate `security` job.

**The CI lint ruleset (`phpcs.xml.dist`) and PHPStan gate (`phpstan.neon`) are settled canon** — decided deliberately in PRs #2/#3 (2026-07-03) to match the sibling repos (post-kinds-for-indieweb, post-formats-for-block-themes). Do not relitigate or "modernize" this config without Courtney's explicit request.

## Branch/PR conventions

- Commit prefixes (Emoji-Log, going forward): `📦 NEW:`, `👌 IMPROVE:`, `🐛 FIX:`, `📖 DOC:`, `🚀 RELEASE:`, `🤖 TEST:`, `‼️ BREAKING:` — emoji + CAPS + imperative mood.
- Branch naming seen in history: `feature/<slug>` (e.g. `feature/ci-standards`, `feature/wp70-api-integration`).
- Merge via `gh pr merge <number> --squash --auto` unless a PR explicitly calls for a merge commit (PR #3 requested a merge commit specifically to preserve a mechanical phpcbf commit as a separate, revertible unit — call this out explicitly if it applies again).
- Always confirm PR state with `gh pr view`/`gh pr list` before merging or retargeting — see Gotchas below.

## Release steps

- Never cut a release, tag, or version bump without Courtney's explicit go-ahead. "Prepare" is not "ship."
- No auto-deploy exists in `.github/workflows/ci.yml` — it only runs lint, PHPStan, tests, and `composer audit`.
- `deploy-to-wordpress-org.sh` is the actual release mechanism: a standalone manual script that checks out the wp.org SVN repo (`https://plugins.svn.wordpress.org/link-extension-for-xfn/`) and pauses for an interactive keypress before proceeding (`read -p "Press Enter to continue..."`). It is not triggered automatically by tags, releases, or CI — someone has to run it by hand with wp.org SVN credentials.
- Because deployment is manual, a merged PR or a version bump in `readme.txt`/`link-extension-for-xfn.php` does not mean the plugin is live on wp.org. Check the actual wp.org plugin page or the SVN repo state before assuming a release shipped.

## Gotchas

- **CI lint/PHPStan config is settled canon** (see above) — don't reopen it.
- **PR merge sequencing**: verify actual state via `gh`, not local branch/git state. Concrete precedent: PR #2 was stacked on PR #1; PR #1 squash-merged first, which diverged PR #2's branch from main, forcing the same four commits to be reopened as PR #3 rebased onto main (see PR #3's description: "Originally stacked on #1 as #2 ... PR #1 was squash-merged first, so this is the same four commits rebased onto main").
- **Built JS artifacts must be committed, not just built locally**: PR #4 fixed a case where `build/interactivity/tooltip.js` was never built or committed, and `@wordpress/interactivity` was missing from `package-lock.json` entirely (so `npm ci` couldn't even install it to build it). Every git-based install shipped with dead front-end tooltips as a result — the enqueued module URL 404'd, which browsers reject for module scripts. Before shipping any change touching `build/` output or the Interactivity API, confirm the built file is actually tracked in git.
