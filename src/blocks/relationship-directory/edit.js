/**
 * Editor component for the Relationship Directory block.
 *
 * @package LinkExtensionForXFN
 */

import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, ToggleControl, RangeControl } from "@wordpress/components";

export default function Edit({ attributes, setAttributes }) {
  const { showSearch, showFilters, limit } = attributes;
  const blockProps = useBlockProps({ className: "xfn-directory" });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Directory Settings", "link-extension-for-xfn")}>
          <ToggleControl
            label={__("Show search", "link-extension-for-xfn")}
            checked={showSearch}
            onChange={(val) => setAttributes({ showSearch: val })}
          />
          <ToggleControl
            label={__("Show filters", "link-extension-for-xfn")}
            checked={showFilters}
            onChange={(val) => setAttributes({ showFilters: val })}
          />
          <RangeControl
            label={__("Maximum links", "link-extension-for-xfn")}
            value={limit}
            onChange={(val) => setAttributes({ limit: val })}
            min={5}
            max={200}
            step={5}
          />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <div className="xfn-directory__placeholder">
          <p>
            {__(
              "Relationship Directory — displays an interactive, filterable list of all XFN relationships on the frontend.",
              "link-extension-for-xfn",
            )}
          </p>
        </div>
      </div>
    </>
  );
}
