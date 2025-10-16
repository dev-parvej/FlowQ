# Survey Header and Subtitle Feature

## Overview
Add a toggle setting to each survey to control the display of a custom header and subtitle on the participant form. This is a **per-survey setting** that allows each survey to have its own custom header text. This replaces the current behavior where the survey title is always displayed.

## Feature Requirements

### 1. Toggle Setting (Per Survey)
**Field Name:** Show Header and Subtitle
- **Type:** Boolean (checkbox)
- **Default Value:** `0` (disabled)
- **Location:** Survey settings/edit page in admin
- **Label:** "Show Custom Header and Subtitle"
- **Description:** "Display a custom header and subtitle at the top of the participant form instead of the survey title"
- **Storage:** `wp_dynamic_survey_surveys` table

### 2. Survey Form Header Field (Per Survey)
**Field Name:** Survey Form Header
- **Type:** Text input (VARCHAR 255)
- **Default Value:** Empty string
- **Required:** Yes (when toggle is enabled)
- **Label:** "Survey Form Header"
- **Description:** "Main heading displayed at the top of the participant form"
- **Visibility:** Only shown when toggle is enabled
- **Validation:** Required field validation when toggle is enabled
- **Storage:** `wp_dynamic_survey_surveys` table

### 3. Survey Form Subtitle Field (Per Survey)
**Field Name:** Survey Form Subtitle
- **Type:** Textarea (TEXT)
- **Default Value:** Empty string
- **Required:** No (optional)
- **Label:** "Survey Form Subtitle"
- **Description:** "Subtitle displayed below the header (optional)"
- **Visibility:** Only shown when toggle is enabled
- **Storage:** `wp_dynamic_survey_surveys` table

## Current Behavior vs New Behavior

### Current Behavior
- Survey title (from `wp_dynamic_survey_surveys.title`) is always displayed at the top of the participant form
- No option to hide or customize the header
- No subtitle support

### New Behavior

#### When Toggle is OFF (Default)
- Nothing is displayed at the top of the participant form
- Current survey title is hidden
- Clean minimal header area
- This is the default for all existing and new surveys

#### When Toggle is ON
- Display custom "Survey Form Header" at the top of the participant form
- Display custom "Survey Form Subtitle" below the header (if provided)
- Survey title from `wp_dynamic_survey_surveys.title` is NOT displayed
- Header field is required; subtitle is optional

## Database Schema Changes

### Table: `wp_dynamic_survey_surveys`

Add three new columns:

```sql
ALTER TABLE wp_dynamic_survey_surveys
ADD COLUMN show_header TINYINT(1) DEFAULT 0 AFTER thank_you_message,
ADD COLUMN form_header VARCHAR(255) DEFAULT '' AFTER show_header,
ADD COLUMN form_subtitle TEXT DEFAULT '' AFTER form_header;
```

**Column Details:**
- `show_header` - TINYINT(1), DEFAULT 0, stores toggle state (0 = off, 1 = on)
- `form_header` - VARCHAR(255), DEFAULT '', stores header text
- `form_subtitle` - TEXT, DEFAULT '', stores subtitle text

## Implementation Details

### Admin UI Location
Add to Survey Edit/Create page (where survey title, description, etc. are configured):

**Placement:** Add new section after survey description/settings

**Form Fields:**
1. **Checkbox:** Show Custom Header and Subtitle
   - Help text: "Display a custom header and subtitle on the participant form instead of the survey title"

2. **Text Input:** Survey Form Header (conditionally visible)
   - Shows when checkbox is checked
   - Required field (add asterisk)
   - Placeholder: "e.g., Help Us Improve Your Experience"

3. **Textarea:** Survey Form Subtitle (conditionally visible)
   - Shows when checkbox is checked
   - Optional field
   - Rows: 3
   - Placeholder: "e.g., Your feedback matters! Take 2 minutes to share your thoughts."

### JavaScript Dependencies
**File:** `assets/js/admin.js` or survey edit page JS

**Functionality:**
- Show/hide header and subtitle fields based on toggle checkbox
- Add required field validation for header when toggle is enabled
- Prevent form submission if toggle is enabled but header is empty
- Display client-side error message: "Survey Form Header is required when Show Custom Header is enabled"

