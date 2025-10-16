# General Settings Requirements

## Overview
Add a General Settings tab to the Settings page where administrators can configure global plugin behavior and form options.

## Tab Navigation
- General tab should be the **first/default tab** in Settings
- When users navigate to Settings (click on Settings menu), General tab loads by default
- Tab order: **General** → Templates → (future tabs)

## Settings Options

### 1. Two-Stage Form Toggle
**Setting Name**: Enable Two-Stage Form
**Field Type**: Checkbox
**Default Value**: Checked (enabled)

**Behavior**:
- **When Enabled (checked - default)**:
  - Participant form displays in two stages
  - **Stage 1**: Collect name, email, address, zipcode
  - **Stage 2**: Collect phone number
  - Current behavior (already implemented)

- **When Disabled (unchecked)**:
  - Participant form displays all fields in one combined form
  - All fields shown together: name, email, address, zipcode, phone number
  - Single submission before starting survey questions

**Technical Details**:
- Store setting in WordPress options: `wp_dynamic_survey_two_stage_form`
- Default value: `1` (enabled)
- Frontend form (`public/class-frontend.php` or participant form handler) should check this setting
- Conditionally render form as single-stage or two-stage based on setting value

### 2. Two-Page Survey Form
**Setting Name**: Enable Two-Page Survey Form
**Field Type**: Checkbox
**Default Value**: Unchecked (disabled)

**Behavior**:
- **When Enabled (checked)**:
  - Participant form (info collection) appears on first page
  - Survey questions appear on a separate second page
  - After submitting participant info, user is redirected to second page URL
  - Survey continues seamlessly using session tracking

- **When Disabled (unchecked - default)**:
  - Current behavior: participant form and survey questions on same page
  - No redirect between info collection and questions

**User Flow When Enabled**:
1. Admin adds survey shortcode to **Page 1** (e.g., `/survey-start`)
2. Admin adds same survey shortcode to **Page 2** (e.g., `/survey-questions`)
3. Admin configures "Second Page URL" in survey settings (points to Page 2)
4. Participant visits Page 1, fills out info form, submits
5. System redirects to Page 2 URL
6. Page 2 detects existing session and shows survey questions
7. Participant completes survey on Page 2

**Survey-Level Configuration**:
- Add new field to survey settings: "Second Page URL" (text input)
- Only visible/required when "Enable Two-Page Survey Form" is enabled globally
- Field label: "Survey Questions Page URL"
- Help text: "Enter the full URL where survey questions will be displayed (must contain the same survey shortcode)"
- Validation: Must be valid URL format

**Technical Details**:
- Global setting option: `wp_dynamic_survey_two_page_mode`
- Default value: `0` (disabled)
- Survey-level setting: Store in `wp_dynamic_survey_surveys` table
  - Add new column: `second_page_url` (VARCHAR 255, nullable)
  - Database migration required
- Session tracking: Use existing session system to maintain state across pages
- Redirect after info submission: Use `wp_redirect()` to second page URL
- Page detection: Check if session exists and info already submitted
  - If yes: Skip info form, show questions directly
  - If no: Show info form

### 3. Allow Multiple Submissions with Same Email
**Setting Name**: Allow Multiple Submissions with Same Email
**Field Type**: Checkbox
**Default Value**: Unchecked (disabled)

**Behavior**:
- **When Enabled (checked)**:
  - Users can submit the same survey multiple times using the same email address
  - No email uniqueness validation is performed
  - Each submission creates a new participant record and response

- **When Disabled (unchecked - default)**:
  - Email addresses must be unique per survey
  - Before allowing survey start, check if email already exists in `wp_dynamic_survey_participants` for this survey
  - If email exists: Display error message "You have already submitted this survey with this email address"
  - Prevent duplicate submissions from same email

**Technical Details**:
- Global setting option: `wp_dynamic_survey_allow_duplicate_emails`
- Default value: `0` (disabled)
- Validation logic: Check before creating participant record
  - Query: `SELECT COUNT(*) FROM wp_dynamic_survey_participants WHERE survey_id = ? AND email = ?`
  - If count > 0 and setting disabled: Block submission
  - If count > 0 and setting enabled: Allow submission
- Error handling: Display user-friendly message when blocked

**Use Cases**:
- **Disabled (default)**: One response per person, prevents duplicate/spam submissions
- **Enabled**: Allows repeat surveys, user testing, or multiple submissions from same user over time

