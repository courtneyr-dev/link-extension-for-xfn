# Contributing to Link Extension for XFN

Thank you for considering contributing to the Link Extension for XFN! This document provides guidelines and instructions for contributing.

## Code of Conduct

This project adheres to a Code of Conduct that all contributors are expected to follow. Please read [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) before contributing.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue on GitHub with:

- **Clear title and description**
- **Steps to reproduce** the issue
- **Expected behavior** vs actual behavior
- **WordPress version**, PHP version, and browser
- **Screenshots** if applicable
- **Error messages** from console or Query Monitor

Use the [Bug Report template](.github/ISSUE_TEMPLATE/bug_report.md).

### Suggesting Features

We welcome feature suggestions! Please create an issue with:

- **Clear use case** - why is this feature needed?
- **Proposed solution** - how should it work?
- **Alternatives considered** - what other approaches did you consider?
- **XFN specification compliance** - does it align with XFN 1.1?

Use the [Feature Request template](.github/ISSUE_TEMPLATE/feature_request.md).

### Documentation Improvements

Documentation improvements are always welcome:

- Fix typos or unclear explanations
- Add examples or usage scenarios
- Improve code comments
- Update screenshots

Use the [Documentation template](.github/ISSUE_TEMPLATE/documentation.md).

## Development Setup

### Prerequisites

- **WordPress 6.4+**
- **PHP 7.4+**
- **Node.js 18+** and npm
- **Git**
- Local WordPress environment (Local, MAMP, Docker, etc.)

### Initial Setup

```bash
# Fork and clone the repository
git clone https://github.com/YOUR_USERNAME/link-extension-for-xfn.git
cd link-extension-for-xfn

# Install dependencies
npm install

# Build the plugin
npm run build

# Start development mode (auto-rebuild on changes)
npm run start
```

### Development Workflow

1. **Create a branch**:

   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make changes** in the `src/` directory

   - JavaScript: `src/index.js`
   - Styles: `src/editor.scss`, `src/style.scss`
   - PHP: `link-extension-for-xfn.php`

3. **Build and test**:

   ```bash
   npm run build
   # Test in WordPress block editor
   ```

4. **Run code quality checks**:

   ```bash
   # Format code
   npm run format

   # Lint JavaScript
   npm run lint:js

   # Lint CSS
   npm run lint:css

   # Lint PHP (requires PHP_CodeSniffer)
   composer run lint
   ```

5. **Test thoroughly**:

   - Test in block editor with various blocks
   - Test all three interfaces (toolbar, inspector, link popover)
   - Test with Query Monitor active
   - Test keyboard navigation
   - Test screen reader compatibility
   - Test in multiple browsers

6. **Commit your changes**:

   ```bash
   git add .
   git commit -m "feat: add your feature description"
   ```

   Follow [Conventional Commits](https://www.conventionalcommits.org/):

   - `feat:` - New features
   - `fix:` - Bug fixes
   - `docs:` - Documentation changes
   - `style:` - Code style changes (formatting)
   - `refactor:` - Code refactoring
   - `test:` - Adding tests
   - `chore:` - Maintenance tasks

7. **Push and create Pull Request**:
   ```bash
   git push origin feature/your-feature-name
   ```
   Then open a Pull Request on GitHub.

## Coding Standards

### PHP

Follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/):

- Use tabs for indentation
- Space after control structures: `if ( condition )`
- Yoda conditions: `if ( true === $value )`
- Single quotes for strings (unless interpolation needed)
- Comprehensive PHPDoc blocks

### JavaScript

Follow [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/):

- Use tabs for indentation
- ES6+ syntax preferred
- Destructuring for imports
- Functional components for React
- PropTypes for component props

### CSS

Follow [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/):

- Use tabs for indentation
- Properties in alphabetical order
- One selector per line
- Space after property colon

### Accessibility

All contributions must maintain WCAG 2.2 AA compliance:

- Proper ARIA labels and roles
- Keyboard navigation support
- Focus management
- Screen reader compatibility
- High contrast mode support

## XFN Specification Compliance

This plugin implements the [XFN 1.1 specification](http://gmpg.org/xfn/11). All contributions must:

- Follow XFN relationship definitions exactly
- Respect mutual exclusivity rules
- Support all 7 relationship categories
- Maintain semantic HTML output

Do not:

- Add non-XFN relationships
- Modify XFN specification behavior
- Change relationship validation rules

## Testing

### Manual Testing

1. Test with **Query Monitor** active - no errors allowed
2. Test in **multiple WordPress themes**
3. Test with **common plugins** (Yoast, Rank Math, WooCommerce)
4. Test in **multiple browsers** (Chrome, Firefox, Safari, Edge)
5. Test **keyboard navigation** fully
6. Test with **screen readers** (NVDA, JAWS, VoiceOver)

### Automated Testing

```bash
# Run all tests
npm test

# Run specific tests
npm run test:unit
npm run test:e2e
```

## Pull Request Process

1. **Update documentation** if needed
2. **Add to CHANGELOG.md** under "Unreleased" section
3. **Ensure all tests pass**
4. **Request review** from maintainers
5. **Address feedback** promptly
6. **Squash commits** if requested

### PR Checklist

- [ ] Code follows WordPress coding standards
- [ ] All tests pass
- [ ] No console errors or warnings
- [ ] Query Monitor shows zero errors
- [ ] Tested in multiple browsers
- [ ] Keyboard navigation works
- [ ] Screen reader compatible
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] Commit messages follow convention

## Release Process

Maintainers only:

1. Update version in `package.json`, `link-extension-for-xfn.php`, `readme.txt`
2. Update `CHANGELOG.md` with release date
3. Build production assets: `npm run build`
4. Create git tag: `git tag -a v1.x.x -m "Version 1.x.x"`
5. Push tag: `git push origin v1.x.x`
6. GitHub Action automatically creates release ZIP
7. Upload to WordPress.org via SVN

## Questions?

- **GitHub Discussions**: Ask questions and share ideas
- **GitHub Issues**: Report bugs or request features
- **WordPress.org Forums**: Get user support

## License

By contributing, you agree that your contributions will be licensed under the GPL v2 or later license.

## Recognition

All contributors will be recognized in:

- GitHub Contributors page
- CHANGELOG.md for their contributions
- Plugin credits (for significant contributions)

Thank you for contributing to the Link Extension for XFN! 🎉
