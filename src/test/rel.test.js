/**
 * Run with `npm run test:unit` (Node's built-in test runner).
 */
// eslint-disable-next-line vitest/no-import-node-test -- The plugin installs no test runner; Node's built-in one needs none.
import { test } from 'node:test';
import assert from 'node:assert/strict';

import { getBlockRel, setBlockXfn } from '../rel.js';

// Core's Navigation Link and Navigation Submenu declare a string `rel`
// attribute and render it on their anchor; Embed declares none.
const navigationLink = { attributes: { rel: { type: 'string' } } };
const navigationSubmenu = { attributes: { rel: { type: 'string' } } };
const embed = { attributes: { url: { type: 'string' } } };

test( 'writes XFN to a Navigation Link rel attribute it has not set yet', () => {
	for ( const blockType of [ navigationLink, navigationSubmenu ] ) {
		assert.deepEqual(
			setBlockXfn(
				blockType,
				{ label: 'Ada', url: 'https://example.com' },
				[ 'colleague' ]
			),
			{ rel: 'colleague' }
		);
	}
} );

test( 'keeps unrelated tokens when XFN is added, swapped or removed', () => {
	assert.deepEqual(
		setBlockXfn( navigationLink, { rel: 'nofollow external' }, [
			'colleague',
		] ),
		{ rel: 'nofollow external colleague' }
	);
	assert.deepEqual(
		setBlockXfn( navigationLink, { rel: 'noopener friend' }, [
			'acquaintance',
		] ),
		{ rel: 'noopener acquaintance' }
	);
	assert.deepEqual(
		setBlockXfn( navigationLink, { rel: 'nofollow noopener friend' }, [] ),
		{ rel: 'nofollow noopener' }
	);
} );

test( 'clears rel when its last token is removed', () => {
	assert.deepEqual( setBlockXfn( navigationLink, { rel: 'friend' }, [] ), {
		rel: undefined,
	} );
} );

test( 'reads a Navigation Link rel from the attribute core renders', () => {
	assert.equal(
		getBlockRel( navigationLink, { rel: 'nofollow colleague' } ),
		'nofollow colleague'
	);
	assert.equal(
		getBlockRel( navigationLink, { metadata: { rel: 'friend' } } ),
		''
	);
} );

test( 'keeps XFN in metadata.rel for blocks without a rel attribute', () => {
	assert.deepEqual(
		setBlockXfn(
			embed,
			{ url: 'https://example.com', metadata: { name: 'Card' } },
			[ 'friend' ]
		),
		{ metadata: { name: 'Card', rel: 'friend' } }
	);
	assert.equal(
		getBlockRel( embed, { metadata: { rel: 'nofollow friend' } } ),
		'nofollow friend'
	);
} );