### 4. Participant Information Fields
**Setting Name**: Participant Information Fields
**Field Type**: Checkboxes (multiple)
**Default Values**: All checked (enabled)

**Fields**:
- **Name** - Always required, cannot be disabled (no checkbox)
- **Email** - Always required, cannot be disabled (no checkbox)
- **Address** - Optional, checkbox to enable/disable
- **Zipcode** - Optional, checkbox to enable/disable
- **Phone Number** - Optional, checkbox to enable/disable

**Behavior**:
- **Name & Email**: Always displayed and required (non-configurable)
- **Address**: When checked, field appears in participant form; when unchecked, field is hidden
- **Zipcode**: When checked, field appears in participant form; when unchecked, field is hidden
- **Phone Number**: When checked, field appears in participant form; when unchecked, field is hidden

**Interaction with Two-Stage Form Setting**:
- If **Phone Number is unchecked**:
  - "Enable two-stage participant form" (Setting 1) should be **automatically unchecked and disabled**
  - Two-stage form becomes impossible since there's nothing to show in Stage 2
  - All enabled fields (name, email, address*, zipcode*) appear in single form
  - User proceeds directly to survey questions after submitting info

- If **Phone Number is checked**:
  - "Enable two-stage participant form" checkbox becomes **enabled/clickable**
  - Admin can choose to enable or disable two-stage form
  - If two-stage form enabled: Stage 1 (name, email, address*, zipcode*), Stage 2 (phone)
  - If two-stage form disabled: All fields in one form including phone

**Technical Details**:
- Global settings options:
  - `wp_dynamic_survey_field_address` - Default: `1`
  - `wp_dynamic_survey_field_zipcode` - Default: `1`
  - `wp_dynamic_survey_field_phone` - Default: `1`
- Frontend form rendering checks these options to determine which fields to display
- JavaScript/jQuery on settings page: Monitor phone number checkbox
  - When unchecked: Disable and uncheck two-stage form checkbox
  - When checked: Enable two-stage form checkbox
- Database: Only save enabled fields to `wp_dynamic_survey_participants` table
- Validation: Only validate fields that are enabled

### 5. Privacy Policy Text
**Setting Name**: Privacy Policy
**Field Type**: Rich Text Editor(s) with basic formatting
**Default Value**: Empty

**Behavior**:
- **When Two-Stage Form is Disabled**:
  - Show **one** rich text editor: "Privacy Policy"
  - This single privacy policy text displays with a required checkbox below the participant form
  - User must check the checkbox to submit the form

- **When Two-Stage Form is Enabled**:
  - Show **two** rich text editors:
    1. "Privacy Policy - Stage 1" (for name, email, address, zipcode)
    2. "Privacy Policy - Stage 2 (Phone Number)" (for phone number field)
  - Each privacy policy has its own required checkbox
  - Stage 1: User must check checkbox to continue to Stage 2
  - Stage 2: User must check checkbox to submit survey

**UI Display on Frontend**:
- Privacy policy text appears below form fields, above the submit/continue button
- Rendered as HTML (supports basic formatting: bold, italic, links, lists, paragraphs)
- Checkbox appears below the privacy policy text
- Checkbox label: "I agree to the privacy policy" (or customizable)
- Checkbox is **required** - form cannot be submitted without checking
- Client-side validation: Show error if user tries to submit without checking
- Responsive design, readable on mobile

**Rich Text Editor Features**:
- Basic formatting: Bold, Italic, Underline
- Links: Allow hyperlinks (for full privacy policy page)
- Lists: Bulleted and numbered lists
- Paragraphs and line breaks
- Use WordPress TinyMCE or wp_editor()

**Interaction with Two-Stage Form Setting**:
- JavaScript on settings page monitors "Enable two-stage participant form" checkbox
- When **Two-Stage Form is Checked**:
  - Hide single "Privacy Policy" editor
  - Show two separate editors: "Privacy Policy - Stage 1" and "Privacy Policy - Stage 2 (Phone Number)"
- When **Two-Stage Form is Unchecked**:
  - Hide both stage-specific editors
  - Show single "Privacy Policy" editor

**Technical Details**:
- Global settings options:
  - `wp_dynamic_survey_privacy_policy` - Single privacy policy text (HTML)
  - `wp_dynamic_survey_privacy_policy_stage1` - Stage 1 privacy policy text (HTML)
  - `wp_dynamic_survey_privacy_policy_stage2` - Stage 2 privacy policy text (HTML)
