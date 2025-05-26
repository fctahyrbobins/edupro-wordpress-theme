# EduPro WordPress Theme

A premium WordPress theme tailored for educational platforms with Moodle integration. This theme provides a modern, accessible, and feature-rich learning management system (LMS) experience.

## Features

### 1. Live Page Builder for Moodle
- Real-time page editing and customization
- Drag-and-drop interface
- Live preview functionality
- Seamless Moodle integration

### 2. Quick Links Menu
- Easy navigation across the platform
- Customizable menu items
- Mobile-responsive design

### 3. Multi-language Support
- Built-in translation support
- RTL language compatibility
- Language switcher widget

### 4. Web Accessibility Features
- WCAG 2.1 compliant
- High contrast mode
- Font size adjustment
- Screen reader optimization
- Dyslexic font support
- Keyboard navigation
- Skip links

### 5. Responsive Design
- Mobile-first approach
- Fluid layouts
- Optimized for all screen sizes
- Touch-friendly interface

### 6. Course Administration
- Advanced course filters
- Custom course enrollment page
- Dedicated course homepage
- Category color customization
- Comprehensive course management panel

### 7. Learning Features
- Course table of contents
- Full-screen/focus mode
- Course notes functionality
- Progress tracking
- Interactive quizzes

### 8. Modern UI Elements
- Multiple header styles
- Toggle sidebar
- Custom dashboard
- Mega menu
- Modern typography
- Clean aesthetics

## Installation

1. Download the theme package
2. Go to WordPress admin panel > Appearance > Themes
3. Click "Add New" and then "Upload Theme"
4. Choose the downloaded zip file and click "Install Now"
5. After installation, click "Activate"

## Configuration

### Theme Options

1. Navigate to WordPress admin panel > Theme Options
2. Configure the following sections:
   - General Settings
   - Header Options
   - Footer Options
   - Course Settings
   - Moodle Integration
   - Accessibility Options
   - Typography Settings
   - Color Schemes

### Moodle Integration

1. Go to Theme Options > Moodle Integration
2. Enter your Moodle URL and API token
3. Configure sync settings
4. Test the connection

### Course Management

1. Access WordPress admin panel > Courses
2. Add new courses or manage existing ones
3. Configure course settings:
   - Basic information
   - Curriculum
   - Prerequisites
   - Pricing
   - Categories
   - Featured image

## Development

### Requirements

- Node.js >= 16.0.0
- npm or yarn
- WordPress >= 6.0
- PHP >= 7.4

### Setup Development Environment

```bash
# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build

# Watch for changes
npm run watch
```

### File Structure

```
edupro/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── inc/
│   ├── moodle-integration.php
│   ├── accessibility.php
│   └── course-management.php
├── src/
│   └── css/
│       └── tailwind.css
├── template-parts/
│   ├── header/
│   ├── footer/
│   └── course/
├── functions.php
├── index.php
├── style.css
├── header.php
├── footer.php
├── single-course.php
├── archive-course.php
├── screenshot.php
├── package.json
├── tailwind.config.js
└── README.md
```

## Customization

### Adding Custom Styles

1. Create a child theme
2. Add custom CSS in the child theme's style.css
3. Use Tailwind CSS utilities for minor modifications

### Modifying Templates

1. Copy template files to your child theme
2. Modify the copied templates
3. WordPress will automatically use child theme templates

### Adding New Features

1. Create new template files in your child theme
2. Add new functions to child theme's functions.php
3. Use WordPress hooks and filters for modifications

## Support

- Documentation: [link-to-documentation]
- Support Forum: [link-to-forum]
- Email Support: support@example.com

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This theme is licensed under the GPL v2 or later.

## Credits

- Tailwind CSS
- Font Awesome
- Google Fonts
- WordPress
- Moodle

## Changelog

### Version 1.0.0
- Initial release
- Basic theme features
- Moodle integration
- Accessibility features
- Course management system

## Authors

BLACKBOXAI

## Support and Updates

For premium support and regular updates, please visit [your-website].
