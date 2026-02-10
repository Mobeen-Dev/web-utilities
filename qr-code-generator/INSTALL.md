# Quick Installation Guide

## Step-by-Step Installation

### Option 1: WordPress Admin Dashboard (Recommended)

1. **Download the Plugin**
   - Download the `qr-code-generator.zip` file

2. **Upload to WordPress**
   - Log in to your WordPress admin dashboard
   - Navigate to `Plugins > Add New`
   - Click the `Upload Plugin` button at the top
   - Click `Choose File` and select the downloaded ZIP file
   - Click `Install Now`

3. **Activate**
   - After installation, click `Activate Plugin`
   - The plugin is now active and ready to use!

### Option 2: FTP/cPanel Upload

1. **Extract Files**
   - Unzip the `qr-code-generator.zip` file on your computer

2. **Upload via FTP**
   - Connect to your site via FTP or cPanel File Manager
   - Navigate to `/wp-content/plugins/`
   - Upload the entire `qr-code-generator` folder

3. **Activate**
   - Go to WordPress Admin > Plugins
   - Find "QR Code Generator" in the list
   - Click `Activate`

## Using the Plugin

### Add to Any Page or Post

1. **Edit Page/Post**
   - Go to the page or post where you want the QR generator
   - Switch to the editor (Block Editor or Classic Editor)

2. **Insert Shortcode**
   
   **For Block Editor (Gutenberg):**
   - Click the `+` button to add a block
   - Search for "Shortcode"
   - Add the Shortcode block
   - Type: `[qr_code_generator]`
   
   **For Classic Editor:**
   - Simply type: `[qr_code_generator]`

3. **Publish/Update**
   - Click `Publish` or `Update`
   - Visit your page to see the QR code generator in action!

### Customization Examples

**Basic with Custom Title:**
```
[qr_code_generator title="Create Your QR Code"]
```

**Without Customization Options:**
```
[qr_code_generator show_options="no"]
```

**Modern Theme:**
```
[qr_code_generator theme="modern"]
```

**Full Customization:**
```
[qr_code_generator 
    title="Free QR Generator" 
    subtitle="Create QR codes in seconds"
    button_text="Generate Now"
    theme="minimal"
    default_size="400"
    default_format="svg"
]
```

## Verification

After installation, verify everything works:

1. ✅ Plugin appears in Plugins list
2. ✅ No error messages displayed
3. ✅ Shortcode renders on frontend
4. ✅ Can generate QR codes
5. ✅ Download function works

## Troubleshooting

**Plugin not working?**
- Clear your browser cache
- Clear WordPress cache (if using caching plugin)
- Check if JavaScript is enabled in your browser
- Try deactivating other plugins temporarily

**QR codes not generating?**
- Check your server can make outbound HTTP requests
- Verify PHP `allow_url_fopen` is enabled
- Check firewall settings

**Need Help?**
- Check the README.md file for detailed documentation
- Review integration-examples.php for advanced usage
- Contact support with error messages

## Next Steps

- Customize the appearance with custom CSS
- Check out integration examples for advanced features
- Add the generator to multiple pages
- Share with your users!

---

**Enjoy your QR Code Generator! 🎉**
