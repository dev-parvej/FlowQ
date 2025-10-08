# WordPress Dynamic Survey Plugin - Current Implementation

## What's Currently Implemented

### Core Plugin Structure
- WordPress plugin with proper activation/deactivation hooks
- Database migration system with custom tables
- Internationalization support (text domain: 'wp-dynamic-survey')
- Security measures (nonce verification, capability checks)

### Question System
- **Single Question Type Only**: Hardcoded 'single_choice' type in `class-question-manager.php:62`
- Question CRUD operations (create, read, update, delete, duplicate)
- Question fields: title, description, extra_message
- Questions ordered by ID (no custom ordering)

### Answer System
- Multiple answer options per question
- Answer fields: answer_text, answer_value, answer_order
- **Conditional Logic**: Each answer can specify `next_question_id` for branching
- **External Redirects**: Each answer can specify `redirect_url`
- **Skip Question Option**: Questions can be marked as skippable, allowing users to proceed without selecting an answer
- Answer reordering functionality

### Database Schema
Tables created:
- `wp_dynamic_survey_surveys`
- `wp_dynamic_survey_questions`
- `wp_dynamic_survey_answers`
- `wp_dynamic_survey_responses`
- `wp_dynamic_survey_participants`
- `wp_dynamic_survey_sessions`
- `wp_dynamic_survey_templates`

### Admin Interface
- Survey management pages
- Question builder with AJAX operations
- Answer options management
- Question duplication
- Analytics and participant tracking
- Shortcode builder
- **Settings Page**: Tabbed navigation interface
  - Templates tab for global template selection

### Template System
- **Global Templates**: Template applies to all surveys (not per-survey)
- **Default Templates**: 5 pre-built templates seeded in database
  - Classic - Traditional form-style
  - Modern - Clean, minimalist design
  - Card-based - Elevated card layout
  - Dark Mode - Dark theme with dark backgrounds, white text, and dark input fields
  - Colorful - Vibrant gradient design
- **Template Storage**:
  - Templates table with fields: id, name, description, is_default, preview_image, styles (JSON)
  - Active template stored in options: `wp_dynamic_survey_active_template`
  - Preview images: SVG files in `assets/images/templates/`
- **Template Selection UI**: Grid layout with preview cards, active badge, and select buttons
- **Template Handler** (`includes/class-template-handler.php`):
  - Dynamic CSS generation based on template styles
  - Supports customizable colors: primary_color, background_color, text_color
  - Input field theming: input_bg_color, input_border_color, input_text_color
  - Button styles: solid, gradient, elevated
  - Border radius and font family customization
  - Auto-applies to participant forms and question containers
  - Dark theme support with white text on hover for answer options

### Frontend
- Survey display via shortcodes
- AJAX-powered survey navigation
- Session management for participants
- Progress tracking
- Thank you page handling

### Integrations
- REST API endpoints (`class-rest-api.php`)
- Centralized AJAX handling (`class-ajax-handler.php`)

### File Structure
```
wp-dynamic-survey/
├── admin/                    # Admin interface
├── includes/                 # Core classes
├── public/                   # Frontend
├── assets/                   # CSS/JS
└── survey-plugin.php        # Main plugin file
```

### Key Classes
- `WP_Dynamic_Survey_Question_Manager`: Question CRUD operations
- `WP_Dynamic_Survey_Question_Admin`: Admin AJAX handlers
- `WP_Dynamic_Survey_Frontend`: Frontend display
- `WP_Dynamic_Survey_Manager`: Survey management
- `WP_Dynamic_Survey_Session_Manager`: Session handling
- `WP_Dynamic_Survey_Settings_Admin`: Settings page with tab navigation
- `WP_Dynamic_Survey_DB_Migrator`: Database migrations including templates table
- `WP_Dynamic_Survey_Template_Handler`: Template management and dynamic CSS generation

### Recent Updates
- Added Settings page with tab navigation (`admin/class-settings-admin.php`)
- Implemented template system with database table and seeded 5 default templates
- Created SVG preview images for all templates
- Built template selection UI with flexbox grid layout
- Created `WP_Dynamic_Survey_Template_Handler` class for centralized template management
- Integrated dynamic CSS generation into participant forms and question containers
- Removed box shadows and borders from containers for cleaner design
- Enhanced Dark Mode template with:
  - Dark container backgrounds (#2d3748)
  - Dark input fields (#1a202c) with proper borders and white text
  - White text on hover for answer options
  - Dark themed skip buttons with hover effects