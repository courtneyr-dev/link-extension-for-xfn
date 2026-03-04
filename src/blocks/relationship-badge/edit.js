/**
 * Editor component for the Relationship Badge block.
 *
 * @package LinkExtensionForXFN
 */

import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, TextControl, ToggleControl } from "@wordpress/components";

export default function Edit({ attributes, setAttributes }) {
  const { url, showUrl } = attributes;
  const blockProps = useBlockProps({ className: "xfn-relationship-badge" });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Badge Settings", "link-extension-for-xfn")}>
          <TextControl
            label={__("URL", "link-extension-for-xfn")}
            value={url}
            onChange={(val) => setAttributes({ url: val })}
            placeholder="https://example.com"
            type="url"
          />
          <ToggleControl
            label={__("Show URL", "link-extension-for-xfn")}
            checked={showUrl}
            onChange={(val) => setAttributes({ showUrl: val })}
          />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <div className="xfn-relationship-badge__placeholder">
          <p>
            {url
              ? url
              : __(
                  "Relationship Badge — enter a URL in the block settings to display its XFN relationships.",
                  "link-extension-for-xfn",
                )}
          </p>
        </div>
      </div>
    </>
  );
}