**Implementation:**
```javascript
// Listen for toggle checkbox change
$('#show_header_checkbox').on('change', function() {
    if ($(this).is(':checked')) {
        $('#header_fields_container').show();
        $('#form_header_input').attr('required', true);
    } else {
        $('#header_fields_container').hide();
        $('#form_header_input').attr('required', false);
    }
});

// On page load, check initial state
if ($('#show_header_checkbox').is(':checked')) {
    $('#header_fields_container').show();
    $('#form_header_input').attr('required', true);
} else {
    $('#header_fields_container').hide();
}

// Form submission validation
$('#survey_form').on('submit', function(e) {
    if ($('#show_header_checkbox').is(':checked') && $('#form_header_input').val().trim() === '') {
        e.preventDefault();
        alert('Survey Form Header is required when Show Custom Header is enabled');
        $('#form_header_input').focus();
        return false;
    }
});
```

### Backend Save Logic
**File:** Survey manager class (likely `includes/class-manager.php` or similar)

**Validation:**
- When `show_header` is enabled (1), validate that `form_header` is not empty
- Sanitize `form_header` with `sanitize_text_field()`
- Sanitize `form_subtitle` with `sanitize_textarea_field()`
- Return error if validation fails: "Survey Form Header is required when Show Custom Header is enabled"

**Save Logic:**
```php
// In survey save/update method
$show_header = isset($_POST['show_header']) ? 1 : 0;
$form_header = sanitize_text_field($_POST['form_header']);
$form_subtitle = sanitize_textarea_field($_POST['form_subtitle']);

// Validation
if ($show_header && empty($form_header)) {
    return new WP_Error('required_field', __('Survey Form Header is required when Show Custom Header is enabled', 'wp-dynamic-survey'));
}

// Save to database
$wpdb->update(
    $wpdb->prefix . 'dynamic_survey_surveys',
    array(
        'show_header' => $show_header,
        'form_header' => $form_header,
        'form_subtitle' => $form_subtitle
    ),
    array('id' => $survey_id)
);
```

### Frontend Display Logic
**File:** `public/templates/participant-form.php`

**Location:** Add at the very top of the form, before any form fields

**Display Logic:**
```php
<?php
// Get survey data
$show_header = (int) $survey->show_header;
$form_header = $survey->form_header;
$form_subtitle = $survey->form_subtitle;

// Only display if toggle is enabled
if ($show_header && !empty($form_header)): ?>
    <div class="survey-header-section">
        <h1 class="survey-form-header"><?php echo esc_html($form_header); ?></h1>
        <?php if (!empty($form_subtitle)): ?>
            <p class="survey-form-subtitle"><?php echo esc_html($form_subtitle); ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>
```

**Important:** Do NOT display `$survey->title` anywhere in the participant form. The title is only for admin reference.

### CSS Styling Requirements
**File:** `assets/css/frontend.css`

**Styles:**
```css
/* Survey Header Section */
.survey-header-section {
    margin-bottom: 2rem;
    text-align: center;
}

.survey-form-header {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    line-height: 1.2;
}

.survey-form-subtitle {
    font-size: 1.125rem;
    opacity: 0.85;
    margin-bottom: 0;
    line-height: 1.5;
}

/* Responsive design */
@media (max-width: 768px) {
    .survey-form-header {
        font-size: 1.5rem;
    }

    .survey-form-subtitle {
        font-size: 1rem;
    }
}
```

### Template Integration
The header and subtitle should be styled according to the active template:

**Dynamic CSS Integration:**
- Use `text_color` from template styles for header and subtitle
- Apply template's `font_family` to header and subtitle
- Header and subtitle should inherit container's text color automatically
- Maintain consistent spacing with form elements
- Support all 5 default templates (Classic, Modern, Card-based, Dark Mode, Colorful)

**Template Handler Updates (if needed):**
- If additional template-specific styles are needed, add to `includes/class-template-handler.php`
- Ensure header and subtitle colors adapt to template (especially Dark Mode)

## Use Cases

### Use Case 1: Marketing Survey
**Survey 1:**
- Toggle: ON
- Header: "Help Us Improve Your Experience"
- Subtitle: "Your feedback matters! Take 2 minutes to share your thoughts."

**Survey 2:**
- Toggle: OFF
- Result: No header displayed, clean minimal form

### Use Case 2: Product Feedback
- Toggle: ON
- Header: "Product Satisfaction Survey"
- Subtitle: "" (empty - no subtitle needed)

