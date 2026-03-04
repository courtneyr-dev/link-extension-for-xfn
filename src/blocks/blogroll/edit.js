/**
 * Editor component for the Blogroll block.
 *
 * @package LinkExtensionForXFN
 */

import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import {
  PanelBody,
  SelectControl,
  RangeControl,
  ToggleControl,
} from "@wordpress/components";

export default function Edit({ attributes, setAttributes }) {
  const { groupBy, limit, showRelationships } = attributes;
  const blockProps = useBlockProps({ className: "xfn-blogroll" });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Blogroll Settings", "link-extension-for-xfn")}>
          <SelectControl
            label={__("Group by", "link-extension-for-xfn")}
            value={groupBy}
            options={[
              {
                label: __("Relationship", "link-extension-for-xfn"),
                value: "relationship",
              },
              {
                label: __("Domain", "link-extension-for-xfn"),
                value: "domain",
              },
            ]}
            onChange={(val) => setAttributes({ groupBy: val })}
          />
          <RangeControl
            label={__("Maximum links", "link-extension-for-xfn")}
            value={limit}
            onChange={(val) => setAttributes({ limit: val })}
            min={5}
            max={200}
            step={5}
          />
          <ToggleControl
            label={__("Show relationship pills", "link-extension-for-xfn")}
            checked={showRelationships}
            onChange={(val) => setAttributes({ showRelationships: val })}
          />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <div className="xfn-blogroll__placeholder">
          <p>
            {__(
              "XFN Blogroll — displays linked sites grouped by relationship or domain on the frontend.",
              "link-extension-for-xfn",
            )}
          </p>
        </div>
      </div>
    </>
  );
}
