/**
 * Relationship Directory Interactivity API view module.
 *
 * Handles search filtering and relationship type filtering on the frontend.
 *
 * @package LinkExtensionForXFN
 */

import { store, getContext } from "@wordpress/interactivity";

const { state } = store("xfn-directory", {
  state: {
    get links() {
      return state.links || [];
    },
    get allRels() {
      return state.allRels || [];
    },
  },
  actions: {
    setSearchTerm(event) {
      const context = getContext();
      context.searchTerm = event.target.value.toLowerCase();
    },
    toggleFilter(event) {
      const context = getContext();
      const rel = event.target.dataset.rel;
      context.activeFilter = context.activeFilter === rel ? "" : rel;
    },
    clearFilter() {
      const context = getContext();
      context.activeFilter = "";
    },
  },
});

/**
 * Register derived state getters for visibility and active filter status.
 *
 * WordPress Interactivity API uses `state.propertyName` in directives,
 * so we create dynamic getters for each link and filter.
 */
function registerDerivedState() {
  const links = state.links || [];
  const allRels = state.allRels || [];

  // Per-link hidden state.
  links.forEach((link) => {
    const key = `isHidden_${link.id}`;
    if (!(key in state)) {
      Object.defineProperty(state, key, {
        get() {
          const context = getContext();
          const search = context.searchTerm || "";
          const filter = context.activeFilter || "";

          if (search) {
            const haystack = [link.url, link.postTitle, ...link.rels]
              .join(" ")
              .toLowerCase();
            if (!haystack.includes(search)) {
              return true;
            }
          }

          if (filter && !link.rels.includes(filter)) {
            return true;
          }

          return false;
        },
        enumerable: true,
        configurable: true,
      });
    }
  });

  // Per-filter active state.
  allRels.forEach((rel) => {
    const key = `isActiveFilter_${rel}`;
    if (!(key in state)) {
      Object.defineProperty(state, key, {
        get() {
          const context = getContext();
          return context.activeFilter === rel;
        },
        enumerable: true,
        configurable: true,
      });
    }
  });
}

registerDerivedState();