### Use Case 3: Multiple Surveys on Same Site
Each survey can have different header settings:
- Survey A: Custom header enabled with unique text
- Survey B: Header disabled for minimal design
- Survey C: Custom header with different text than Survey A

## Validation Rules

### Admin Side
1. If toggle is enabled, header field must not be empty
2. Display error message if save is attempted with empty header when toggle is on
3. Subtitle field is always optional (no validation needed)
4. Error message: "Survey Form Header is required when Show Custom Header is enabled"

### Frontend Side
No validation needed - display logic only

## Migration Requirements

### Database Migration
**File:** `includes/class-db-migrator.php`

**Migration Function:**
```php
public function migrate_survey_header_fields() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dynamic_survey_surveys';

    // Check if columns already exist
    $columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'show_header'");

    if (empty($columns)) {
        $wpdb->query("
            ALTER TABLE {$table_name}
            ADD COLUMN show_header TINYINT(1) DEFAULT 0 AFTER thank_you_message,
            ADD COLUMN form_header VARCHAR(255) DEFAULT '' AFTER show_header,
            ADD COLUMN form_subtitle TEXT DEFAULT '' AFTER form_header
        ");
    }
}
```

**Run Migration:**
- Add to plugin activation hook
- Or add to database version check in migrator class
- Increment database version number

### Backward Compatibility
- Default values ensure existing surveys continue to work without showing header (toggle defaults to OFF)
- Existing surveys will have `show_header = 0`, so no header is displayed
- Admin can opt-in per survey to enable custom headers

## Testing Checklist

### Admin Testing
- [ ] Toggle checkbox shows/hides header and subtitle fields correctly
- [ ] Required validation works for header field when toggle is enabled
- [ ] Can save survey with toggle OFF
- [ ] Can save survey with toggle ON + header filled
- [ ] Cannot save survey with toggle ON + header empty (shows error)
- [ ] Subtitle can be saved as empty or with content
- [ ] Existing surveys load correctly with new fields (defaults applied)
- [ ] Fields persist correctly after save and page reload

### Frontend Testing
- [ ] When toggle OFF: No header or subtitle displayed
- [ ] When toggle OFF: Survey title is not displayed anywhere
- [ ] When toggle ON + header only: Header displays correctly
- [ ] When toggle ON + header and subtitle: Both display correctly
- [ ] Header and subtitle respect template styling (all 5 templates)
- [ ] Responsive design works on mobile devices
- [ ] Header/subtitle don't break form layout
- [ ] Text wraps correctly on smaller screens
- [ ] Special characters display correctly (quotes, apostrophes, etc.)

### Integration Testing
- [ ] Works with two-stage forms
- [ ] Works with single-stage forms
- [ ] Works with all template themes
- [ ] Privacy policy still displays correctly below header
- [ ] Form validation still works normally
- [ ] Multiple surveys on same page each show correct header
- [ ] Shortcode rendering works correctly with header enabled/disabled

### Database Testing
- [ ] Migration runs successfully on plugin update
- [ ] New columns added correctly to surveys table
- [ ] Default values applied to existing records
- [ ] Data saves correctly to new columns
- [ ] Database rollback doesn't break plugin (columns can be NULL)

## Files to Modify

### Database Files
1. `includes/class-db-migrator.php` - Add migration for new columns

### Admin Files
1. Survey edit/create page template - Add new form fields
2. Survey manager class (`includes/class-manager.php` or similar) - Add save/validation logic
3. `assets/js/admin.js` - Add toggle show/hide logic and validation

### Frontend Files
1. `public/templates/participant-form.php` - Add header/subtitle display logic
2. `assets/css/frontend.css` - Add styling for header and subtitle

### Template Files (if needed)
1. `includes/class-template-handler.php` - Ensure template styles apply to header/subtitle

## Priority
Medium - Enhances user experience and provides per-survey customization options

## Dependencies
- Requires existing survey management system
- Works with existing template system
- Compatible with current participant form structure
- Requires database migration on plugin update

## Notes
- This is a per-survey setting, not a global setting
- Each survey can have different header configuration
- Survey title field remains for admin reference only
- When header toggle is OFF, nothing is displayed (not even survey title)
- Header and subtitle text should be plain text only (no HTML support needed)
