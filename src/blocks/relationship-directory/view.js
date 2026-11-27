/**
 * Relationship Directory Interactivity API view module.
 *
 * Handles search filtering and relationship type filtering on the frontend.
 * Every value comes from the nearest directory's context, so each directory
 * on a page filters only its own links.
 *
 * @package LinkExtensionForXFN
 */

import { store, getContext } from "@wordpress/interactivity";

function matchesLink(link, { searchTerm, activeFilter }) {
  if (searchTerm) {
    const haystack = [link.url, link.postTitle, ...link.rels]
      .join(" ")
      .toLowerCase();
    if (!haystack.includes(searchTerm)) {
      return false;
    }
  }

  return !activeFilter || link.rels.includes(activeFilter);
}

const { state } = store("xfn-directory", {
  state: {
    get isHidden() {
      const context = getContext();
      return !matchesLink(context.links[context.linkIndex], context);
    },
    get isActiveFilter() {
      const context = getContext();
      return context.activeFilter === context.filterRel;
    },
    get resultsText() {
      const context = getContext();
      const count = context.links.filter((link) =>
        matchesLink(link, context),
      ).length;

      const i18n = state.i18n || {};
      const template =
        count === 1
          ? i18n.resultsTextSingular || "%d relationship shown"
          : i18n.resultsTextPlural || "%d relationships shown";

      return template.replace("%d", count);
    },
  },
  actions: {
    setSearchTerm(event) {
      const context = getContext();
      context.searchTerm = event.target.value.toLowerCase();
    },
    toggleFilter() {
      const context = getContext();
      context.activeFilter =
        context.activeFilter === context.filterRel ? "" : context.filterRel;
    },
    clearFilter() {
      const context = getContext();
      context.activeFilter = "";
    },
  },
});