- Use `wp_editor()` for rich text editing in admin
- Sanitize HTML on save using `wp_kses_post()` to allow safe HTML tags
- Frontend:
  - Display privacy policy text using `wp_kses_post()`
  - Render checkbox with required attribute
  - Validate checkbox is checked before form submission
  - JavaScript validation with error message: "You must agree to the privacy policy"
- Default values: Empty strings (privacy policy is optional - if empty, no checkbox shown)
- Only show privacy policy and checkbox if text is not empty

### 6. Optional Phone Number Stage
**Setting Name**: Make Phone Number Optional (Stage 2)
**Field Type**: Checkbox
**Default Value**: Unchecked (disabled)

**Behavior**:
- **Only visible when "Enable two-stage participant form" is checked**
- Hidden/disabled when two-stage form is unchecked (no relevance)

- **When Enabled (checked)**:
  - Stage 2 (phone number) becomes optional
  - User can skip the phone number stage entirely
  - Display "Skip" button on Stage 2 form alongside "Submit" button
  - Clicking "Skip" proceeds to survey questions without collecting phone number
  - Phone number field is NOT required (no validation)
  - Database: Store NULL for phone number if skipped

- **When Disabled (unchecked - default)**:
  - Stage 2 (phone number) is required
  - User must enter phone number to proceed
  - No "Skip" button shown
  - Phone number field has required validation
  - Cannot proceed to survey without providing phone number

**UI Display on Frontend (Stage 2 when enabled)**:
- Phone number input field present but not required
- Two buttons displayed:
  1. "Submit" button - Validates and saves phone number if entered, proceeds to survey
  2. "Skip" button - Skips phone collection, proceeds directly to survey questions
- If user enters phone number and clicks "Submit": Save phone to database
- If user clicks "Skip": Do not save phone (store NULL), proceed to survey

**Interaction with Two-Stage Form Setting**:
- JavaScript monitors "Enable two-stage participant form" checkbox
- When two-stage form is **unchecked**:
  - Hide "Make Phone Number Optional" checkbox
  - Set it to unchecked (reset to default)
- When two-stage form is **checked**:
  - Show "Make Phone Number Optional" checkbox
  - Allow admin to enable/disable

**Technical Details**:
- Global setting option: `wp_dynamic_survey_phone_optional`
- Default value: `0` (disabled - phone required)
- Frontend Stage 2 logic:
  - Check `wp_dynamic_survey_phone_optional`
  - If enabled: Show "Skip" button, remove required validation from phone field
  - If disabled: Hide "Skip" button, add required validation to phone field
- Skip button handler: Proceed to survey questions without saving phone number
- Database: Phone column accepts NULL values (already nullable)

## UI/UX Requirements

### General Tab Interface
- Clean form layout using WordPress admin form components
- Section heading: "Form Settings"

**Setting 1 - Two-Stage Form**:
- Checkbox with label: "Enable two-stage participant form"
- Help text below checkbox: "When enabled, participants will provide basic information first (name, email, address, zipcode), then phone number in a second step. When disabled, all fields appear together."

**Setting 2 - Two-Page Survey**:
- Checkbox with label: "Enable two-page survey form"
- Help text below checkbox: "When enabled, participant information and survey questions will be displayed on separate pages. You'll need to add the survey shortcode to both pages and configure the second page URL in each survey's settings."

**Setting 3 - Multiple Submissions**:
- Checkbox with label: "Allow multiple submissions with same email"
- Help text below checkbox: "When enabled, users can submit the same survey multiple times using the same email address. When disabled, each email can only submit once per survey."

**Setting 4 - Participant Information Fields**:
- Section sub-heading: "Participant Information Fields"
- Static text: "Name and Email are always required"
- Checkbox with label: "Collect Address"
- Checkbox with label: "Collect Zipcode"
- Checkbox with label: "Collect Phone Number"

**Field Interactions**:
- When "Collect Phone Number" is unchecked:
  - JavaScript automatically unchecks "Enable two-stage participant form"
  - "Enable two-stage participant form" becomes disabled (grayed out, not clickable)
- When "Collect Phone Number" is checked:
  - "Enable two-stage participant form" becomes enabled (user can check/uncheck it)

**Setting 5 - Privacy Policy**:
- Section sub-heading: "Privacy Policy Settings"

**When Two-Stage Form is Disabled**:
- Show one rich text editor with label: "Privacy Policy"
- Help text: "This text will appear below the participant form with a required checkbox. Leave empty to disable."

