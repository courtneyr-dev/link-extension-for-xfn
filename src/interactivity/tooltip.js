/**
 * XFN tooltip Interactivity API view module.
 *
 * Shows/hides relationship tooltips on XFN-annotated links.
 *
 * @package LinkExtensionForXFN
 */

import './tooltip.scss';
import { store, getContext } from '@wordpress/interactivity';

const openTooltips = new Set();

store( 'xfn-links', {
	actions: {
		showTooltip() {
			const context = getContext();
			context.isOpen = true;
			openTooltips.add( context );
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
		openTooltips.forEach( ( context ) => {
			context.isOpen = false;
		} );
		openTooltips.clear();
		event.stopPropagation();
	},
	true
);
