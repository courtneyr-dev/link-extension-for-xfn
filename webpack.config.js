/**
 * Custom webpack configuration.
 *
 * Uses @wordpress/scripts' native script-modules support
 * (WP_EXPERIMENTAL_MODULES) instead of a hand-rolled second config.
 * The default export then becomes a two-config array:
 *
 * 1. Script config — block editor scripts (src/index.js, block index.js).
 * 2. Module config — Interactivity API view modules. block.json
 *    `viewScriptModule` entries (relationship-directory/view.js) are
 *    picked up automatically; the tooltip module is not block-bound
 *    (PHP registers it with wp_register_script_module), so it is added
 *    as an explicit entry.
 *
 * History: the previous version spread the script config into a second
 * module config by hand. The shared plugin instances put the script
 * compile into module mode, which failed every @wordpress/* import and
 * — because a failed compile still cleans its output — wiped build/.
 */

process.env.WP_EXPERIMENTAL_MODULES = "true";

const path = require("path");
const [scriptConfig, moduleConfig] = require("@wordpress/scripts/config/webpack.config");

module.exports = [
  scriptConfig,
  {
    ...moduleConfig,
    entry: async () => ({
      ...(await moduleConfig.entry()),
      "interactivity/tooltip": path.resolve(
        __dirname,
        "src/interactivity/tooltip.js",
      ),
    }),
  },
];
