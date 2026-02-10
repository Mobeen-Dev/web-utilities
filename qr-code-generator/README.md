# QR Code Generator - WordPress Plugin

A professional WordPress plugin that generates QR codes from URLs in SVG and PNG formats with easy download functionality.

## Features

- ✅ Generate QR codes from any URL
- ✅ Support for PNG and SVG formats
- ✅ Customizable QR code options (size, colors, margin)
- ✅ One-click download functionality
- ✅ Responsive design
- ✅ Multiple theme options (default, minimal, modern)
- ✅ AJAX-powered for smooth user experience
- ✅ Security with nonce verification
- ✅ Translation ready
- ✅ WordPress coding standards compliant

## Installation

### Method 1: Upload via WordPress Admin

1. Download the plugin ZIP file
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin"
4. Choose the ZIP file and click "Install Now"
5. Activate the plugin

### Method 2: FTP Upload

1. Extract the ZIP file
2. Upload the `qr-code-generator` folder to `/wp-content/plugins/`
3. Go to WordPress Admin → Plugins
4. Activate "QR Code Generator"

## Usage

### Using Shortcode

Add the QR code generator to any page or post using the shortcode:

```
[qr_code_generator]
```

### Shortcode Attributes

Customize the generator with these optional attributes:

```
[qr_code_generator 
    title="Free QR Code Generator" 
    subtitle="Generate QR codes instantly"
    button_text="Generate Now"
    placeholder="Enter URL..."
    show_options="yes"
    default_size="300"
    default_format="png"
    theme="default"
]
```

**Available Attributes:**

- `title` - Main heading (default: "Free QR Code Generator")
- `subtitle` - Subtitle text (default: "Generate QR codes from your URLs instantly")
- `button_text` - Generate button text (default: "Generate QR Code")
- `placeholder` - Input placeholder (default: "Enter your URL here...")
- `show_options` - Show customization options (yes/no, default: yes)
- `default_size` - Default QR code size in pixels (default: 300)
- `default_format` - Default format (png/svg, default: png)
- `theme` - Visual theme (default/minimal/modern, default: default)

### Examples

**Basic Usage:**
```
[qr_code_generator]
```

**Custom Title and Button:**
```
[qr_code_generator title="Get Your QR Code" button_text="Create QR Code"]
```

**Minimal Theme without Options:**
```
[qr_code_generator theme="minimal" show_options="no"]
```

**Modern Theme with Custom Defaults:**
```
[qr_code_generator 
    theme="modern" 
    default_size="400" 
    default_format="svg"
]
```

## File Structure

```
qr-code-generator/
├── qr-code-generator.php          # Main plugin file
├── includes/
│   ├── class-qr-generator.php     # QR generation logic
│   ├── class-qr-ajax-handler.php  # AJAX request handling
│   └── class-qr-shortcode.php     # Shortcode registration
├── assets/
│   ├── css/
│   │   └── qr-style.css          # Plugin styles
│   └── js/
│       └── qr-script.js          # Frontend JavaScript
└── README.md                      # Documentation
```

## Features Explained

### QR Code Customization

Users can customize their QR codes with:

1. **Format**: Choose between PNG (raster) or SVG (vector) format
2. **Size**: Adjust QR code dimensions (100px - 1000px)
3. **Margin**: Add white space around the QR code (0-50px)
4. **Color**: Customize the QR code color (default: black)
5. **Background**: Change background color (default: white)

### Security

- Nonce verification for all AJAX requests
- URL validation and sanitization
- Input sanitization following WordPress standards
- Capability checks where applicable

### Performance

- Optimized AJAX requests
- Minimal DOM manipulation
- Efficient CSS with modern layout techniques
- Lazy loading of options panel

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Minimum Requirements

- WordPress: 5.0 or higher
- PHP: 7.2 or higher
- MySQL: 5.6 or higher

## API Usage

This plugin uses the QR Server API (https://api.qrserver.com) to generate QR codes. The API is free and doesn't require authentication.

### Alternative: Using Your Own QR Generation

To use a different QR code generation method, modify the `QR_Generator` class in `includes/class-qr-generator.php`.

## Customization

### Custom Styles

Add custom CSS to your theme:

```css
/* Override button color */
.qr-generate-btn {
    background: #your-color !important;
}

/* Custom container width */
.qr-code-generator-wrapper {
    max-width: 1000px;
}
```

### Hooks and Filters

The plugin provides hooks for developers:

```php
// Modify QR code options before generation
add_filter('qr_code_generator_options', function($options) {
    $options['size'] = 500; // Force 500px size
    return $options;
});
```

## Troubleshooting

### QR Code Not Generating

1. Check browser console for JavaScript errors
2. Verify AJAX URL is correct
3. Ensure PHP `allow_url_fopen` is enabled
4. Check firewall isn't blocking API requests

### Download Not Working

1. Check browser's download permissions
2. Verify popup blockers aren't interfering
3. Test in different browsers

### Styling Issues

1. Clear cache (browser and WordPress)
2. Check for theme CSS conflicts
3. Try disabling other plugins temporarily

## Support

For support and bug reports:
- Create an issue on GitHub
- Contact: your-email@example.com

## Changelog

### Version 1.0.0
- Initial release
- PNG and SVG format support
- Customization options
- Multiple themes
- Download functionality

## License

GPL v2 or later

## Credits

- QR Code generation powered by QR Server API
- Developed following WordPress coding standards

## Future Enhancements

- [ ] Bulk QR code generation
- [ ] QR code history
- [ ] Custom logo embedding
- [ ] Admin dashboard widget
- [ ] REST API endpoints
- [ ] Gutenberg block
- [ ] Elementor widget

---

**Made with ❤️ for the WordPress community**
