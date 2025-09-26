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
- Answer reordering functionality

### Database Schema
Tables created:
- `wp_dynamic_survey_surveys`
- `wp_dynamic_survey_questions`
- `wp_dynamic_survey_answers`
- `wp_dynamic_survey_responses`
- `wp_dynamic_survey_participants`
- `wp_dynamic_survey_sessions`

### Admin Interface
- Survey management pages
- Question builder with AJAX operations
- Answer options management
- Question duplication
- Analytics and participant tracking
- Shortcode builder

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