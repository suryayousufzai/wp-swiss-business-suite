# WP Swiss Business Suite

Professional WordPress plugin for Swiss small and medium-sized enterprises. Provides multilingual support, booking management, and business tools designed specifically for the Swiss market.

## About

WP Swiss Business Suite is a comprehensive business solution built for Swiss companies operating in a multilingual environment. The plugin addresses the unique needs of Swiss businesses by supporting all four official languages (German, French, Italian, and English) while providing essential business management tools.

## Current Features

### Phase 1: Multilingual System

The multilingual system provides full support for Switzerland's four official languages with a clean, professional interface that integrates seamlessly into WordPress navigation menus.

**Language Support**
- German (Deutsch) - Default language
- French (Français)
- Italian (Italiano)  
- English

**Features**
- Professional language switcher integrated into navigation menu
- Clean "DE | FR | IT | EN" display style matching Swiss corporate websites
- Admin control panel for easy management
- Compatible with translation management systems (Polylang, TranslatePress)
- Automatic language detection and persistence
- Mobile-responsive design

### Phase 2: Booking System

Complete appointment and reservation management system designed for service-based businesses.

**Core Features**
- Online appointment scheduling
- Service management and configuration
- Customer booking form with validation
- Email confirmation system
- Admin booking dashboard
- Calendar view of appointments
- Booking metadata tracking

**Admin Interface**
- Comprehensive booking overview
- Service creation and management
- Settings configuration
- Email template customization
- Time slot management

### Phase 3: Invoice Generator (Planned)

Swiss QR-Bill compliant invoicing system currently in development.

**Planned Features**
- Swiss QR-Bill generation
- Invoice management
- Payment tracking
- Tax calculation (Swiss VAT)
- Multi-currency support
- Financial reporting

## Technical Details

### System Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher  
- MySQL 5.7 or higher
- Modern web browser

### Database Architecture

The plugin creates five database tables for efficient data management:

```
wp_sbs_languages      - Language configuration and settings
wp_sbs_translations   - Translation content storage
wp_sbs_bookings       - Appointment and reservation data
wp_sbs_booking_meta   - Extended booking information
wp_sbs_services       - Service definitions and pricing
```

### Code Structure

Built using object-oriented programming principles with clean separation of concerns:

```
wp-swiss-business-suite/
├── admin/
│   ├── class-admin.php           Main admin class
│   ├── views/                    Admin page templates
│   │   ├── dashboard.php
│   │   ├── bookings.php
│   │   ├── languages.php
│   │   ├── services.php
│   │   └── settings.php
│   ├── css/                      Admin stylesheets
│   └── js/                       Admin JavaScript
├── public/
│   ├── class-public.php          Public-facing functionality
│   ├── css/                      Public stylesheets
│   ├── js/                       Public JavaScript
│   └── templates/                Frontend templates
├── includes/
│   ├── core/                     Core functionality
│   │   ├── class-activator.php
│   │   ├── class-deactivator.php
│   │   ├── class-loader.php
│   │   └── class-plugin.php
│   ├── multilang/                Multilingual system
│   │   ├── class-language-manager.php
│   │   ├── class-translator.php
│   │   └── class-language-switcher.php
│   ├── booking/                  Booking system
│   │   ├── class-booking-manager.php
│   │   ├── class-calendar.php
│   │   └── class-email-handler.php
│   └── database/
│       └── class-db-setup.php    Database installation
└── wp-swiss-business-suite.php   Main plugin file
```

## Installation

### Method 1: Upload via WordPress Admin

1. Download the plugin ZIP file
2. Navigate to WordPress Admin > Plugins > Add New
3. Click "Upload Plugin" button
4. Choose the ZIP file and click "Install Now"
5. Click "Activate" after installation completes

### Method 2: Manual Installation

1. Download and extract the plugin files
2. Upload the `wp-swiss-business-suite` folder to `/wp-content/plugins/`
3. Navigate to WordPress Admin > Plugins
4. Find "WP Swiss Business Suite" and click "Activate"

