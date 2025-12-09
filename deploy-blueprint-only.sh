#!/bin/bash
# Quick deploy - just add/update the blueprint.json file

set -e

echo "======================================"
echo "Deploy Blueprint to WordPress.org"
echo "======================================"
echo ""

# Plugin information
PLUGIN_SLUG="link-extension-for-xfn"
SVN_URL="https://plugins.svn.wordpress.org/${PLUGIN_SLUG}/"
SVN_DIR="/tmp/${PLUGIN_SLUG}-blueprint-deploy"

# Clean up any existing directory
if [ -d "$SVN_DIR" ]; then
    echo "Removing existing temp directory..."
    rm -rf "$SVN_DIR"
fi

# Checkout just the assets directory (faster)
echo "Checking out SVN repository..."
svn co "$SVN_URL" "$SVN_DIR" --depth empty
cd "$SVN_DIR"
svn update assets --set-depth infinity

echo "✓ SVN repository checked out"
echo ""

# Create blueprints directory if it doesn't exist
mkdir -p assets/blueprints

# Copy the blueprint
echo "Copying blueprint.json..."
cp "$OLDPWD/assets/blueprints/blueprint.json" assets/blueprints/

echo "✓ Blueprint copied"
echo ""

# Check if file needs to be added to SVN
if svn status assets/blueprints/blueprint.json | grep -q '?'; then
    echo "Adding blueprint.json to SVN..."
    svn add assets/blueprints/blueprint.json
    echo "✓ File added to SVN"
else
    echo "✓ File already tracked by SVN (updating)"
fi

echo ""
echo "SVN Status:"
svn status

echo ""
echo "======================================"
echo "Ready to Commit"
echo "======================================"
echo ""
read -p "Commit to WordPress.org? (yes/no): " -r
echo ""

if [[ ! $REPLY =~ ^[Yy]es$ ]]; then
    echo "✗ Cancelled"
    echo "SVN working copy is at: $SVN_DIR"
    exit 1
fi

# Commit
echo "Committing to WordPress.org..."
svn ci -m "Add Playground blueprint for live preview demo"

echo ""
echo "======================================"
echo "✓ Blueprint Deployed!"
echo "======================================"
echo ""
echo "Next steps:"
echo "1. Go to: https://wordpress.org/plugins/$PLUGIN_SLUG/advanced/"
echo "2. Toggle 'Enable Live Preview' to ON"
echo "3. Save changes"
echo ""
echo "The preview button should appear within a few minutes!"
