/**
 * XFN tooltip Interactivity API view module.
 *
 * Shows/hides relationship tooltips on XFN-annotated links.
 *
 * @package LinkExtensionForXFN
 */

import './tooltip.scss';
import { store, getContext, getElement } from '@wordpress/interactivity';

// Open tooltip contexts, keyed to the anchor that opened them.
const openTooltips = new Map();

store( 'xfn-links', {
	actions: {
		showTooltip() {
			const context = getContext();
			context.isOpen = true;
			openTooltips.set( context, getElement().ref );
		},
		hideTooltip() {
			const context = getContext();
			context.isOpen = false;
			openTooltips.delete( context );
		},
	},
} );

// A hover tooltip must close on Escape wherever focus is (WCAG 2.2 SC
// 1.4.13). Core's Navigation overlay stops Escape before it bubbles to the
// document, so this listens in the capture phase. It stops only an Escape
// that closed a tooltip; the next Escape reaches the overlay.
document.addEventListener(
	'keydown',
	( event ) => {
		if ( event.key !== 'Escape' || ! openTooltips.size ) {
			return;
		}
		// A route swap can remove a hovered anchor without mouseleave; a
		// tooltip that's no longer on the page mustn't swallow Escape.
		let closedVisible = false;
		openTooltips.forEach( ( anchor, context ) => {
			context.isOpen = false;
			closedVisible = closedVisible || Boolean( anchor?.isConnected );
		} );
		openTooltips.clear();
		if ( closedVisible ) {
			event.stopPropagation();
		}
	},
	true
);
