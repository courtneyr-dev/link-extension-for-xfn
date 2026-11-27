/**
 * Rel token helpers shared by the XFN editor controls.
 */

const XFN_VALUES = [
	'contact',
	'acquaintance',
	'friend',
	'met',
	'co-worker',
	'colleague',
	'co-resident',
	'neighbor',
	'child',
	'parent',
	'sibling',
	'spouse',
	'kin',
	'muse',
	'crush',
	'date',
	'sweetheart',
	'me',
];

/**
 * Parse rel attribute to separate XFN and non-XFN values
 *
 * @param {string} relString Space-separated rel tokens.
 * @return {{xfn: string[], other: string[]}} XFN tokens and every other token.
 */
export function parseRelAttribute( relString ) {
	if ( ! relString ) {
		return { xfn: [], other: [] };
	}

	const relParts = relString.split( /\s+/ ).filter( Boolean );
	const xfn = [];
	const other = [];

	relParts.forEach( ( part ) => {
		if ( XFN_VALUES.includes( part ) ) {
			xfn.push( part );
		} else {
			other.push( part );
		}
	} );

	return { xfn, other };
}

/**
 * Combine XFN and other rel values
 *
 * @param {string[]} xfnValues   XFN tokens.
 * @param {string[]} otherValues Non-XFN tokens, kept first.
 * @return {string} Space-separated rel value.
 */
export function combineRelValues( xfnValues, otherValues ) {
	const allValues = [ ...otherValues, ...xfnValues ].filter( Boolean );
	return [ ...new Set( allValues ) ].join( ' ' );
}

/**
 * Whether the block type declares its own `rel` attribute.
 *
 * Navigation Link, Navigation Submenu, Button and Image declare one and
 * render it on their anchor. The block's attributes can't answer this:
 * the editor leaves unset attributes out, so a new Navigation Link has no
 * `rel` key until something writes one.
 *
 * @param {Object} blockType Registered block type.
 * @return {boolean} True when rel belongs in the `rel` attribute.
 */
const hasRelAttribute = ( blockType ) => Boolean( blockType?.attributes?.rel );

/**
 * Read a block's current rel value.
 *
 * @param {Object} blockType  Registered block type.
 * @param {Object} attributes Block attributes.
 * @return {string} The block's rel value.
 */
export function getBlockRel( blockType, attributes ) {
	return (
		( hasRelAttribute( blockType )
			? attributes.rel
			: attributes.metadata?.rel ) || ''
	);
}

/**
 * Build the attribute update that sets a block's XFN tokens and keeps its
 * other rel tokens.
 *
 * @param {Object}   blockType  Registered block type.
 * @param {Object}   attributes Block attributes.
 * @param {string[]} xfnValues  XFN tokens the block should carry.
 * @return {Object} Attributes to pass to setAttributes().
 */
export function setBlockXfn( blockType, attributes, xfnValues ) {
	const { other } = parseRelAttribute( getBlockRel( blockType, attributes ) );
	const rel = combineRelValues( xfnValues, other ) || undefined;

	return hasRelAttribute( blockType )
		? { rel }
		: { metadata: { ...attributes.metadata, rel } };
}
