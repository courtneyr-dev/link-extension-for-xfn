/**
 * XFN tooltip Interactivity API view module.
 *
 * Shows/hides relationship tooltips on XFN-annotated links.
 *
 * @package LinkExtensionForXFN
 */

import "./tooltip.scss";
import { store, getContext } from "@wordpress/interactivity";

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
    handleKeydown(event) {
      if (event.key === "Escape") {
        const context = getContext();
        context.isOpen = false;
      }
    },
  },
});
