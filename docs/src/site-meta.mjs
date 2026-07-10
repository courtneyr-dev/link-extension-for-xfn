// Single source of per-plugin parameters for the docs site.
// Everything else (astro.config.mjs, Head.astro, structured data) reads from here.
export default {
	name: 'Link Extension for XFN',
	slug: 'link-extension-for-xfn',
	site: 'https://courtneyr-dev.github.io',
	base: '/link-extension-for-xfn',
	description:
		'User documentation for Link Extension for XFN: add XFN relationship metadata to links in the WordPress block editor.',
	github: 'https://github.com/courtneyr-dev/link-extension-for-xfn',
	wporg: 'https://wordpress.org/plugins/link-extension-for-xfn/',
	version: '1.0.4',
	requiresWP: '6.9',
	requiresPHP: '8.2',
	author: 'Courtney Robertson',
	authorUrl: 'https://courtneyr.dev/',
};
