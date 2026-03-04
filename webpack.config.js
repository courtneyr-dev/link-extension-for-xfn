/**
 * Custom webpack configuration.
 *
 * Two-config array:
 * 1. Default @wordpress/scripts config — builds block editor scripts (src/index.js).
 * 2. Module config — builds Interactivity API view modules as ES modules.
 *
 * The module config is necessary because @wordpress/interactivity is only
 * available as a script module in WordPress, not a regular script.
 */

const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const path = require("path");

const moduleConfig = {
  ...defaultConfig,
  entry: {
    "interactivity/tooltip": path.resolve(
      __dirname,
      "src/interactivity/tooltip.js",
    ),
    "blocks/relationship-directory/view": path.resolve(
      __dirname,
      "src/blocks/relationship-directory/view.js",
    ),
  },
  output: {
    path: path.resolve(__dirname, "build"),
    filename: "[name].js",
    module: true,
    chunkFormat: "module",
    library: { type: "module" },
    clean: false,
  },
  experiments: {
    ...(defaultConfig.experiments || {}),
    outputModule: true,
  },
  externalsType: "module",
};

module.exports = [defaultConfig, moduleConfig];
