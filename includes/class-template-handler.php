<?php
/**
 * Template Handler for WP Dynamic Survey Plugin
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template Handler class
 */
class FlowQ_Template_Handler {

    /**
     * Templates table name
     *
     * @var string
     */
    private $table_name;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'flowq_templates';
    }

    /**
     * Get active template from database
     *
     * @return array|null Template data or null if not found
     */
    public function get_active_template() {
        // Get active template ID
        $active_template_id = get_option('flowq_active_template', 1);

        // Try to get from object cache first
        $cache_key = 'flowq_active_template_' . $active_template_id;
        $cache_group = 'flowq_templates';
        $template = wp_cache_get($cache_key, $cache_group);

        if ($template === false) {
            // Cache miss - query database
            global $wpdb;

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table with object caching
            $template = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $active_template_id),
                ARRAY_A
            );

            // Store in cache for 24 hours (cache is invalidated when active template changes)
            if ($template) {
                wp_cache_set($cache_key, $template, $cache_group, 24 * HOUR_IN_SECONDS);
            }
        }

        return $template;
    }

    /**
     * Invalidate active template cache
     *
     * @return void
     */
    public function invalidate_cache() {
        $active_template_id = get_option('flowq_active_template', 1);
        $cache_key = 'flowq_active_template_' . $active_template_id;
        $cache_group = 'flowq_templates';
        wp_cache_delete($cache_key, $cache_group);
    }

    /**
     * Get template styles as array
     *
     * @return array Template styles configuration
     */
    public function get_template_styles() {
        $template = $this->get_active_template();

        if (!$template || empty($template['styles'])) {
            // Return default styles if template not found
            return array(
                'primary_color' => '#2271b1',
                'background_color' => '#ffffff',
                'text_color' => '#1d2327',
                'button_style' => 'solid',
                'border_radius' => '6px',
                'font_family' => 'system-ui, sans-serif'
            );
        }

        return json_decode($template['styles'], true);
    }

    /**
     * Generate dynamic CSS based on template styles
     *
     * @param array $styles Template styles configuration
     * @return string CSS styles
     */
    public function generate_template_css($styles = null) {
        if ($styles === null) {
            $styles = $this->get_template_styles();
        }

        $css = '';

        // Extract and sanitize styles with defaults
        $primary_color = FlowQ_Security_Helper::sanitize_hex_color($styles['primary_color'] ?? '#2271b1');
        $background_color = FlowQ_Security_Helper::sanitize_hex_color($styles['background_color'] ?? '#ffffff');
        $text_color = FlowQ_Security_Helper::sanitize_hex_color($styles['text_color'] ?? '#1d2327');
        $border_radius = $this->sanitize_border_radius($styles['border_radius'] ?? '6px');
        $font_family = $this->sanitize_font_family($styles['font_family'] ?? 'system-ui, sans-serif');
        $button_style = in_array($styles['button_style'] ?? 'solid', array('solid', 'gradient', 'elevated'), true) ? $styles['button_style'] : 'solid';

        // Base styles
        $css .= ".flowq-participant-form, .flowq-container {";
        $css .= "font-family: {$font_family};";
        $css .= "}";

        // Container background - Increased specificity
        $css .= ".flowq-participant-form .participant-form-container, .flowq-container .question-container {";
        $css .= "background: {$background_color};";
        $css .= "border-radius: {$border_radius};";
        $css .= "}";

        // Text colors - Increased specificity
        $css .= ".flowq-participant-form .form-title, .flowq-container .question-title, .flowq-participant-form .form-label, .flowq-container .answer-text, .flowq-participant-form .survey-form-header, .flowq-participant-form .survey-form-subtitle {";
        $css .= "color: {$text_color};";
        $css .= "}";

        // Secondary text colors (descriptions, notes, etc.)
        $css .= ".flowq-container .survey-description, .flowq-container .question-description p, .flowq-participant-form .form-notes, .flowq-participant-form .privacy-note, .flowq-participant-form .required-note, .flowq-container .skip-notice, .flowq-container .progress-text, .flowq-participant-form .privacy-policy-text {";
        $css .= "color: {$text_color};";
        $css .= "opacity: 0.8;";
        $css .= "}";

        // Privacy policy links
        $css .= ".flowq-participant-form .privacy-policy-text a {";
        $css .= "color: {$primary_color};";
        $css .= "}";

        // Privacy policy checkbox label
        $css .= ".flowq-participant-form .privacy-policy-checkbox label {";
        $css .= "color: {$text_color};";
        $css .= "}";

        // Sanitize gradient colors if present
        $gradient_start = isset($styles['gradient_start']) ? FlowQ_Security_Helper::sanitize_hex_color($styles['gradient_start']) : null;
        $gradient_end = isset($styles['gradient_end']) ? FlowQ_Security_Helper::sanitize_hex_color($styles['gradient_end']) : null;

        // Primary buttons
        $css .= ".flowq-participant-form .btn-primary, .flowq-container .btn-primary {";
        if ($button_style === 'gradient' && $gradient_start && $gradient_end) {
            $css .= "background: linear-gradient(135deg, {$gradient_start}, {$gradient_end});";
        } else {
            $css .= "background: {$primary_color};";
        }
        $css .= "border-radius: {$border_radius};";
        $css .= "}";

        // Primary button hover
        $css .= ".flowq-participant-form .btn-primary:hover:not(:disabled), .flowq-container .btn-primary:hover:not(:disabled) {";
        if ($button_style === 'gradient' && $gradient_start && $gradient_end) {
            $css .= "background: linear-gradient(135deg, {$gradient_start}, {$gradient_end});";
            $css .= "opacity: 0.9;";
        } else {
            $css .= "background: " . $this->darken_color($primary_color, 10) . ";";
        }
        $css .= "}";

        // Sanitize input colors if present
        $input_bg_color = isset($styles['input_bg_color']) ? FlowQ_Security_Helper::sanitize_hex_color($styles['input_bg_color']) : null;
        $input_border_color = isset($styles['input_border_color']) ? FlowQ_Security_Helper::sanitize_hex_color($styles['input_border_color']) : null;
        $input_text_color = isset($styles['input_text_color']) ? FlowQ_Security_Helper::sanitize_hex_color($styles['input_text_color']) : null;

        // Form controls
        $css .= ".flowq-participant-form .form-control {";
        $css .= "border-radius: {$border_radius};";
        // Apply dark theme input styles if available
        if ($input_bg_color) {
            $css .= "background-color: {$input_bg_color};";
        }
        if ($input_border_color) {
            $css .= "border-color: {$input_border_color};";
        }
        if ($input_text_color) {
            $css .= "color: {$input_text_color};";
        }
        $css .= "}";

        // Form control placeholder
        if ($input_text_color) {
            $css .= ".flowq-participant-form .form-control::placeholder {";
            $css .= "color: rgba(" . $this->hex_to_rgb($input_text_color) . ", 0.5);";
            $css .= "}";
        }

        // Form control focus state
        $css .= ".flowq-participant-form .form-control:focus {";
        $css .= "border-color: {$primary_color};";
        $css .= "box-shadow: 0 0 0 3px rgba(" . $this->hex_to_rgb($primary_color) . ", 0.1);";
        $css .= "}";

        // Answer options
        if ($input_bg_color) {
            $css .= ".flowq-container .single-choice .answer-label {";
            $css .= "background-color: {$input_bg_color};";
            $css .= "border-color: {$input_border_color};";
            $css .= "}";
        }

        $css .= ".flowq-container .single-choice .answer-label:hover, .flowq-container .single-choice .answer-input:checked + .answer-label {";
        $css .= "border-color: {$primary_color};";
        $css .= "background: rgba(" . $this->hex_to_rgb($primary_color) . ", 0.05);";
        $css .= "}";

        // Answer text color on hover (white for dark themes)
        if ($input_bg_color) {
            $css .= ".flowq-container .single-choice .answer-label:hover .answer-text, .flowq-container .single-choice .answer-input:checked + .answer-label .answer-text {";
            $css .= "color: #ffffff;";
            $css .= "}";
        }

        $css .= ".flowq-container .single-choice .answer-input:checked + .answer-label .answer-indicator {";
        $css .= "border-color: {$primary_color};";
        $css .= "background: {$primary_color};";
        $css .= "}";

        // Progress bar
        $css .= ".flowq-container .progress-fill {";
        if ($button_style === 'gradient' && $gradient_start && $gradient_end) {
            $css .= "background: linear-gradient(90deg, {$gradient_start}, {$gradient_end});";
        } else {
            $css .= "background: linear-gradient(90deg, {$primary_color}, " . $this->darken_color($primary_color, 15) . ");";
        }
        $css .= "}";

        // Card shadow for elevated button style
        if ($button_style === 'elevated' && isset($styles['card_shadow'])) {
            $card_shadow = $this->sanitize_box_shadow($styles['card_shadow']);
            if ($card_shadow) {
                $css .= ".flowq-participant-form .participant-form-container, .flowq-container .question-container {";
                $css .= "box-shadow: {$card_shadow};";
                $css .= "}";
            }
        }

        // Skip button styling
        if ($input_bg_color) {
            // Dark theme skip button
            $css .= ".flowq-container .skip-question-btn {";
            $css .= "background: {$input_bg_color};";
            $css .= "border-color: {$input_border_color};";
            $css .= "color: {$text_color};";
            $css .= "}";
        }

        $css .= ".flowq-container .skip-question-btn:hover {";
        $css .= "border-color: {$primary_color};";
        $css .= "background: rgba(" . $this->hex_to_rgb($primary_color) . ", 0.05);";
        if ($input_bg_color) {
            $css .= "color: #ffffff;";
        }
        $css .= "}";

        return $css;
    }

    /**
     * Convert hex color to RGB
     *
     * @param string $hex Hex color code
     * @return string RGB values (e.g., "34, 113, 177")
     */
    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r}, {$g}, {$b}";
    }

    /**
     * Sanitize border radius value
     *
     * @param string $value Border radius value
     * @return string Sanitized border radius
     */
    private function sanitize_border_radius($value) {
        // Only allow numeric values with px, rem, em, or %
        if (preg_match('/^\d+(\.\d+)?(px|rem|em|%)$/', $value)) {
            return $value;
        }
        return '6px';
    }

    /**
     * Sanitize font family value
     *
     * @param string $value Font family value
     * @return string Sanitized font family
     */
    private function sanitize_font_family($value) {
        // Remove potentially dangerous characters, allow letters, numbers, spaces, commas, quotes, hyphens
        $sanitized = preg_replace('/[^a-zA-Z0-9\s,\'\"\-]/', '', $value);
        return !empty($sanitized) ? $sanitized : 'system-ui, sans-serif';
    }

    /**
     * Sanitize box shadow value
     *
     * @param string $value Box shadow value
     * @return string Sanitized box shadow or empty string
     */
    private function sanitize_box_shadow($value) {
        // Basic validation - only allow common box-shadow patterns
        $value = FlowQ_Security_Helper::sanitize_css_value($value);
        if (empty($value)) {
            return '';
        }
        // Additional check for box-shadow specific patterns
        if (preg_match('/^[\d\.\s]+(px|rem|em)[\s\d\.\-]*(px|rem|em)?[\s\d\.\-]*(px|rem|em)?[\s\d\.\-]*(px|rem|em)?[\s]*(rgba?\([^)]+\)|#[a-fA-F0-9]{3,6})?$/i', $value)) {
            return $value;
        }
        return '';
    }

    /**
     * Darken a hex color by a percentage
     *
     * @param string $hex Hex color code
     * @param int $percent Percentage to darken (0-100)
     * @return string Darkened hex color
     */
    private function darken_color($hex, $percent) {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, min(255, $r - ($r * $percent / 100)));
        $g = max(0, min(255, $g - ($g * $percent / 100)));
        $b = max(0, min(255, $b - ($b * $percent / 100)));

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

    /**
     * Render template-specific CSS inline
     *
     * @deprecated Use enqueue_template_styles() instead
     * @return string Empty string (use enqueue_template_styles() for proper enqueueing)
     */
    public function render_template_css() {
        // Deprecated method - use enqueue_template_styles() instead
        _deprecated_function(__METHOD__, '1.0.0', 'FlowQ_Template_Handler::enqueue_template_styles()');

        // Call the proper enqueue method instead of returning raw tags
        $this->enqueue_template_styles();
        return '';
    }

    /**
     * Enqueue template-specific CSS properly using wp_add_inline_style
     *
     * @return void
     */
    public function enqueue_template_styles() {
        // Only enqueue on frontend
        if (is_admin()) {
            return;
        }

        // Generate dynamic CSS (values are sanitized in generate_template_css)
        $css = $this->generate_template_css();

        // Add inline styles to the frontend stylesheet
        // CSS values are already sanitized in generate_template_css(), additional sanitization for safety
        wp_add_inline_style('flowq-frontend', FlowQ_Security_Helper::sanitize_css($css));
    }

    /**
     * Initialize template handler hooks
     *
     * @return void
     */
    public function init() {
        // Hook into wp_enqueue_scripts to properly enqueue template styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_template_styles'), 20);
    }
}