### Method 3: Install from GitHub

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/suryayousufzai/wp-swiss-business-suite.git
```

Then activate via WordPress admin panel.

## Configuration

After activation, the plugin creates a new "Swiss Business" menu in the WordPress admin sidebar.

### Initial Setup

1. Go to Swiss Business > Settings
2. Configure translation options:
   - Enable or disable multilingual features
   - Choose language switcher display style
   - Select menu location for language switcher
3. Set up your services (Swiss Business > Services)
4. Configure booking options

### Adding Booking Form

Insert the booking form on any page or post using the shortcode:

```
[wp_sbs_booking]
```

### Adding Language Switcher

The language switcher automatically appears in your navigation menu based on settings. You can also manually add it anywhere using:

```
[wp_sbs_language_switcher]
```

## Usage Guide

### Managing Bookings

Access the booking management dashboard at Swiss Business > Bookings. Here you can:

- View all appointments in a calendar layout
- See booking details and customer information
- Update booking status
- Send confirmation emails
- Export booking data

### Managing Languages

Navigate to Swiss Business > Languages to:

- Enable or disable specific languages
- Set default language
- Configure auto-detection settings
- Manage translation integration

### Managing Services

Create and manage your services at Swiss Business > Services:

- Add new services with pricing
- Set service duration and availability
- Configure service-specific options
- Organize services by category

## Translation Integration

The plugin is designed to work alongside professional translation management systems. We recommend using one of the following:

**Polylang** (Free)
- Manual translation control
- Per-page translation
- String translation
- SEO-friendly URLs

**TranslatePress** (Free & Pro versions)
- Visual translation editor
- Automatic translation option
- Front-end translation interface
- SEO optimized

### Setting up with Polylang

1. Install and activate Polylang
2. Configure your four languages (DE, FR, IT, EN)
3. The plugin's language switcher will automatically integrate
4. Translate pages through WordPress admin

## Development

### Local Development Setup

```bash
# Clone the repository
git clone https://github.com/suryayousufzai/wp-swiss-business-suite.git

# Navigate to your WordPress plugins directory
cd /path/to/wordpress/wp-content/plugins/

# Create symbolic link for development
ln -s /path/to/cloned/repo wp-swiss-business-suite

# Activate in WordPress admin
```

### Coding Standards

This plugin follows WordPress coding standards and best practices:

- WordPress PHP Coding Standards
- WordPress JavaScript Coding Standards  
- Proper data sanitization and validation
- Secure database queries using wpdb
- Internationalization ready
- Extensive inline documentation

### Hooks and Filters

The plugin provides several hooks for customization:

**Actions**
```php
do_action('wp_sbs_before_booking_form');
do_action('wp_sbs_after_booking_form');
do_action('wp_sbs_booking_created', $booking_id);
```

**Filters**
```php
apply_filters('wp_sbs_booking_email_subject', $subject, $booking);
apply_filters('wp_sbs_available_languages', $languages);
apply_filters('wp_sbs_service_price', $price, $service_id);
```

## Roadmap

### Version 1.5 (Q2 2026)
- Enhanced booking notifications
- Service categories
- Booking calendar export
- Customer management system

### Version 2.0 (Q3 2026)
- Swiss QR-Bill invoice generator
- Payment integration
- Financial reporting
- Multi-location support

### Version 2.5 (Q4 2026)
- Recurring appointments
- Staff management
- Advanced analytics
- Mobile app integration

## Support and Documentation

For questions, bug reports, or feature requests:

- GitHub Issues: https://github.com/suryayousufzai/wp-swiss-business-suite/issues
- Email: surya.yousufzai@auaf.edu.af

## Contributing

Contributions are welcome. Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -m 'Add some feature'`)
4. Push to the branch (`git push origin feature/YourFeature`)
5. Open a Pull Request

Please ensure your code follows WordPress coding standards and includes appropriate documentation.

## License

This plugin is licensed under the GPL v2 or later.

```
WP Swiss Business Suite
Copyright (C) 2026 Surya Yousufzai

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

Full license text available in the LICENSE file.

## Credits

### Author

Surya Yousufzai
- Website: https://suryayousufzai.github.io
- GitHub: https://github.com/suryayousufzai
- Email: surya.yousufzai@auaf.edu.af

### Acknowledgments

This plugin was developed specifically for the Swiss small business market, taking inspiration from professional Swiss corporate websites and addressing the unique multilingual requirements of Swiss enterprises.

Special thanks to the WordPress community for providing excellent documentation and development tools.

## Changelog

### Version 1.4.1 (2026-03-04)
- Implemented professional booking system
- Added comprehensive admin dashboard
- Integrated Swiss-style language switcher into navigation menus
- Optimized database structure for performance
- Enhanced email notification system
- Improved mobile responsiveness
- Added translation system integration support

### Version 1.0.0 (2026-03-01)
- Initial release
- Basic multilingual functionality
- Simple booking form
- Admin interface foundation

---

Built with care for Swiss businesses by Surya Yousufzai
