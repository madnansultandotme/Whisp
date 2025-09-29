# Whisp Plugin Installation Guide

## Quick Installation

### Method 1: Manual Upload
1. **Zip the Plugin**: Create a zip file from the `Whisp` folder
2. **Upload via WordPress**: Go to **WordPress Admin → Plugins → Add New → Upload Plugin**
3. **Browse**: Select the `whisp.zip` file
4. **Install**: Click "Install Now"
5. **Activate**: Click "Activate Plugin"

### Method 2: FTP Upload
1. **Upload Folder**: Copy the entire `Whisp` folder to `/wp-content/plugins/`
2. **Set Permissions**: Ensure proper file permissions (644 for files, 755 for folders)
3. **Activate**: Go to **WordPress Admin → Plugins** and activate "Whisp"

## Post-Installation Setup

### 1. Verify Installation
- ✅ Check **WordPress Admin → Whisp Chat** appears in menu
- ✅ Visit your website frontend and look for floating chat button
- ✅ Test the 4-step form functionality

### 2. Basic Configuration
```php
// Optional: Add to wp-config.php for custom settings
define('WHISP_BRAND_COLOR', '#24E3B3');
define('WHISP_POSITION', 'bottom-right');
```

### 3. Database Verification
The plugin automatically creates the table `wp_whisp_chat_leads` with these fields:
- id (primary key)
- name, email, phone, country
- chat_messages (JSON)
- status, timestamps, etc.

## Testing the Widget

### User Flow Test
1. **Open Website**: Go to your website frontend
2. **Click Chat Button**: Floating button should appear bottom-right
3. **Complete Form**: Fill out 4-step form (Name → Email → Phone → Country)
4. **Test Chat**: Send a test message
5. **Check Admin**: Go to **Whisp Chat** in admin to see the lead

### Admin Dashboard Test
1. **Go to Admin**: WordPress Admin → Whisp Chat
2. **View Leads**: Should see your test lead
3. **Check Data**: Verify all form data was captured
4. **Test Status**: Update lead status to "Contacted"

## Troubleshooting

### Common Issues

#### 1. Chat Widget Not Appearing
- **Check Plugin Active**: Ensure plugin is activated
- **Check Theme**: Verify `wp_footer()` exists in your theme
- **Browser Console**: Check for JavaScript errors

#### 2. Form Not Submitting
- **AJAX URLs**: Check browser console for 404 errors on AJAX calls
- **Nonce Issues**: Verify WordPress nonces are working
- **Server Permissions**: Check file permissions are correct

#### 3. Database Issues
- **Table Creation**: Check if `wp_whisp_chat_leads` table exists
- **Permissions**: Verify database user has CREATE TABLE permissions
- **WordPress Version**: Ensure WordPress 5.0+ compatibility

### Debug Mode
Add to wp-config.php for debugging:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Customization

### CSS Customization
```css
/* Add to your theme's style.css */
:root {
  --whisp-brand: #YOUR_COLOR;
  --whisp-radius: 12px;
}

#whisp-chat-widget .chat-toggle-btn {
  bottom: 30px;
  right: 30px;
}
```

### PHP Hooks
```php
// Add to your theme's functions.php
add_action('whisp_after_lead_save', function($lead_id) {
  // Your custom code here
});
```

## Migration from ForexDrift Theme

If you previously used this widget in the ForexDrift theme:

1. **Backup Data**: Export existing leads from the theme
2. **Install Plugin**: Follow installation steps above  
3. **Data Migration**: Leads will be in separate `whisp_chat_leads` table
4. **Update References**: Change any custom code to use `whisp_` prefixes

## Support

### Files to Check
- **Main Plugin**: `whisp.php` (entry point)
- **Styles**: `assets/css/chat-widget.css`
- **Scripts**: `assets/js/chat-widget.js`  
- **Backend**: `includes/chat-leads.php`
- **Template**: `template-parts/chat-widget.php`

### WordPress Requirements
- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher
- **Memory**: 128MB minimum

### Browser Compatibility
- ✅ Chrome 60+
- ✅ Firefox 55+
- ✅ Safari 12+
- ✅ Edge 79+
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

---

## Quick Start Checklist

- [ ] Plugin uploaded and activated
- [ ] Chat button appears on frontend
- [ ] 4-step form works properly
- [ ] Form submission creates database entry
- [ ] Admin dashboard shows leads
- [ ] Email notifications working (optional)

**Installation Complete!** 🎉

The Whisp chat widget is now ready to capture leads and manage conversations on your WordPress website.