# Whisp - Professional Chat Widget Plugin

A comprehensive WordPress chat widget plugin designed for lead generation and customer communication with progressive form capture and integrated dashboard management.

## Features

### 🎯 Lead Generation
- **Progressive 4-Step Form**: Name → Email → Phone → Country
- **Real-time Validation**: Form validation with error messaging
- **Data Persistence**: Form data saved between steps
- **Smart Progress Bar**: Visual progress tracking with dots

### 💬 Chat Interface  
- **Professional Design**: Modern, responsive chat widget
- **Step-by-Step Navigation**: Clean form experience
- **Mobile Optimized**: Touch-friendly interface
- **Smooth Animations**: Professional transitions and effects

### 🛡️ Security
- **WordPress Nonces**: CSRF protection on all forms
- **Input Sanitization**: Server-side data validation
- **SQL Injection Protection**: Prepared statements
- **XSS Prevention**: Output escaping and filtering

### 📊 Dashboard Management
- **Lead Tracking**: Complete conversation history
- **Status Management**: New → Contacted → Qualified → Converted → Closed
- **Email Notifications**: Automated admin alerts
- **Export Capabilities**: CSV/Excel lead export

## File Structure

```
whisp/
├── whisp.php                 # Main plugin file
├── README.md                 # Documentation
├── template-parts/
│   └── chat-widget.php       # Chat widget HTML template
├── assets/
│   ├── css/
│   │   └── chat-widget.css   # Widget styling
│   └── js/
│       └── chat-widget.js    # Widget functionality
└── includes/
    └── chat-leads.php        # Backend lead management
```

## Technical Specifications

### Requirements
- **WordPress**: 5.0+
- **PHP**: 7.4+
- **MySQL**: 5.6+ or MariaDB 10.0+
- **Memory**: 128MB PHP memory limit

### Database Schema
- **Table**: `wp_whisp_chat_leads`
- **Fields**: id, name, email, phone, country, messages, status, timestamps
- **Indexes**: Optimized for performance

### Integration
- **AJAX Endpoints**: Secure form submission and messaging
- **WordPress Hooks**: Actions and filters for extensibility
- **Template System**: Customizable widget templates

## Installation

1. **Upload Plugin**: Copy the `whisp` folder to `/wp-content/plugins/`
2. **Activate Plugin**: Go to WordPress Admin → Plugins → Activate "Whisp"
3. **Configure Settings**: Visit Whisp Chat in admin menu
4. **Test Widget**: Check frontend for chat button

## Usage

### For Users
1. Click the floating chat button
2. Complete the 4-step form (Name, Email, Phone, Country)
3. Send messages and receive responses
4. Chat history is saved automatically

### For Admins
1. Go to **Whisp Chat** in WordPress admin
2. View all leads and conversations
3. Update lead status and add notes
4. Export lead data for CRM integration

## Customization

### CSS Variables
```css
:root {
  --whisp-brand: #24E3B3;
  --whisp-radius: 12px;
  --whisp-shadow: 0 4px 20px rgba(0,0,0,0.15);
}
```

### Hooks Available
- `whisp_before_form_render`
- `whisp_after_lead_save`
- `whisp_message_received`
- `whisp_widget_settings`

## Development Notes

### Original Implementation
This plugin was extracted from the ForexDrift theme where it was fully developed and tested. All functionality has been proven in production.

### Features Included
- ✅ 4-step progressive form with validation
- ✅ Real-time AJAX form submission  
- ✅ Complete lead management dashboard
- ✅ Email notifications system
- ✅ Mobile-responsive design
- ✅ Security implementation
- ✅ Database optimization

### Code Quality
- WordPress Coding Standards compliant
- Object-oriented PHP architecture
- Secure AJAX implementations
- Responsive CSS with modern practices
- ES6+ JavaScript with class-based structure

## Support

For technical support or customization requests, please refer to the plugin documentation or contact the development team.

## License

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html

---

**Version**: 1.0.0  
**Last Updated**: September 29, 2025  
**Tested WordPress Version**: 6.4  
**Production Ready**: ✅