**When Two-Stage Form is Enabled**:
- Show two rich text editors:
  1. Label: "Privacy Policy - Stage 1"
     - Help text: "Appears on first form (name, email, address, zipcode) with required checkbox"
  2. Label: "Privacy Policy - Stage 2 (Phone Number)"
     - Help text: "Appears on second form (phone number) with required checkbox"

**Editor Toggling**:
- JavaScript monitors "Enable two-stage participant form" checkbox state
- Dynamically shows/hides appropriate privacy policy editor(s)

**Setting 6 - Optional Phone Number**:
- Checkbox with label: "Make phone number optional (Stage 2)"
- Help text: "When enabled, users can skip the phone number stage. Only applies when two-stage form is enabled."
- **Visibility**: Only shown when "Enable two-stage participant form" is checked
- **Hidden**: When two-stage form is unchecked

**Field Interactions**:
- When "Enable two-stage participant form" is unchecked:
  - Hide "Make phone number optional" checkbox
- When "Enable two-stage participant form" is checked:
  - Show "Make phone number optional" checkbox

- Save button at bottom: "Save Changes"
- Success/error notices after save

### Permissions
- Only users with `manage_options` capability can access and modify
- WordPress nonce verification required for form submission

### Form Submission
- AJAX-based save (optional) or standard form POST
- Display success message: "Settings saved successfully"
- Display error message if save fails

## Technical Implementation

### Storage

**Global Settings (WordPress Options)**:
1. `wp_dynamic_survey_two_stage_form`
   - Value: `1` (enabled) or `0` (disabled)
   - Default: `1`

2. `wp_dynamic_survey_two_page_mode`
   - Value: `1` (enabled) or `0` (disabled)
   - Default: `0`

3. `wp_dynamic_survey_allow_duplicate_emails`
   - Value: `1` (enabled) or `0` (disabled)
   - Default: `0`

4. `wp_dynamic_survey_field_address`
   - Value: `1` (enabled) or `0` (disabled)
   - Default: `1`

5. `wp_dynamic_survey_field_zipcode`
   - Value: `1` (enabled) or `0` (disabled)
   - Default: `1`

6. `wp_dynamic_survey_field_phone`
   - Value: `1` (enabled) or `0` (disabled)
   - Default: `1`

7. `wp_dynamic_survey_privacy_policy`
   - Value: HTML string
   - Default: Empty string

8. `wp_dynamic_survey_privacy_policy_stage1`
   - Value: HTML string
   - Default: Empty string

9. `wp_dynamic_survey_privacy_policy_stage2`
   - Value: HTML string
   - Default: Empty string

10. `wp_dynamic_survey_phone_optional`
    - Value: `1` (enabled) or `0` (disabled)
    - Default: `0`

- Use `update_option()` and `get_option()` WordPress functions

**Survey-Level Settings (Database Table)**:
- Table: `wp_dynamic_survey_surveys`
- New column: `second_page_url` (VARCHAR 255, NULL)
- Stored per survey, only used when two-page mode is globally enabled

### Frontend Integration

**Two-Stage Form**:
- Modify participant form rendering logic to check `wp_dynamic_survey_two_stage_form`
- If enabled: show two-stage form (current behavior)
- If disabled: combine all fields into single form stage

**Two-Page Survey**:
- Check `wp_dynamic_survey_two_page_mode` global setting
- If enabled:
  - After participant info submission, redirect to `second_page_url` from survey settings
  - On page load, check for existing session
  - If session exists with participant info: Show survey questions only
  - If no session: Show participant info form
- If disabled: Current single-page behavior

**Multiple Submissions with Same Email**:
- Check `wp_dynamic_survey_allow_duplicate_emails` before creating participant
- If disabled (default):
  - Query database for existing participant with same email and survey_id
  - If found: Return error, prevent form submission
  - Display error message: "You have already submitted this survey with this email address"
- If enabled: Skip validation, allow duplicate email submissions
- Implement check in participant creation logic (before INSERT)

**Participant Information Fields**:
- Check field settings before rendering participant form:
  - `wp_dynamic_survey_field_address` - Show/hide address field
  - `wp_dynamic_survey_field_zipcode` - Show/hide zipcode field
  - `wp_dynamic_survey_field_phone` - Show/hide phone number field
