# Changelog

All notable changes to the QR Code Generator WordPress Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.2] - 2026-02-11

### Changed
- Replaced color pickers with hex-only inputs and swatch previews.

### Fixed
- Button icon/spinner alignment and pressed-state visuals.
- Share now copies the page URL to clipboard and shows a success toast.

## [1.0.0] - 2024-02-09

### Added
- Initial release of QR Code Generator plugin
- QR code generation from URLs
- Support for PNG format
- Support for SVG format
- Customizable QR code size (100px - 1000px)
- Adjustable margin (0-50px)
- Custom color selection for QR code
- Custom background color selection
- One-click download functionality
- Responsive design for mobile devices
- AJAX-powered generation for smooth UX
- Security with WordPress nonce verification
- Three theme options (default, minimal, modern)
- Shortcode support with multiple attributes
- Translation ready with text domain
- Comprehensive documentation
- Integration examples
- WordPress coding standards compliance

### Features
- **Shortcode**: `[qr_code_generator]` with customizable attributes
- **Formats**: PNG and SVG output
- **Customization**: Size, colors, margin, error correction
- **Security**: Nonce verification, input sanitization, URL validation
- **Performance**: Optimized AJAX, minimal DOM manipulation
- **Accessibility**: Semantic HTML, ARIA labels where needed
- **Responsive**: Mobile-friendly design

### Technical Details
- PHP 7.2+ compatible
- WordPress 5.0+ compatible
- Uses QR Server API for generation
- Object-oriented architecture
- Follows WordPress Plugin Handbook guidelines
- PSR-4 autoloading ready
- No external dependencies required

### Files Included
- Main plugin file with initialization
- QR Generator class for code generation
- AJAX Handler class for requests
- Shortcode class for page integration
- Frontend JavaScript for interactions
- Responsive CSS with theme variants
- Comprehensive documentation
- Integration examples
- Installation guide

### Security
- All inputs sanitized using WordPress functions
- Nonce verification on all AJAX requests
- URL validation before processing
- Hex color validation
- Integer validation for numeric inputs
- No SQL queries (stateless operation)
- Directory protection with index.php files

### Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS/Android)

---

## [1.0.1]

### Added
- Documentation: `action.md` with a focused fix plan for responsive layout, SVG preview, validation limits, color UX, loader feedback, sharing, and dark color QR issues.
- UI: Hex text inputs synchronized with color pickers, helper text, and status line for clearer feedback.
- UX: Share button with Web Share API and clipboard fallback.
- Options: Error correction selector (defaults to M) for more resilient codes.

### Changed
- Responsiveness improvements for small screens (<=480px) and stacked controls on mobile.
- Loader now keeps button text visible with a spinner and status line instead of masking text.
- SVG preview handling to prevent cropping and scale responsively.

### Fixed
- Input validation for URL length (2048 max), size (100-1000), margin (0-50), and hex colors on client and server.
- Contrast checks for QR/Background colors to prevent unreadable dark combinations.

### Planned Features
- Bulk QR code generation
- QR code history/library
- Custom logo/image embedding in QR codes
- Admin dashboard widget
- Gutenberg block version
- Elementor widget integration
- REST API endpoints
- Advanced analytics
- QR code tracking
- vCard QR code support
- WiFi QR code support
- Email QR code support
- SMS QR code support
- Download history
- User accounts for saving codes
- Premium templates
- Export to various formats
- Batch processing
- CSV import for bulk generation

### Under Consideration
- Custom API endpoint selection
- Offline generation capability
- Database storage option
- Multi-language QR codes
- Dynamic QR codes
- Password protected QR codes
- Expiring QR codes
- QR code statistics
- A/B testing for QR designs

---

## Version History

### [1.0.0] - 2024-02-09
- 🎉 Initial public release

---

## Upgrade Notices

### 1.0.0
This is the first release. No upgrade required.

---

## Contributors
- Initial development and release

## Support
For support inquiries, bug reports, or feature requests:
- GitHub Issues: [Create an issue]
- Email: support@example.com
- Documentation: See README.md

---

**Legend:**
- ✨ Added - New features
- 🔧 Changed - Changes in existing functionality  
- 🐛 Fixed - Bug fixes
- 🗑️ Removed - Removed features
- 🔒 Security - Security improvements
- 📝 Deprecated - Soon-to-be removed features
