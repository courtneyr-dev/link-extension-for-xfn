#!/bin/bash
# Deploy Link Extension for XFN to WordPress.org SVN
# This script automates the WordPress.org plugin deployment process

set -e # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Plugin information
PLUGIN_SLUG="link-extension-for-xfn"
PLUGIN_VERSION="1.0.1"
SVN_URL="https://plugins.svn.wordpress.org/${PLUGIN_SLUG}/"
SVN_DIR="/tmp/${PLUGIN_SLUG}-svn"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}WordPress.org Plugin Deployment${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""
echo -e "Plugin: ${GREEN}${PLUGIN_SLUG}${NC}"
echo -e "Version: ${GREEN}${PLUGIN_VERSION}${NC}"
echo ""

# Check if user has WordPress.org credentials
echo -e "${YELLOW}⚠️  You will need your WordPress.org username and password${NC}"
echo -e "${YELLOW}   Make sure you have commit access to the plugin repository${NC}"
echo ""
read -p "Press Enter to continue or Ctrl+C to cancel..."
echo ""

# Clean up any existing SVN directory
if [ -d "$SVN_DIR" ]; then
    echo -e "${YELLOW}Removing existing SVN directory...${NC}"
    rm -rf "$SVN_DIR"
fi

# Checkout SVN repository
echo -e "${BLUE}Step 1: Checking out SVN repository...${NC}"
svn co "$SVN_URL" "$SVN_DIR"
echo -e "${GREEN}✓ SVN repository checked out${NC}"
echo ""

# Navigate to SVN directory
cd "$SVN_DIR"

# Copy plugin files to trunk (excluding dev files)
echo -e "${BLUE}Step 2: Copying plugin files to trunk...${NC}"

# Remove old trunk files (except .svn)
find trunk -mindepth 1 -maxdepth 1 ! -name '.svn' -exec rm -rf {} \;

# Copy files using rsync with .distignore
rsync -rc \
    --exclude-from="$OLDPWD/.distignore" \
    --exclude=".git" \
    --exclude=".github" \
    --exclude="deploy-to-wordpress-org.sh" \
    --delete \
    "$OLDPWD/" trunk/

echo -e "${GREEN}✓ Plugin files copied to trunk${NC}"
echo ""

# Copy assets to assets directory
echo -e "${BLUE}Step 3: Copying assets (screenshots, banners, icons)...${NC}"

# Create assets directory if it doesn't exist
if [ ! -d "assets" ]; then
    mkdir assets
    svn add assets
fi

# Copy assets from .wordpress-org directory
if [ -d "$OLDPWD/.wordpress-org" ]; then
    rsync -rc --delete "$OLDPWD/.wordpress-org/" assets/
    echo -e "${GREEN}✓ Assets copied${NC}"
else
    echo -e "${YELLOW}⚠️  No .wordpress-org directory found, skipping assets${NC}"
fi
echo ""

# Check SVN status
echo -e "${BLUE}Step 4: Checking what changed...${NC}"
svn status
echo ""

# Add any new files
echo -e "${BLUE}Step 5: Adding new files to SVN...${NC}"
svn status | grep '^?' | awk '{print $2}' | xargs -I{} svn add {}@
echo -e "${GREEN}✓ New files added${NC}"
echo ""

# Remove any deleted files
echo -e "${BLUE}Step 6: Removing deleted files from SVN...${NC}"
svn status | grep '^!' | awk '{print $2}' | xargs -I{} svn rm {}@
echo -e "${GREEN}✓ Deleted files removed${NC}"
echo ""

# Show final status
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Final Status Before Commit${NC}"
echo -e "${BLUE}========================================${NC}"
svn status
echo ""

# Review changes
echo -e "${YELLOW}Review the changes above carefully.${NC}"
echo -e "${YELLOW}This will commit to WordPress.org SVN trunk.${NC}"
echo ""
read -p "Do you want to commit these changes? (yes/no): " -r
echo ""

if [[ ! $REPLY =~ ^[Yy]es$ ]]; then
    echo -e "${RED}✗ Deployment cancelled${NC}"
    echo -e "${YELLOW}SVN working copy is at: ${SVN_DIR}${NC}"
    exit 1
fi

# Commit to trunk
echo -e "${BLUE}Step 7: Committing to trunk...${NC}"
svn ci -m "Deploy version ${PLUGIN_VERSION} to trunk

- Fix plugin naming: now consistently uses 'Link Extension for XFN'
- Fix installation directory path (xfn-link-extension → link-extension-for-xfn)
- Fix Settings menu name (XFN Link Extension → Link Extension for XFN)
- Change Contributors to Developers in plugin header
- Update all documentation and user-facing strings"

echo -e "${GREEN}✓ Committed to trunk${NC}"
echo ""

# Create tag
echo -e "${BLUE}Step 8: Creating tag for version ${PLUGIN_VERSION}...${NC}"
svn cp trunk "tags/${PLUGIN_VERSION}"
svn ci "tags/${PLUGIN_VERSION}" -m "Tagging version ${PLUGIN_VERSION}"
echo -e "${GREEN}✓ Tag created${NC}"
echo ""

# Success message
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✓ Deployment Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Plugin deployed to WordPress.org"
echo -e "Trunk: ${BLUE}https://plugins.svn.wordpress.org/${PLUGIN_SLUG}/trunk/${NC}"
echo -e "Tag: ${BLUE}https://plugins.svn.wordpress.org/${PLUGIN_SLUG}/tags/${PLUGIN_VERSION}/${NC}"
echo ""
echo -e "${YELLOW}The plugin should appear on WordPress.org within a few minutes.${NC}"
echo -e "${YELLOW}Check: https://wordpress.org/plugins/${PLUGIN_SLUG}/${NC}"
echo ""

# Clean up
read -p "Remove SVN working directory? (yes/no): " -r
if [[ $REPLY =~ ^[Yy]es$ ]]; then
    cd "$OLDPWD"
    rm -rf "$SVN_DIR"
    echo -e "${GREEN}✓ SVN directory cleaned up${NC}"
fi

echo ""
echo -e "${GREEN}All done! 🎉${NC}"