- Name and email always shown (hardcoded, no option check needed)
- Only validate and save fields that are enabled
- Database columns remain unchanged (all fields still exist in `wp_dynamic_survey_participants`)
- Store NULL or empty string for disabled fields
- Frontend JavaScript validation: Only validate enabled fields

**Privacy Policy**:
- Check two-stage form setting to determine which privacy policy to display
- If two-stage form disabled:
  - Get `wp_dynamic_survey_privacy_policy`
  - If not empty: Display privacy policy text + required checkbox below form
  - Checkbox label: "I agree to the privacy policy"
  - Validate checkbox is checked before form submission
- If two-stage form enabled:
  - Stage 1: Get `wp_dynamic_survey_privacy_policy_stage1`
    - If not empty: Display + required checkbox
    - Validate before allowing continue to Stage 2
  - Stage 2: Get `wp_dynamic_survey_privacy_policy_stage2`
    - If not empty: Display + required checkbox
    - Validate before allowing survey submission
- Frontend validation: JavaScript prevents form submission if checkbox unchecked
- Error message: "You must agree to the privacy policy to continue"

**Optional Phone Number Stage**:
- Only applies when two-stage form is enabled
- Check `wp_dynamic_survey_phone_optional` setting
- If enabled (phone optional):
  - Stage 2 phone number field: Remove required attribute
  - Display two buttons: "Submit" and "Skip"
  - "Submit" button: Validate phone if entered, save to database, proceed to survey
  - "Skip" button: Skip phone collection entirely, proceed to survey with phone = NULL
  - No validation error if phone field is empty
- If disabled (phone required - default):
  - Stage 2 phone number field: Add required attribute
  - Display only "Submit" button (no "Skip" button)
  - Validate phone number is entered before proceeding
  - Cannot proceed without entering phone number
- Button styling: "Skip" button should be secondary/text style (less prominent than "Submit")

### Database Migration
- Create migration to add `second_page_url` column to `wp_dynamic_survey_surveys` table
- Migration class: `includes/class-db-migrator.php`
- SQL: `ALTER TABLE wp_dynamic_survey_surveys ADD COLUMN second_page_url VARCHAR(255) NULL AFTER thank_you_message;`

### Code Locations
- Settings admin class: `admin/class-settings-admin.php`
  - Add General tab as default tab
  - Render all 4 settings
  - Add JavaScript for phone number/two-stage form interaction
- Frontend form handler: `public/class-frontend.php` or relevant participant form file
  - Check field settings to conditionally render form fields
  - Check two-stage form setting for multi-step logic
  - Check two-page mode for redirect logic
  - Check duplicate email setting for validation
- Survey admin: Add "Second Page URL" field to survey edit page
- DB Migrator: `includes/class-db-migrator.php`

### JavaScript Requirements (Settings Page)

**Phone Number / Two-Stage Form Interaction**:
- Listen for changes on "Collect Phone Number" checkbox
- When unchecked:
  - Set "Enable two-stage participant form" to unchecked
  - Add `disabled` attribute to "Enable two-stage participant form"
- When checked:
  - Remove `disabled` attribute from "Enable two-stage participant form"
- On page load: Check initial state and apply logic accordingly

**Privacy Policy Editor Toggling**:
- Listen for changes on "Enable two-stage participant form" checkbox
- When checked (two-stage enabled):
  - Hide single privacy policy editor (`wp_dynamic_survey_privacy_policy`)
  - Show two stage-specific editors (`wp_dynamic_survey_privacy_policy_stage1` and `wp_dynamic_survey_privacy_policy_stage2`)
- When unchecked (two-stage disabled):
  - Hide both stage-specific editors
  - Show single privacy policy editor
- On page load: Check initial state and show appropriate editor(s)
- Use jQuery `show()` / `hide()` or add/remove CSS classes for visibility

**Optional Phone Number Setting Visibility**:
- Listen for changes on "Enable two-stage participant form" checkbox
- When checked (two-stage enabled):
  - Show "Make phone number optional (Stage 2)" checkbox
- When unchecked (two-stage disabled):
  - Hide "Make phone number optional (Stage 2)" checkbox
  - Optionally reset it to unchecked
- On page load: Check initial two-stage form state and show/hide accordingly

## Future Extensibility
- Additional settings will be added to General tab in future updates
- Keep form structure modular to easily add new settings fields
- Group related settings under section headings

## Internationalization
- All text strings must be translatable using `__()` or `_e()` with text domain `'wp-dynamic-survey'`
