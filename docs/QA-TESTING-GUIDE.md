# Link Extension for XFN - QA Testing Guide

## Table of Contents

1. [Testing Overview](#testing-overview)
2. [Test Environment Setup](#test-environment-setup)
3. [Pre-Testing Checklist](#pre-testing-checklist)
4. [Functional Testing](#functional-testing)
5. [Interface Testing](#interface-testing)
6. [Compatibility Testing](#compatibility-testing)
7. [Accessibility Testing](#accessibility-testing)
8. [Performance Testing](#performance-testing)
9. [Security Testing](#security-testing)
10. [Regression Testing](#regression-testing)
11. [Test Case Templates](#test-case-templates)
12. [Bug Reporting](#bug-reporting)

---

## Testing Overview

### Testing Goals

- Verify all XFN functionality works as designed
- Ensure compatibility across WordPress versions and browsers
- Validate accessibility compliance (WCAG 2.2 AA)
- Confirm performance meets standards
- Identify and document any bugs or issues

### Test Scope

**In Scope**:
- XFN relationship selection and storage
- All three interface locations (Toolbar, Inspector, Advanced)
- All supported blocks (Button, Navigation, Paragraph, List, etc.)
- Keyboard navigation and screen reader support
- Cross-browser compatibility
- Mobile responsiveness

**Out of Scope**:
- Classic Editor (not supported)
- Non-link blocks
- Third-party page builders (unless Gutenberg-compatible)

### Testing Roles

- **QA Tester**: Executes test cases, reports bugs
- **Developer**: Fixes bugs, provides test builds
- **Accessibility Specialist**: Validates WCAG compliance
- **Product Owner**: Approves test results

---

## Test Environment Setup

### Required Environments

Set up at least 3 test environments:

#### Environment 1: Fresh WordPress Install
- **Purpose**: Clean slate testing
- **Setup**: Fresh WordPress 6.4+ install
- **Theme**: Twenty Twenty-Four
- **Plugins**: Only XFN Link Extension

#### Environment 2: Common Plugin Stack
- **Purpose**: Compatibility testing
- **Setup**: WordPress with common plugins
- **Theme**: Popular theme (Astra, GeneratePress, etc.)
- **Plugins**:
  - XFN Link Extension
  - Yoast SEO or Rank Math
  - WooCommerce (optional)
  - Contact Form 7 or WPForms

#### Environment 3: Edge Cases
- **Purpose**: Stress testing
- **Setup**: WordPress with many plugins
- **Theme**: Custom theme
- **Plugins**: 20+ active plugins including page builders

### Software Requirements

| Software | Version | Notes |
|----------|---------|-------|
| WordPress | 6.4+ | Test on 6.4, 6.5, 6.6, latest |
| PHP | 7.4+ | Test on 7.4, 8.0, 8.1, 8.2 |
| MySQL | 5.7+ or MariaDB 10.3+ | Standard |
| Node.js | 18+ | For development testing |
| npm | 9+ | For development testing |

### Browser Testing Matrix

| Browser | Versions | Operating Systems |
|---------|----------|-------------------|
| Chrome | Latest 2 versions | Windows, macOS, Linux |
| Firefox | Latest 2 versions | Windows, macOS, Linux |
| Safari | Latest 2 versions | macOS, iOS |
| Edge | Latest version | Windows |

### Testing Tools

- **Browser DevTools**: Console, Network, Performance
- **Screen Readers**: NVDA (Windows), JAWS (Windows), VoiceOver (macOS/iOS)
- **Accessibility**: axe DevTools, WAVE browser extension
- **Performance**: Lighthouse, Query Monitor plugin
- **Debug**: WordPress Debug mode, Query Monitor

---

## Pre-Testing Checklist

Before starting tests, verify:

- [ ] Plugin installed and activated successfully
- [ ] WordPress version meets minimum (6.4+)
- [ ] PHP version meets minimum (7.4+)
- [ ] No PHP errors in debug.log
- [ ] No JavaScript console errors
- [ ] Test content prepared (posts, pages, links)
- [ ] Test user accounts created (admin, editor, author)
- [ ] Browsers updated to required versions
- [ ] Testing tools installed and configured
- [ ] Bug tracking system ready
- [ ] Test data backed up

---

## Functional Testing

### Test Suite 1: Basic Functionality

#### Test Case 1.1: Plugin Activation
**Priority**: Critical

**Steps**:
1. Navigate to Plugins → Installed Plugins
2. Find "Link Extension for XFN"
3. Click "Activate"
4. Verify activation message appears

**Expected Result**:
- Plugin activates without errors
- No PHP warnings or errors
- Success message displayed

**Pass/Fail**: ___________

---

#### Test Case 1.2: XFN Button Appears in Toolbar
**Priority**: Critical

**Steps**:
1. Create new post
2. Add a Button block
3. Enter button text and URL
4. Look at block toolbar

**Expected Result**:
- "XFN" button visible in toolbar
- Button has recognizable icon
- Button is clickable

**Pass/Fail**: ___________

---

#### Test Case 1.3: Select Single Friendship Relationship
**Priority**: Critical

**Steps**:
1. Add Button block with link
2. Click XFN button in toolbar
3. Expand XFN section
4. Click "Friend" under Friendship category
5. Click outside popover to save

**Expected Result**:
- Friend button becomes active/selected
- Summary shows [friend] pill
- Count badge shows (1)

**Pass/Fail**: ___________

---

#### Test Case 1.4: Relationship Mutual Exclusivity
**Priority**: High

**Steps**:
1. Add Button block with link
2. Open XFN interface
3. Select "Friend" under Friendship
4. Try to select "Acquaintance" under Friendship

**Expected Result**:
- "Acquaintance" replaces "Friend"
- Only one friendship relationship active at a time
- Count remains at 1

**Pass/Fail**: ___________

---

#### Test Case 1.5: Multiple Compatible Relationships
**Priority**: High

**Steps**:
1. Add Button block with link
2. Open XFN interface
3. Select "Friend" (Friendship)
4. Select "Met" (Physical)
5. Select "Colleague" (Professional)
6. Save

**Expected Result**:
- All three relationships active
- Summary shows three pills
- Count badge shows (3)
- HTML contains `rel="friend met colleague"`

**Pass/Fail**: ___________

---

#### Test Case 1.6: Relationship Persistence
**Priority**: Critical

**Steps**:
1. Add link with XFN relationships
2. Save the post
3. Reload the page
4. Edit the block
5. Check XFN settings

**Expected Result**:
- Selected relationships still active
- Count badge correct
- Pills display correctly
- HTML rel attribute unchanged

**Pass/Fail**: ___________

---

#### Test Case 1.7: Remove Relationships
**Priority**: High

**Steps**:
1. Add link with multiple relationships
2. Open XFN interface
3. Deselect all relationships
4. Save

**Expected Result**:
- All relationships removed
- Count badge shows (0) or disappears
- No pills in summary
- rel attribute empty or contains only non-XFN values

**Pass/Fail**: ___________

---

#### Test Case 1.8: Preserve Non-XFN Rel Values
**Priority**: Critical

**Steps**:
1. Add Button block with link
2. In link settings, add "noopener noreferrer" to rel
3. Open XFN interface
4. Add "Friend" relationship
5. Save and check HTML source

**Expected Result**:
- HTML shows `rel="noopener noreferrer friend"`
- Non-XFN values preserved
- XFN value added to end
- No duplication

**Pass/Fail**: ___________

---

### Test Suite 2: Interface Location Testing

#### Test Case 2.1: Floating Toolbar Interface
**Priority**: Critical

**Test Blocks**: Button, Navigation

**Steps**:
1. Add Button block
2. Add button text and URL
3. Click XFN button in toolbar
4. Verify popover appears
5. Expand XFN section
6. Select relationships
7. Verify summary updates
8. Save

**Expected Result**:
- Popover appears below toolbar
- XFN section expands/collapses
- Relationships selectable
- Summary pills appear
- Count badge updates
- Changes save on close

**Pass/Fail**: ___________

---

#### Test Case 2.2: Inspector Controls Interface
**Priority**: High

**Test Blocks**: Button, Navigation, Paragraph (with link)

**Steps**:
1. Add block with link
2. Select the block
3. Open Block sidebar (right side)
4. Find "XFN Relationships" panel
5. Expand panel
6. Select relationships
7. View summary

**Expected Result**:
- Panel appears in Block tab
- Panel expands when clicked
- Relationships organized by category
- Radio buttons for exclusive choices
- Checkboxes for multiple choices
- Summary section shows active relationships

**Pass/Fail**: ___________

---

#### Test Case 2.3: Link Advanced Panel Interface
**Priority**: High

**Test Blocks**: Paragraph (inline link), List (inline link)

**Steps**:
1. Add Paragraph block
2. Type text and select it
3. Click Link button
4. Enter URL
5. Click "Advanced"
6. Expand XFN section
7. Select relationships
8. Press Enter to save

**Expected Result**:
- Advanced section expands
- XFN section visible
- Section expands/collapses
- Relationships selectable
- Summary updates
- Link saves with relationships

**Pass/Fail**: ___________

---

#### Test Case 2.4: Panel Synchronization (Critical)
**Priority**: Critical

**Test Blocks**: Button, Image (block-level links)

**Purpose**: Verify that Inspector sidebar and floating toolbar panels display the same XFN values

**Steps**:
1. Add a Button block
2. Add URL: "https://example.com"
3. Open Inspector sidebar → XFN Relationships panel
4. Select "Friend" and "Met"
5. Verify values are selected
6. Click the floating toolbar "XFN" button
7. Verify the same values ("Friend" and "Met") appear selected
8. Change to "Colleague" in the floating toolbar
9. Close and reopen Inspector sidebar
10. Verify "Colleague" is now selected there

**Expected Result**:
- Inspector sidebar and floating toolbar show identical XFN values
- Changes in one panel immediately reflect in the other
- Both panels read from the same block attribute source
- No mismatched states between panels

**Pass/Fail**: ___________

---

### Test Suite 3: Block Compatibility

#### Test Case 3.1: Button Block
**Priority**: Critical

**Steps**:
1. Add Button block
2. Add text: "Click Here"
3. Add URL: "https://example.com"
4. Check Inspector sidebar
5. Verify "XFN Relationships" panel is open by default
6. Add XFN relationships (select "Friend")
7. Save and view frontend
8. Inspect HTML to verify rel attribute

**Expected Result**:
- XFN Relationships panel visible and open by default in Inspector sidebar
- XFN controls accessible via toolbar
- Relationships save automatically as you select them
- Frontend HTML shows: `<a ... rel="friend">`
- rel attribute contains selected XFN values

**Pass/Fail**: ___________

---

#### Test Case 3.2: Navigation Block
**Priority**: Critical

**Steps**:
1. Add Navigation block
2. Add navigation item
3. Enter label and URL
4. Select navigation item
5. Add XFN via Inspector or Toolbar
6. Save

**Expected Result**:
- XFN controls accessible
- Relationships save per navigation item
- Multiple items can have different relationships

**Pass/Fail**: ___________

---

#### Test Case 3.3: Paragraph Block (Inline Link)
**Priority**: Critical

**Steps**:
1. Add Paragraph block
2. Type: "Visit my friend's website"
3. Select "friend's website"
4. Add link
5. Use Link Advanced panel to add XFN
6. Add "friend" + "met"
7. Save

**Expected Result**:
- Link Advanced panel accessible
- XFN section works
- Inline link gets relationships
- Other text unaffected

**Pass/Fail**: ___________

---

#### Test Case 3.4: List Block
**Priority**: High

**Steps**:
1. Add List block
2. Create list items with links
3. Add different XFN relationships to each
4. Verify each link independent

**Expected Result**:
- Each list item link can have unique relationships
- No cross-contamination between list items

**Pass/Fail**: ___________

---

#### Test Case 3.5: Cover Block
**Priority**: Medium

**Steps**:
1. Add Cover block with image
2. Add button or link to cover
3. Apply XFN relationships
4. Save

**Expected Result**:
- XFN controls work within Cover block
- Relationships apply correctly

**Pass/Fail**: ___________

---

#### Test Case 3.6: Media & Text Block
**Priority**: Medium

**Steps**:
1. Add Media & Text block
2. Add media and text content
3. Add link in text area
4. Apply XFN relationships

**Expected Result**:
- XFN works in Media & Text text area
- Similar to Paragraph behavior

**Pass/Fail**: ___________

---

### Test Suite 4: All Relationship Types

#### Test Case 4.1: All Friendship Values
**Priority**: High

**Test Each**: contact, acquaintance, friend

**Steps**:
1. Create 3 button blocks
2. Apply each friendship value separately
3. Verify mutual exclusivity
4. Check HTML output

**Expected Result**:
- Each value works independently
- Mutual exclusivity enforced
- HTML correct for each

**Pass/Fail**: ___________

---

#### Test Case 4.2: Physical Relationship (met)
**Priority**: High

**Steps**:
1. Add link
2. Select only "met"
3. Combine "met" with friendship value
4. Combine "met" with professional values
5. Check HTML

**Expected Result**:
- "met" works alone
- "met" combines with all other types
- HTML correct in all cases

**Pass/Fail**: ___________

---

#### Test Case 4.3: Professional Relationships
**Priority**: High

**Test**: co-worker, colleague, both together

**Steps**:
1. Test co-worker alone
2. Test colleague alone
3. Test co-worker + colleague together
4. Combine with other categories

**Expected Result**:
- Each works independently
- Both can be selected together
- Combine properly with other types

**Pass/Fail**: ___________

---

#### Test Case 4.4: Geographical Relationships
**Priority**: High

**Test**: co-resident, neighbor (mutual exclusivity)

**Steps**:
1. Select co-resident
2. Try to select neighbor
3. Verify mutual exclusivity
4. Test with other relationship types

**Expected Result**:
- Mutual exclusivity works
- Only one geographical value at a time
- Combines with other types

**Pass/Fail**: ___________

---

#### Test Case 4.5: Family Relationships
**Priority**: High

**Test**: child, parent, sibling, spouse, kin (all mutually exclusive)

**Steps**:
1. Test each value separately
2. Verify mutual exclusivity between all
3. Combine with other categories

**Expected Result**:
- Each works independently
- Mutual exclusivity enforced across all 5 values
- Combine properly with other types

**Pass/Fail**: ___________

---

#### Test Case 4.6: Romantic Relationships
**Priority**: High

**Test**: muse, crush, date, sweetheart (multiple allowed)

**Steps**:
1. Test each value separately
2. Select multiple together
3. Test all 4 together
4. Combine with other categories

**Expected Result**:
- Each works independently
- Multiple selections allowed
- All 4 can be active simultaneously

**Pass/Fail**: ___________

---

#### Test Case 4.7: Identity Relationship (me)
**Priority**: High

**Steps**:
1. Add link to your own profile
2. Select "me"
3. Combine with other relationships (if applicable)
4. Test on self-links vs. external links

**Expected Result**:
- "me" works as single checkbox
- Can combine with other types if needed
- HTML output correct

**Pass/Fail**: ___________

---

## Interface Testing

### Test Suite 5: UI/UX Testing

#### Test Case 5.1: Collapsible Section Behavior
**Priority**: High

**Steps**:
1. Open XFN interface (any location)
2. Verify section is collapsed by default
3. Click to expand
4. Verify smooth animation
5. Click to collapse
6. Verify smooth animation

**Expected Result**:
- Starts collapsed
- Expands smoothly (animation)
- Content fully visible when expanded
- Collapses smoothly
- Chevron icon rotates appropriately

**Pass/Fail**: ___________

---

#### Test Case 5.2: Count Badge Display
**Priority**: Medium

**Steps**:
1. Open XFN with no relationships
2. Verify count badge shows (0) or is hidden
3. Add 1 relationship
4. Verify badge shows (1)
5. Add 2 more relationships
6. Verify badge shows (3)

**Expected Result**:
- Badge displays correct count
- Updates in real-time
- Visible and readable

**Pass/Fail**: ___________

---

#### Test Case 5.3: Relationship Pills Display
**Priority**: Medium

**Steps**:
1. Add relationships
2. Verify pills appear in summary
3. Check pill styling (color, spacing)
4. Add/remove relationships
5. Verify pills update immediately

**Expected Result**:
- Pills display for each relationship
- Clear, readable text
- Distinct visual style
- Updates dynamically

**Pass/Fail**: ___________

---

#### Test Case 5.4: Button Group Styling
**Priority**: Low

**Steps**:
1. Open XFN interface
2. Examine button groups
3. Select buttons
4. Verify visual feedback
5. Check hover states

**Expected Result**:
- Clear selected/unselected states
- Hover effects present
- Good visual hierarchy
- WordPress component styling

**Pass/Fail**: ___________

---

#### Test Case 5.5: Popover Positioning
**Priority**: Medium

**Steps**:
1. Open Toolbar XFN button near top of screen
2. Verify popover position
3. Open near bottom of screen
4. Verify popover adjusts
5. Test with narrow viewport

**Expected Result**:
- Popover positions intelligently
- Doesn't go off-screen
- Adjusts for viewport constraints
- Readable in all positions

**Pass/Fail**: ___________

---

#### Test Case 5.6: Mobile Responsiveness
**Priority**: High

**Steps**:
1. Test on mobile viewport (375px width)
2. Open XFN interfaces
3. Verify touch targets adequate
4. Test expand/collapse
5. Test relationship selection

**Expected Result**:
- Touch targets minimum 44x44px
- Interface usable on mobile
- Text readable
- No horizontal scrolling needed

**Pass/Fail**: ___________

---

## Compatibility Testing

### Test Suite 6: WordPress Version Compatibility

#### Test Case 6.1: WordPress 6.4
**Priority**: Critical (minimum version)

**Steps**:
1. Install WordPress 6.4
2. Install plugin
3. Run basic functionality tests
4. Check for errors

**Expected Result**:
- Plugin activates
- All features work
- No compatibility warnings

**Pass/Fail**: ___________

---

#### Test Case 6.2: WordPress 6.5
**Priority**: High

[Same steps as 6.1]

**Pass/Fail**: ___________

---

#### Test Case 6.3: WordPress 6.6+
**Priority**: High

[Same steps as 6.1]

**Pass/Fail**: ___________

---

#### Test Case 6.4: WordPress Latest
**Priority**: Critical

[Same steps as 6.1, test with very latest version]

**Pass/Fail**: ___________

---

### Test Suite 7: PHP Version Compatibility

#### Test Case 7.1: PHP 7.4
**Priority**: Critical (minimum version)

**Steps**:
1. Configure server with PHP 7.4
2. Install plugin
3. Test activation and basic features
4. Check PHP logs for errors

**Expected Result**:
- No PHP errors
- All features work
- Performance acceptable

**Pass/Fail**: ___________

---

#### Test Case 7.2: PHP 8.0
**Priority**: High

[Same steps as 7.1]

**Pass/Fail**: ___________

---

#### Test Case 7.3: PHP 8.1
**Priority**: High

[Same steps as 7.1]

**Pass/Fail**: ___________

---

#### Test Case 7.4: PHP 8.2
**Priority**: High

[Same steps as 7.1]

**Pass/Fail**: ___________

---

### Test Suite 8: Theme Compatibility

#### Test Case 8.1: Twenty Twenty-Four
**Priority**: Critical

**Steps**:
1. Activate Twenty Twenty-Four theme
2. Test all XFN interfaces
3. Check styling
4. Verify no conflicts

**Expected Result**:
- Plugin works perfectly
- Styling matches WordPress design
- No theme conflicts

**Pass/Fail**: ___________

---

#### Test Case 8.2: Popular Theme (Astra)
**Priority**: High

[Same steps as 8.1 with Astra theme]

**Pass/Fail**: ___________

---

#### Test Case 8.3: Popular Theme (GeneratePress)
**Priority**: High

[Same steps as 8.1 with GeneratePress theme]

**Pass/Fail**: ___________

---

#### Test Case 8.4: Block Theme
**Priority**: High

**Steps**:
1. Activate any block theme
2. Test in Post Editor
3. Test in Site Editor
4. Verify full functionality

**Expected Result**:
- Works in both editors
- No styling issues
- Full feature parity

**Pass/Fail**: ___________

---

### Test Suite 9: Plugin Compatibility

#### Test Case 9.1: Yoast SEO
**Priority**: High

**Steps**:
1. Activate Yoast SEO
2. Create post with XFN links
3. Verify no conflicts with Yoast's link analysis
4. Check that XFN and nofollow coexist

**Expected Result**:
- No conflicts
- Both plugins work simultaneously
- Rel attributes properly combined

**Pass/Fail**: ___________

---

#### Test Case 9.2: Rank Math SEO
**Priority**: Medium

[Same steps as 9.1 with Rank Math]

**Pass/Fail**: ___________

---

#### Test Case 9.3: WooCommerce
**Priority**: Medium

**Steps**:
1. Activate WooCommerce
2. Create product with links in description
3. Add XFN relationships
4. Test on frontend

**Expected Result**:
- Works in product descriptions
- No conflicts with WooCommerce
- Product pages render correctly

**Pass/Fail**: ___________

---

#### Test Case 9.4: Contact Form 7 / WPForms
**Priority**: Low

**Steps**:
1. Install form plugin
2. Test if XFN appears in form editor (may not apply)
3. Verify no JavaScript conflicts
4. Check page performance

**Expected Result**:
- No conflicts
- Both plugins coexist
- No performance impact

**Pass/Fail**: ___________

---

### Test Suite 10: Browser Compatibility

#### Test Case 10.1: Chrome (Windows)
**Priority**: Critical

**Steps**:
1. Open post editor in Chrome on Windows
2. Test all three XFN interfaces
3. Test all relationship types
4. Check console for errors
5. Test keyboard navigation

**Expected Result**:
- Full functionality
- No console errors
- Smooth performance
- Correct rendering

**Pass/Fail**: ___________

---

#### Test Case 10.2: Chrome (macOS)
**Priority**: High

[Same steps as 10.1 on macOS]

**Pass/Fail**: ___________

---

#### Test Case 10.3: Firefox (Windows)
**Priority**: Critical

[Same steps as 10.1 in Firefox on Windows]

**Pass/Fail**: ___________

---

#### Test Case 10.4: Firefox (macOS)
**Priority**: High

[Same steps as 10.1 in Firefox on macOS]

**Pass/Fail**: ___________

---

#### Test Case 10.5: Safari (macOS)
**Priority**: Critical

[Same steps as 10.1 in Safari on macOS]

**Pass/Fail**: ___________

---

#### Test Case 10.6: Safari (iOS)
**Priority**: High

**Steps**:
1. Open WordPress editor in Safari on iOS
2. Test touch interactions
3. Test XFN interfaces
4. Verify mobile rendering

**Expected Result**:
- Touch targets adequate
- Interface usable on mobile
- Full functionality

**Pass/Fail**: ___________

---

#### Test Case 10.7: Edge (Windows)
**Priority**: Medium

[Same steps as 10.1 in Edge]

**Pass/Fail**: ___________

---

## Accessibility Testing

### Test Suite 11: Keyboard Navigation

#### Test Case 11.1: Tab Navigation
**Priority**: Critical

**Steps**:
1. Open XFN interface
2. Use Tab key to navigate through controls
3. Verify logical tab order
4. Ensure all controls reachable
5. Test Shift+Tab to go backwards

**Expected Result**:
- Tab order logical
- All interactive elements reachable
- No keyboard traps
- Focus visible at all times

**Pass/Fail**: ___________

---

#### Test Case 11.2: Button Activation
**Priority**: Critical

**Steps**:
1. Tab to a relationship button
2. Press Space to activate
3. Press Enter to activate
4. Verify both work

**Expected Result**:
- Space activates button
- Enter activates button
- Visual feedback provided

**Pass/Fail**: ___________

---

#### Test Case 11.3: Collapsible Section Toggle
**Priority**: High

**Steps**:
1. Tab to XFN section toggle
2. Press Space or Enter
3. Verify section expands
4. Press again to collapse

**Expected Result**:
- Activates with Space or Enter
- ARIA attributes update
- Screen reader announces state

**Pass/Fail**: ___________

---

#### Test Case 11.4: Arrow Key Navigation
**Priority**: Medium

**Steps**:
1. Navigate to button group
2. Use arrow keys to move between options
3. Test Up/Down and Left/Right
4. Verify wrapping behavior

**Expected Result**:
- Arrow keys navigate within groups
- Wraps to beginning/end
- Focus visible

**Pass/Fail**: ___________

---

#### Test Case 11.5: Escape Key Behavior
**Priority**: Medium

**Steps**:
1. Open XFN popover
2. Press Escape key
3. Verify popover closes
4. Focus returns appropriately

**Expected Result**:
- Escape closes popover
- Focus returns to trigger
- Changes saved

**Pass/Fail**: ___________

---

### Test Suite 12: Screen Reader Testing

#### Test Case 12.1: NVDA (Windows)
**Priority**: Critical

**Steps**:
1. Enable NVDA screen reader
2. Navigate to XFN interface
3. Tab through controls
4. Listen to announcements
5. Verify all labels read correctly

**Expected Result**:
- All controls announced
- Labels clear and descriptive
- States communicated (checked/unchecked)
- Count badge values read
- Instructions clear

**Pass/Fail**: ___________

---

#### Test Case 12.2: JAWS (Windows)
**Priority**: High

[Same steps as 12.1 with JAWS]

**Pass/Fail**: ___________

---

#### Test Case 12.3: VoiceOver (macOS)
**Priority**: Critical

[Same steps as 12.1 with VoiceOver on macOS]

**Pass/Fail**: ___________

---

#### Test Case 12.4: VoiceOver (iOS)
**Priority**: High

**Steps**:
1. Enable VoiceOver on iOS device
2. Open WordPress editor
3. Navigate to XFN interface
4. Test with touch gestures
5. Verify announcements

**Expected Result**:
- All controls accessible
- Touch targets adequate
- Announcements clear
- Gestures work correctly

**Pass/Fail**: ___________

---

### Test Suite 13: ARIA and Semantic HTML

#### Test Case 13.1: ARIA Labels Present
**Priority**: High

**Steps**:
1. Inspect XFN interface with browser DevTools
2. Check for aria-label attributes
3. Verify aria-labelledby usage
4. Check aria-describedby

**Expected Result**:
- All controls have labels
- Labels are descriptive
- ARIA used appropriately
- No missing labels

**Pass/Fail**: ___________

---

#### Test Case 13.2: ARIA States (Expanded/Collapsed)
**Priority**: High

**Steps**:
1. Inspect collapsible toggle button
2. Check aria-expanded attribute
3. Expand section
4. Verify aria-expanded="true"
5. Collapse section
6. Verify aria-expanded="false"

**Expected Result**:
- aria-expanded present
- Updates when toggled
- Screen readers announce state

**Pass/Fail**: ___________

---

#### Test Case 13.3: Button Roles
**Priority**: Medium

**Steps**:
1. Inspect relationship buttons
2. Verify proper roles
3. Check aria-pressed for toggle buttons
4. Verify button vs. checkbox semantics

**Expected Result**:
- Semantic HTML used
- Roles appropriate
- States communicated via ARIA

**Pass/Fail**: ___________

---

#### Test Case 13.4: Focus Management
**Priority**: High

**Steps**:
1. Open XFN popover
2. Verify focus moves to popover
3. Close popover
4. Verify focus returns to trigger
5. Test with keyboard only

**Expected Result**:
- Focus management correct
- No focus loss
- Logical focus flow

**Pass/Fail**: ___________

---

### Test Suite 14: Visual Accessibility

#### Test Case 14.1: Color Contrast
**Priority**: High

**Steps**:
1. Use axe DevTools or WAVE
2. Check color contrast ratios
3. Verify text meets WCAG AA (4.5:1)
4. Verify UI components meet WCAG AA (3:1)

**Expected Result**:
- All text passes 4.5:1 ratio
- UI components pass 3:1 ratio
- No contrast failures

**Pass/Fail**: ___________

---

#### Test Case 14.2: High Contrast Mode (Windows)
**Priority**: Medium

**Steps**:
1. Enable Windows High Contrast mode
2. Open XFN interface
3. Verify visibility of all controls
4. Check selected/unselected states
5. Test usability

**Expected Result**:
- All controls visible
- States distinguishable
- Fully usable
- No invisible text

**Pass/Fail**: ___________

---

#### Test Case 14.3: Browser Zoom (200%)
**Priority**: High

**Steps**:
1. Set browser zoom to 200%
2. Open XFN interface
3. Verify layout doesn't break
4. Ensure all content readable
5. No horizontal scrolling needed

**Expected Result**:
- Layout adapts to zoom
- Text remains readable
- No overlap or cutoff
- Fully functional

**Pass/Fail**: ___________

---

#### Test Case 14.4: Focus Indicators
**Priority**: Critical

**Steps**:
1. Navigate with keyboard
2. Verify visible focus indicators on all controls
3. Check focus indicator contrast
4. Ensure indicators don't obscure content

**Expected Result**:
- Focus always visible
- Clear, obvious indicators
- Sufficient contrast
- Consistent styling

**Pass/Fail**: ___________

---

## Performance Testing

### Test Suite 15: Load Performance

#### Test Case 15.1: Page Load Time
**Priority**: Medium

**Steps**:
1. Clear cache
2. Open post editor
3. Measure time to interactive
4. Check Network panel
5. Verify assets load quickly

**Expected Result**:
- Plugin assets < 50KB gzipped
- No blocking resources
- Fast load time
- No performance warnings

**Pass/Fail**: ___________

---

#### Test Case 15.2: Editor Performance
**Priority**: High

**Steps**:
1. Create post with 20+ blocks
2. Add XFN relationships to 10 links
3. Monitor typing performance
4. Check for lag or delay
5. Use Performance profiler

**Expected Result**:
- No noticeable lag
- Smooth typing experience
- No performance regression
- CPU usage reasonable

**Pass/Fail**: ___________

---

#### Test Case 15.3: Frontend Performance
**Priority**: High

**Steps**:
1. Create post with XFN links
2. Publish post
3. Load frontend
4. Run Lighthouse audit
5. Check performance score

**Expected Result**:
- No frontend JavaScript loaded
- No performance impact
- Lighthouse score unaffected
- Page speed unchanged

**Pass/Fail**: ___________

---

#### Test Case 15.4: Memory Usage
**Priority**: Medium

**Steps**:
1. Open browser Performance Monitor
2. Open post editor
3. Use XFN features extensively
4. Monitor memory usage
5. Check for memory leaks

**Expected Result**:
- Memory usage stable
- No continuous growth
- No memory leaks
- Garbage collection normal

**Pass/Fail**: ___________

---

### Test Suite 16: Query Monitor Testing

#### Test Case 16.1: Database Queries
**Priority**: High

**Steps**:
1. Install and activate Query Monitor
2. Open post editor
3. Use XFN features
4. Check Query Monitor panel
5. Verify no additional queries

**Expected Result**:
- No database queries from plugin
- Data stored in post content
- No custom tables
- No performance impact

**Pass/Fail**: ___________

---

#### Test Case 16.2: PHP Errors
**Priority**: Critical

**Steps**:
1. Enable Query Monitor
2. Use all plugin features
3. Check PHP Errors tab
4. Verify no warnings or notices

**Expected Result**:
- Zero PHP errors
- Zero PHP warnings
- Zero PHP notices
- Clean execution

**Pass/Fail**: ___________

---

#### Test Case 16.3: HTTP Requests
**Priority**: Medium

**Steps**:
1. Monitor HTTP requests in Query Monitor
2. Use plugin features
3. Verify no external requests
4. Check for efficient asset loading

**Expected Result**:
- No external HTTP requests
- Only local asset requests
- Assets served efficiently
- No unnecessary requests

**Pass/Fail**: ___________

---

## Security Testing

### Test Suite 17: Input Validation

#### Test Case 17.1: XSS Prevention
**Priority**: Critical

**Steps**:
1. Attempt to inject script tags in relationship values
2. Try XSS in link URLs with XFN
3. Check HTML output is escaped
4. Test with various XSS payloads

**Expected Result**:
- All output escaped
- No script execution
- XSS attempts blocked
- Safe HTML output

**Pass/Fail**: ___________

---

#### Test Case 17.2: SQL Injection
**Priority**: Critical

**Steps**:
1. Attempt SQL injection in relationship selection
2. Try SQL in associated link fields
3. Verify no database queries anyway
4. Check sanitization

**Expected Result**:
- No SQL queries from plugin
- Input sanitized
- No vulnerabilities
- Data stored safely in post content

**Pass/Fail**: ___________

---

#### Test Case 17.3: CSRF Protection
**Priority**: High

**Steps**:
1. Check for nonce usage in any AJAX
2. Verify nonce validation
3. Test without nonce
4. Ensure protection present

**Expected Result**:
- Nonces used where needed
- Validation enforced
- CSRF protected
- (Note: Plugin has minimal need for nonces)

**Pass/Fail**: ___________

---

### Test Suite 18: Permission Checks

#### Test Case 18.1: Editor Role
**Priority**: High

**Steps**:
1. Log in as Editor
2. Create post with XFN links
3. Verify full functionality
4. Save and publish

**Expected Result**:
- Full access to XFN features
- Can create and edit
- Can publish

**Pass/Fail**: ___________

---

#### Test Case 18.2: Author Role
**Priority**: High

**Steps**:
1. Log in as Author
2. Create post with XFN links
3. Verify full functionality
4. Save and publish

**Expected Result**:
- Full access to XFN features
- Can create and edit own posts
- Can publish own content

**Pass/Fail**: ___________

---

#### Test Case 18.3: Contributor Role
**Priority**: Medium

**Steps**:
1. Log in as Contributor
2. Create post with XFN links
3. Verify functionality
4. Submit for review

**Expected Result**:
- Can use XFN features
- Can save drafts
- Cannot publish (expected)

**Pass/Fail**: ___________

---

#### Test Case 18.4: Subscriber Role
**Priority**: Low

**Steps**:
1. Log in as Subscriber
2. Verify no access to post editor
3. Confirm XFN not relevant

**Expected Result**:
- No post editor access (expected WordPress behavior)
- Plugin doesn't grant inappropriate permissions

**Pass/Fail**: ___________

---

## Regression Testing

### Test Suite 19: After Bug Fixes

Run this checklist after any bug fix:

- [ ] Original bug is fixed
- [ ] Fix doesn't break existing features
- [ ] Run critical test cases (1.1-1.8)
- [ ] Test related functionality
- [ ] Check for new console errors
- [ ] Verify accessibility not affected
- [ ] Test on multiple browsers
- [ ] Update regression test suite

---

### Test Suite 20: After Feature Additions

Run this checklist after adding new features:

- [ ] New feature works as designed
- [ ] Existing features still work
- [ ] Run full functional test suite
- [ ] Update test documentation
- [ ] Test edge cases
- [ ] Verify backwards compatibility
- [ ] Check performance impact
- [ ] Update user documentation

---

## Test Case Templates

### Template: Basic Feature Test

**Test Case ID**: ___________
**Test Case Name**: ___________
**Priority**: Critical / High / Medium / Low
**Prerequisites**: ___________

**Steps**:
1.
2.
3.

**Expected Result**:
-
-

**Actual Result**:
___________

**Pass/Fail**: ___________
**Tested By**: ___________
**Date**: ___________
**Browser/OS**: ___________
**Notes**: ___________

---

### Template: Browser Compatibility Test

**Test Case ID**: ___________
**Browser**: ___________
**Version**: ___________
**OS**: ___________
**Date**: ___________

**Checklist**:
- [ ] Plugin activates
- [ ] XFN button appears
- [ ] Relationships selectable
- [ ] Changes save
- [ ] No console errors
- [ ] Visual rendering correct
- [ ] Performance acceptable

**Issues Found**: ___________

**Pass/Fail**: ___________

---

### Template: Accessibility Test

**Test Case ID**: ___________
**Feature**: ___________
**Tool Used**: NVDA / JAWS / VoiceOver / axe / WAVE
**Date**: ___________

**Checklist**:
- [ ] Keyboard accessible
- [ ] Screen reader compatible
- [ ] ARIA attributes correct
- [ ] Focus management proper
- [ ] Color contrast passes
- [ ] WCAG 2.2 AA compliant

**Issues Found**: ___________

**Pass/Fail**: ___________

---

## Bug Reporting

### Bug Report Template

**Title**: [Component] Brief description

**Priority**: Critical / High / Medium / Low

**Environment**:
- WordPress Version: ___________
- PHP Version: ___________
- Plugin Version: ___________
- Browser: ___________
- OS: ___________
- Theme: ___________
- Other Plugins: ___________

**Steps to Reproduce**:
1.
2.
3.

**Expected Behavior**:
___________

**Actual Behavior**:
___________

**Screenshots/Videos**:
___________

**Console Errors**:
```
```

**Additional Notes**:
___________

**Reproducibility**: Always / Sometimes / Rare

---

### Bug Severity Levels

**Critical**:
- Plugin won't activate
- Data loss occurs
- Security vulnerability
- Complete feature failure
- Blocks editing

**High**:
- Major feature broken
- Affects many users
- No workaround available
- Significant UX issue

**Medium**:
- Minor feature issue
- Affects some users
- Workaround available
- Cosmetic issue with impact

**Low**:
- Minor cosmetic issue
- Edge case scenario
- Documentation issue
- Enhancement request

---

## Testing Sign-Off

### Release Readiness Checklist

Before marking release as ready:

- [ ] All critical tests pass
- [ ] All high priority tests pass
- [ ] No critical or high bugs open
- [ ] Medium/low bugs documented and triaged
- [ ] Accessibility testing complete
- [ ] Performance testing complete
- [ ] Security testing complete
- [ ] Browser compatibility verified
- [ ] WordPress version compatibility verified
- [ ] Documentation updated
- [ ] Changelog updated

**QA Lead Approval**: ___________
**Date**: ___________

**Product Owner Approval**: ___________
**Date**: ___________

**Notes**: ___________

---

## Appendix: Testing Tools Reference

### Required Tools

| Tool | Purpose | URL |
|------|---------|-----|
| WordPress | Testing environment | wordpress.org |
| Query Monitor | Performance/debugging | wordpress.org/plugins/query-monitor |
| axe DevTools | Accessibility testing | deque.com/axe/devtools |
| WAVE | Accessibility testing | wave.webaim.org |
| Lighthouse | Performance audit | Built into Chrome DevTools |
| NVDA | Screen reader (Windows) | nvaccess.org |
| VoiceOver | Screen reader (Mac/iOS) | Built into macOS/iOS |

### Optional Tools

- JAWS screen reader (commercial)
- Browser Stack for cross-browser testing
- GTmetrix for performance analysis
- WP-CLI for automation

---

**Document Version**: 1.0.0
**Last Updated**: 2025-12-02
**Plugin Version**: 1.0.0
