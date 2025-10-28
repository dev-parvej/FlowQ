# Templates Requirements

## Overview
Add a template system that allows admins to choose from predefined survey templates. Future updates will allow custom template creation and editing.

## Template Features

### Default Templates (Phase 1 - Current)
Provide a selection of pre-built, fixed templates that users can choose from:
- Multiple professional template designs
- Templates control the visual appearance and styling of surveys
- Each template has a unique identifier and name
- Templates are marked with `isDefault: true` flag

### Template Properties
Each template should include:
- `id`: Unique template identifier (integer or string)
- `name`: Display name for the template
- `description`: Brief description of the template style/purpose
- `isDefault`: Boolean flag (true for system templates, false for custom templates in future)
- `preview_image`: Path to template preview/thumbnail image
- `styles`: CSS/styling configuration for the template
- `created_at`: Timestamp
- `updated_at`: Timestamp

### Initial Default Templates
Create at least 3-5 default templates:
1. **Classic** - Traditional form-style survey
2. **Modern** - Clean, minimalist design
3. **Card-based** - Each question displayed as a card
4. **Dark Mode** - Dark theme template
5. **Colorful** - Vibrant, engaging design

## Admin Interface

### Template Selection Page
- Display all available templates in a grid/card layout
- Each template card shows:
  - Preview image/thumbnail
  - Template name
  - Template description
  - "Select" or "Active" button/indicator
- Currently active template should be visually indicated
- Filter/search functionality (optional for future)

### Template Actions (Phase 1)
- **Select Template**: Apply a template to the survey
- **Preview Template**: View template styling before applying (optional)

### Template Actions (Phase 2 - Future)
- **Create Custom Template**: Allow users to create new templates
- **Edit Template**: Modify custom templates (system templates remain locked)
- **Delete Template**: Remove custom templates only
- **Duplicate Template**: Clone existing template as starting point

## Database Schema

### New Table: `flowq_templates`
```sql
CREATE TABLE flowq_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_default TINYINT(1) DEFAULT 0,
    preview_image VARCHAR(500),
    styles LONGTEXT, -- JSON or serialized CSS configuration
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Global Template Setting
- Template is applied globally to all surveys (not per-survey)
- Store active template ID in WordPress options table: `flowq_active_template`
- Default: 1 (Classic template)

## Technical Requirements

### Template Storage
- System default templates: Inserted via database migration on plugin activation
- Template styles stored as JSON or serialized PHP array
- Preview images stored in plugin assets directory

### Template Application
- Template is global and applies to all surveys
- Frontend renders all surveys using the active global template's styles
- Fallback to default Classic template (ID: 1) if active template is deleted

### Migration Strategy
- Create migration to add `flowq_templates` table
- Seed default templates
- Set global active template option to default "Classic" template (ID: 1)

### Security
- Default templates cannot be deleted or modified
- Capability check: `manage_options` for template selection
- Nonce verification for all template-related actions
- Sanitize and validate all template data

### Future Extensibility
- Template system should support custom templates without major refactoring
- Template editor interface will be added in future update
- Export/import template functionality (future consideration)

## UI/UX Considerations
- Visual preview is critical for template selection
- Clear indication of which template is currently active
- "Default" badge on system templates
- Disabled delete/edit buttons for default templates
- Confirmation dialog before switching templates (warns about potential style changes)

## Implementation Priority
1. Database schema and migrations
2. Seed default templates with basic styles
3. Settings page with Templates tab
4. Template selection interface
5. Apply template to survey frontend
6. Template preview functionality (optional)
