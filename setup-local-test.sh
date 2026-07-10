#!/bin/bash

# XFN Link Extension - Local WordPress Testing Setup
# This script sets up a test environment with Query Monitor, Debug Bar, and Plugin Check

set -e  # Exit on error

echo "=========================================="
echo "XFN Link Extension - Test Setup"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# Get the directory where this script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PLUGIN_ZIP="$SCRIPT_DIR/link-extension-for-xfn.zip"

# Check if plugin ZIP exists
if [ ! -f "$PLUGIN_ZIP" ]; then
    print_error "Plugin ZIP not found: $PLUGIN_ZIP"
    echo "Please run this script from the plugin directory containing link-extension-for-xfn.zip"
    exit 1
fi

print_success "Found plugin ZIP: $PLUGIN_ZIP"

# Check for Local sites directory
LOCAL_SITES_DIR="$HOME/Local Sites"
if [ ! -d "$LOCAL_SITES_DIR" ]; then
    print_error "Local Sites directory not found at: $LOCAL_SITES_DIR"
    echo "Please create a site in Local by Flywheel first, then run this script."
    exit 1
fi

# List available sites
echo ""
echo "Available Local sites:"
echo "----------------------"
SITES=()
SITE_PATHS=()
i=1
for site_dir in "$LOCAL_SITES_DIR"/*; do
    if [ -d "$site_dir" ]; then
        site_name=$(basename "$site_dir")
        SITES+=("$site_name")
        SITE_PATHS+=("$site_dir")
        echo "$i) $site_name"
        ((i++))
    fi
done

if [ ${#SITES[@]} -eq 0 ]; then
    print_error "No Local sites found!"
    echo ""
    echo "Please create a new site in Local by Flywheel:"
    echo "1. Open Local app"
    echo "2. Click 'Create a new site' or '+'"
    echo "3. Name it 'xfn-test' (or any name)"
    echo "4. Choose preferred environment (Preferred recommended)"
    echo "5. Set WordPress username and password"
    echo "6. After creation, run this script again"
    exit 1
fi

# Ask user to select a site or create new one
echo ""
echo "Options:"
echo "  1-${#SITES[@]}: Use existing site"
echo "  N: I need to create a new site first"
echo ""
read -p "Enter your choice: " choice

if [[ "$choice" == "N" ]] || [[ "$choice" == "n" ]]; then
    echo ""
    print_info "Please create a new site in Local by Flywheel:"
    echo "1. Open Local app"
    echo "2. Click 'Create a new site' or '+'"
    echo "3. Suggested name: 'xfn-test'"
    echo "4. Choose 'Preferred' environment"
    echo "5. WordPress username: admin"
    echo "6. WordPress password: (your choice, remember it!)"
    echo "7. After creation, run this script again"
    exit 0
fi

# Validate choice
if ! [[ "$choice" =~ ^[0-9]+$ ]] || [ "$choice" -lt 1 ] || [ "$choice" -gt ${#SITES[@]} ]; then
    print_error "Invalid choice"
    exit 1
fi

# Get selected site
SITE_INDEX=$((choice - 1))
SITE_NAME="${SITES[$SITE_INDEX]}"
SITE_PATH="${SITE_PATHS[$SITE_INDEX]}"

print_success "Selected site: $SITE_NAME"
echo ""

# Find WordPress installation path
WP_PATH="$SITE_PATH/app/public"
if [ ! -d "$WP_PATH" ]; then
    print_error "WordPress installation not found at: $WP_PATH"
    exit 1
fi

print_success "WordPress path: $WP_PATH"

# Check if WP-CLI works with this site
cd "$WP_PATH"
if ! wp core version &>/dev/null; then
    print_error "Cannot access WordPress installation with WP-CLI"
    echo "Make sure the site is running in Local"
    exit 1
fi

WP_VERSION=$(wp core version)
print_success "WordPress version: $WP_VERSION"

# Enable WordPress debugging
echo ""
print_info "Enabling WordPress debug mode..."
wp config set WP_DEBUG true --raw --type=constant --quiet
wp config set WP_DEBUG_LOG true --raw --type=constant --quiet
wp config set WP_DEBUG_DISPLAY false --raw --type=constant --quiet
wp config set SCRIPT_DEBUG true --raw --type=constant --quiet
print_success "Debug mode enabled"

# Install and activate Query Monitor
echo ""
print_info "Installing Query Monitor..."
if wp plugin is-installed query-monitor; then
    print_info "Query Monitor already installed"
    wp plugin activate query-monitor --quiet
else
    wp plugin install query-monitor --activate --quiet
fi
print_success "Query Monitor activated"

# Install and activate Debug Bar
echo ""
print_info "Installing Debug Bar..."
if wp plugin is-installed debug-bar; then
    print_info "Debug Bar already installed"
    wp plugin activate debug-bar --quiet
else
    wp plugin install debug-bar --activate --quiet
fi
print_success "Debug Bar activated"

# Install and activate Plugin Check
echo ""
print_info "Installing Plugin Check..."
if wp plugin is-installed plugin-check; then
    print_info "Plugin Check already installed"
    wp plugin activate plugin-check --quiet
else
    wp plugin install plugin-check --activate --quiet
fi
print_success "Plugin Check activated"

# Install XFN Link Extension
echo ""
print_info "Installing XFN Link Extension..."
PLUGINS_DIR="$WP_PATH/wp-content/plugins"
XFN_PLUGIN_DIR="$PLUGINS_DIR/link-extension-for-xfn"

# Remove old version if exists
if [ -d "$XFN_PLUGIN_DIR" ]; then
    print_info "Removing old version..."
    rm -rf "$XFN_PLUGIN_DIR"
fi

# Unzip plugin
print_info "Extracting plugin..."
unzip -q "$PLUGIN_ZIP" -d "$PLUGINS_DIR/"

# Check if extraction was successful
if [ ! -f "$XFN_PLUGIN_DIR/link-extension-for-xfn.php" ]; then
    print_error "Plugin extraction failed"
    print_info "Trying alternative extraction..."
    # Sometimes zip creates a nested directory
    if [ -d "$PLUGINS_DIR/link-extension-for-xfn/link-extension-for-xfn" ]; then
        mv "$PLUGINS_DIR/link-extension-for-xfn/link-extension-for-xfn/"* "$PLUGINS_DIR/link-extension-for-xfn/"
        rmdir "$PLUGINS_DIR/link-extension-for-xfn/link-extension-for-xfn"
    fi
fi

# Activate plugin
if wp plugin activate link-extension-for-xfn --quiet; then
    print_success "XFN Link Extension activated"
else
    print_error "Failed to activate XFN Link Extension"
    echo "Check for activation errors in Query Monitor"
fi

# Install Twenty Twenty-Four theme (if not already)
echo ""
print_info "Ensuring Twenty Twenty-Four theme is available..."
if ! wp theme is-installed twentytwentyfour; then
    wp theme install twentytwentyfour --quiet
    print_success "Twenty Twenty-Four installed"
else
    print_info "Twenty Twenty-Four already installed"
fi

# Get site URL
SITE_URL=$(wp option get siteurl)
ADMIN_URL="$SITE_URL/wp-admin"

# Summary
echo ""
echo "=========================================="
echo "✓ Setup Complete!"
echo "=========================================="
echo ""
echo "Site: $SITE_NAME"
echo "WordPress: $WP_VERSION"
echo ""
echo "Installed Plugins:"
echo "  ✓ Query Monitor (for error detection)"
echo "  ✓ Debug Bar (for debugging)"
echo "  ✓ Plugin Check (for WordPress.org compliance)"
echo "  ✓ XFN Link Extension (your plugin)"
echo ""
echo "Debug Mode: ENABLED"
echo "  - Errors logged to: wp-content/debug.log"
echo "  - Check Query Monitor toolbar for errors"
echo ""
echo "URLs:"
echo "  Admin: $ADMIN_URL"
echo "  Site:  $SITE_URL"
echo ""
echo "Next Steps:"
echo "1. Open Local and ensure '$SITE_NAME' site is running"
echo "2. Visit: $ADMIN_URL"
echo "3. Look for Query Monitor in the admin toolbar (top)"
echo "4. Create a new post to test XFN plugin"
echo "5. Check Query Monitor for any errors (red/orange badges)"
echo ""
echo "Testing Checklist:"
echo "  □ Create new post"
echo "  □ Add Button block"
echo "  □ Click XFN toolbar button"
echo "  □ Test relationship selections"
echo "  □ Check Inspector Controls panel"
echo "  □ Test inline links with XFN"
echo "  □ Save and publish post"
echo "  □ View published post"
echo "  □ Verify rel attributes in HTML"
echo "  □ Check Query Monitor - should show ZERO errors"
echo ""
echo "Debug Log Location:"
echo "  $WP_PATH/wp-content/debug.log"
echo ""
echo "To view debug log:"
echo "  tail -f \"$WP_PATH/wp-content/debug.log\""
echo ""
print_success "Ready for testing!"
