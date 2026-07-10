# Documentation plan

Plan and status for the Link Extension for XFN user documentation, including open items that need maintainer review.

## Plugin summary

Link Extension for XFN (version 1.1.0, WordPress 6.9+, PHP 8.2 per plugin header) adds XFN relationship options to the block editor's link tools. Relationships — 18 values in 7 categories from the XFN 1.1 spec — are stored in the standard HTML `rel` attribute of links in post content and mirrored to `_xfn_relationships` post meta. It ships three frontend blocks (XFN Blogroll, Relationship Badge, Relationship Directory) that build lists from a site-wide scan of published content, frontend tooltips gated to WordPress 7.0+, an automatic bridge for the Outpost plugin's Micropub XFN data, and Abilities API integration for automation. It does not use the classic Link Manager and makes no external requests.

## Audience

WordPress site owners, admins, and editors; bloggers adding relationship context to links; IndieWeb users (`rel="me"`, Micropub via Outpost); site builders working with Button/Navigation/Image links; blogroll and directory keepers. Not plugin developers, except for the "For developers and maintainers" pointers.

## Key user tasks

1. Add relationships to an inline text link (Advanced panel, always on).
2. Enable Inspector Controls and tag block-level links (Button, Image, Navigation Link, Site Logo, Post Title, Query Title, Embed).
3. Display XFN data with the Blogroll, Relationship Badge, and Relationship Directory blocks.
4. Confirm `rel` output on the frontend.
5. Understand privacy implications of publishing relationships.
6. Troubleshoot missing panels, empty blocks, and absent tooltips.

## Proposed docs pages

The standard set is in place: `index.md` (hub), `installation.md`, `getting-started.md`, `settings.md` (updated in place), `common-tasks.md`, `screenshots.md`, `troubleshooting.md`, `faq.md`, `privacy-and-data.md`, `accessibility.md`, plus this plan. The pre-existing detailed guides (USER-GUIDE, paragraph-links, button-links, image-links, other-block-links, XFN-TESTING-GUIDE, blog-post) are linked from the hub; on 2026-07-10 their floating-toolbar walkthroughs were removed along with the feature. `docs/README.md` now points to `index.md`.

## Screenshot inventory

One existing asset copied into place (`frontend-playground-demo.png`); nine captures still needed, including settings, both editor surfaces, frontend `rel` inspection, the WP 7.0+ tooltip, and the three blocks. Full table with setup data and alt text: [screenshots.md](screenshots.md). Capture tooling exists (`npm run screenshots` → `screenshots/capture.js`, Playwright + Playground) but currently outputs to `.wordpress-org/` and `docs/images/`, not `docs/assets/screenshots/`.

## Hosting recommendation

GitHub Pages from `/docs` on the main branch, plain Markdown with the Primer Jekyll theme (`docs/_config.yml` added). `index.md` is the navigation hub. No MkDocs or extra tooling.

## Validation checklist

- [ ] Every claim traceable to the repo (plugin source, readme.txt, CHANGELOG, audit).
- [ ] Version/compat statements cite the plugin header (1.1.0, WP 6.9+, PHP 8.2).
- [x] Floating toolbar button removed from the plugin and from all user docs (historical reports excepted).
- [ ] Tooltip mentions always carry the WordPress 7.0+ caveat.
- [ ] Block docs state the published-content scan and ~5 minute cache.
- [ ] Internal links resolve (relative paths); nav footers on every page.
- [ ] Only existing images are embedded; planned ones reference screenshots.md.
- [ ] No WCAG compliance claims — evidence described, not certified.

## Assumptions

- The `build/` directory stays committed, so installs need no build step.
- The three blocks and the Outpost/meta-mirror integrations remain enabled by default (they're behind default-on feature flags).
- The WordPress.org listing referenced throughout the repo (support forum, Playground preview links) exists; docs phrase installs primarily via GitHub/ZIP to stay safe.

## Needs maintainer review

1. **Floating Toolbar Button — resolved 2026-07-10: removed.** The setting and its copy were removed in the 1.1.1 cycle (the button was advertised in 1.0.x–1.1.0 but never implemented). The pre-existing guides have been updated; historical QA/report documents still mention it as a matter of record.
2. **PHP requirement mismatch.** Header and readme.txt say Requires PHP 8.2; the activation gate only enforces PHP 7.4; CHANGELOG still says "PHP 7.4+ required"; composer requires >=8.2. Docs follow the header (8.2). Confirm the true floor and align all files.
3. **Tooltips gated to WordPress 7.0+** — with the supported floor now at 6.9 and "Tested up to" at 7.0 (2026-07-09 audit), only sites still on 6.9 lack tooltips. readme.txt and these docs state the gate; no further action unless the flag's timing changes.
4. **The three blocks were undocumented** in readme.txt, README.md, and prior docs. They're now documented here from source; confirm they're intended for release (they sit behind a default-on `blocks` feature flag) and add them to readme.txt.
5. **Two divergent Playground blueprints** (root `blueprint.json` draft-on-post-new vs `assets/blueprints/blueprint.json` published `/xfn-demo/`). Confirm which drives the wp.org live preview.
6. **Stale deploy/test artifacts:** `deploy-to-wordpress-org.sh` hardcodes version 1.0.3 with a 1.0.1-era commit message; `setup-local-test.sh` and `screenshots/README.md` reference the old `xfn-link-extension` slug/paths; `npm run sync` points to a missing `sync-to-local.sh`.
7. **WCAG 2.2 AA and screen reader testing claims are self-asserted** — no audit artifact in the repo. Docs describe rather than certify; consider an external audit or softer readme wording.
8. **Nonce without a verifier — resolved 2026-07-10: removed.** The unused `xfn_link_extension` nonce is no longer localized; if the planned REST validation endpoint ships later, mint a purpose-named nonce with it.
9. **`_xfn_relationships` meta is REST-visible to users who can edit posts** — confirm this exposure is intended and consider noting it in readme.txt's privacy-adjacent text.
10. **Screenshot tooling output paths** — `screenshots/capture.js` writes to `.wordpress-org/` and `docs/images/`; documentation captures now use the separate `npm run screenshots:docs` (`screenshots/capture-docs.js`) targeting `docs/assets/screenshots/`.
11. **WordPress.org listing confirmation** — the repo links to a wordpress.org plugin page throughout; confirm the listing is live so installation.md can state directory installs unconditionally.
12. **Demo page `rel` loss — retracted 2026-07-10; no bug.** The 2026-07-09 finding was a testing artifact: the unauthenticated check received Playground's empty auto-login redirect and misread it as missing markup. Verified on WordPress 7.0.1 with an authenticated fetch: stored content keeps all 14 `rel` attributes, the rendered page carries them, tooltip wraps render, and the hover tooltip works (screenshot captured). The blueprint and the plugin's render path are both correct.

---

[Documentation home](index.md) · Previous: [Screenshots](screenshots.md)
