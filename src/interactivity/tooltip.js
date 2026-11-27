/**
 * XFN tooltip Interactivity API view module.
 *
 * Shows/hides relationship tooltips on XFN-annotated links.
 *
 * @package LinkExtensionForXFN
 */

import "./tooltip.scss";
import { store, getContext, withSyncEvent } from "@wordpress/interactivity";

store("xfn-links", {
  actions: {
    showTooltip() {
      const context = getContext();
      context.isOpen = true;
    },
    hideTooltip() {
      const context = getContext();
      context.isOpen = false;
    },
    // Escape closes an open tooltip and stops there, so a surrounding
    // Navigation overlay stays open; the next Escape reaches the overlay.
    handleKeydown: withSyncEvent((event) => {
      const context = getContext();
      if (event.key === "Escape" && context.isOpen) {
        event.stopPropagation();
        context.isOpen = false;
      }
    }),
  },
});
