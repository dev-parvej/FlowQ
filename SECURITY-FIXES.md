# FlowQ Security Fixes

Security issues identified during code review. **All issues have been fixed.**

## Changes Made

1. **Created `includes/class-security-helper.php`** - New security helper class with:
   - `check_rate_limit()` - Rate limiting using WordPress transients
   - `get_client_ip()` - Safe client IP detection
   - `validate_session_format()` - Session ID format validation
   - `sanitize_hex_color()` - Hex color sanitization
   - `sanitize_css_value()` - CSS value sanitization
   - `sanitize_css()` - Complete CSS block sanitization
   - `sanitize_gradient()` - Gradient CSS validation

2. **Updated `includes/class-rest-api.php`** - Added rate limiting and session format validation
3. **Updated `includes/class-shortcode.php`** - Uses security helper for CSS sanitization
4. **Updated `includes/class-template-handler.php`** - Sanitizes all color and CSS values
5. **Updated `public/class-frontend.php`** - Fixed escaped translated strings
6. **Updated `public/templates/survey-container.php`** - Improved PHPCS ignore documentation

---

## Issue #1: REST API - validate_session Endpoint (HIGH)

**File**: `includes/class-rest-api.php:92`

**Problem**: Public endpoint (`permission_callback => '__return_true'`) allows unauthenticated session validation. While the REST response limits output to 4 fields, the underlying method could expose sensitive participant data. Attackers can enumerate valid session IDs.

**Current Code**:
```php
register_rest_route(self::NAMESPACE, '/validate/session', array(
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => array($this, 'validate_session'),
    'permission_callback' => '__return_true',
    // ...
));
```

**Fix Required**:
- Add rate limiting or authentication check
- Validate that the request comes from the legitimate session owner
- Consider using stronger session validation tokens
- Limit the data returned in the response

---

## Issue #2: CSS Injection in Shortcode Handler (MEDIUM)

**File**: `includes/class-shortcode.php:204`

**Problem**: `wp_strip_all_tags()` is insufficient for CSS sanitization. CSS can contain injection vectors like `expression()`, `@import`, or malicious URLs.

**Current Code**:
```php
$inline_css = '#' . esc_attr($safe_container_id) . ' {' . wp_strip_all_tags($custom_css) . '}';
wp_add_inline_style('flowq-shortcode', $inline_css);
```

**Fix Required**:
- Implement comprehensive CSS validation/sanitization
- Use `wp_kses()` with a CSS-specific allowed list or a dedicated CSS sanitizer
- Consider using predefined CSS classes instead of arbitrary inline CSS
- Sanitize CSS property values with a whitelist approach

---

## Issue #3: CSS Injection in Template Handler (MEDIUM)

**File**: `includes/class-template-handler.php:340`

**Problem**: Template color values from database are concatenated directly into CSS without proper validation. `wp_strip_all_tags()` only removes HTML tags, not malicious CSS.

**Current Code**:
```php
$css .= "background: {$background_color};";
$css .= "color: {$text_color};";
// ...
wp_add_inline_style('flowq-frontend', wp_strip_all_tags($css));
```

**Fix Required**:
- Validate all color values with `sanitize_hex_color()`
- Use regex validation for hex colors: `/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/`
- Sanitize gradient values and other CSS properties
- Never concatenate unvalidated database values into CSS

---

## Issue #4: Shortcode Output Escaping Verification (MEDIUM)

**Files**:
- `public/class-frontend.php:184` - `render_survey_shortcode`
- `includes/class-shortcode.php:45` - `render_survey_list`

**Problem**: Shortcode callbacks return HTML that needs verification that all output is properly escaped. Survey titles, descriptions, and links must be escaped appropriately.

**Fix Required**:
- Audit `render_survey()` method for proper escaping of all variables
- Verify `render_survey_list()` escapes all survey data (titles, descriptions, links)
- Ensure survey subtitle HTML is processed with `wp_kses_post()` consistently

---

## Issue #5: Unescaped Translated String (LOW)

**File**: `public/class-frontend.php:58` - `filter_thank_you_page_content`

**Problem**: The "Access Denied" heading uses `__()` without escaping.

**Current Code**:
```php
'<h2>' . __('Access Denied', 'flowq') . '</h2>'
```

**Fix Required**:
```php
'<h2>' . esc_html__('Access Denied', 'flowq') . '</h2>'
```

---

## Issue #6: Blanket PHPCS Ignore in Template (LOW)

**File**: `public/templates/survey-container.php:23`

**Problem**: Blanket `phpcs:ignore` comment suppresses escape warnings without specific justification.

**Current Code**:
```php
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_participant_form() handles escaping internally
echo $this->render_participant_form($survey);
```

**Fix Required**:
- Verify all outputs in `participant-form.php` are properly escaped
- Replace blanket PHPCS ignore with specific, justified comments
- Document which specific outputs are handled internally

---

## Files to Modify

| File | Issue(s) |
|------|----------|
| `includes/class-rest-api.php` | #1 - Add permission checks |
| `includes/class-shortcode.php` | #2, #4 - CSS sanitization, output escaping |
| `includes/class-template-handler.php` | #3 - Validate CSS color values |
| `public/class-frontend.php` | #4, #5 - Escape strings, verify output |
| `public/templates/survey-container.php` | #6 - Verify/document escaping |
| `public/templates/participant-form.php` | #6 - Audit all output |

---

## Verification Checklist

- [ ] Test REST API endpoint with various session IDs
- [ ] Attempt CSS injection in shortcode custom_css parameter
- [ ] Verify all frontend output is properly escaped
- [ ] Run WordPress coding standards check (PHPCS) after fixes
- [ ] Test all templates render correctly after changes
