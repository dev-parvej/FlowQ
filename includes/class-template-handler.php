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
     * Get active template from database
     *
     * @return array|null Template data or null if not found
     */
    public function get_active_template() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'flowq_templates';

        // Get active template ID
        $active_template_id = get_option('flowq_active_template', 1);

        // Fetch template from database
        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $active_template_id),
            ARRAY_A
        );

        return $template;
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

        // Extract styles with defaults
        $primary_color = $styles['primary_color'] ?? '#2271b1';
        $background_color = $styles['background_color'] ?? '#ffffff';
        $text_color = $styles['text_color'] ?? '#1d2327';
        $border_radius = $styles['border_radius'] ?? '6px';
        $font_family = $styles['font_family'] ?? 'system-ui, sans-serif';
        $button_style = $styles['button_style'] ?? 'solid';

        // Base styles
        $css .= ".flowq-participant-form, .flowq-container {";
        $css .= "font-family: {$font_family};";
        $css .= "}";

        // Container background
        $css .= ".participant-form-container, .question-container {";
        $css .= "background: {$background_color};";
        $css .= "border-radius: {$border_radius};";
        $css .= "}";

        // Text colors
        $css .= ".form-title, .question-title, .form-label, .answer-text {";
        $css .= "color: {$text_color};";
        $css .= "}";

        // Secondary text colors (descriptions, notes, etc.)
        $css .= ".survey-description, .question-description p, .form-notes, .privacy-note, .required-note, .skip-notice, .progress-text, .privacy-policy-text {";
        $css .= "color: {$text_color};";
        $css .= "opacity: 0.8;";
        $css .= "}";

        // Privacy policy links
        $css .= ".privacy-policy-text a {";
        $css .= "color: {$primary_color};";
        $css .= "}";

        // Privacy policy checkbox label
        $css .= ".privacy-policy-checkbox label {";
        $css .= "color: {$text_color};";
        $css .= "}";

        // Primary buttons
        $css .= ".btn-primary {";
        if ($button_style === 'gradient' && isset($styles['gradient_start']) && isset($styles['gradient_end'])) {
            $css .= "background: linear-gradient(135deg, {$styles['gradient_start']}, {$styles['gradient_end']});";
        } else {
            $css .= "background: {$primary_color};";
        }
        $css .= "border-radius: {$border_radius};";
        $css .= "}";

        // Primary button hover
        $css .= ".btn-primary:hover:not(:disabled) {";
        if ($button_style === 'gradient' && isset($styles['gradient_start']) && isset($styles['gradient_end'])) {
            $css .= "background: linear-gradient(135deg, {$styles['gradient_start']}, {$styles['gradient_end']});";
            $css .= "opacity: 0.9;";
        } else {
            $css .= "background: " . $this->darken_color($primary_color, 10) . ";";
        }
        $css .= "}";

        // Form controls
        $css .= ".form-control {";
        $css .= "border-radius: {$border_radius};";
        // Apply dark theme input styles if available
        if (isset($styles['input_bg_color'])) {
            $css .= "background-color: {$styles['input_bg_color']};";
        }
        if (isset($styles['input_border_color'])) {
            $css .= "border-color: {$styles['input_border_color']};";
        }
        if (isset($styles['input_text_color'])) {
            $css .= "color: {$styles['input_text_color']};";
        }
        $css .= "}";

        // Form control placeholder
        if (isset($styles['input_text_color'])) {
            $css .= ".form-control::placeholder {";
            $css .= "color: rgba(" . $this->hex_to_rgb($styles['input_text_color']) . ", 0.5);";
            $css .= "}";
        }

        // Form control focus state
        $css .= ".form-control:focus {";
        $css .= "border-color: {$primary_color};";
        $css .= "box-shadow: 0 0 0 3px rgba(" . $this->hex_to_rgb($primary_color) . ", 0.1);";
        $css .= "}";

        // Answer options
        $css .= ".single-choice .answer-label:hover, .single-choice .answer-input:checked + .answer-label {";
        $css .= "border-color: {$primary_color};";
        $css .= "background: rgba(" . $this->hex_to_rgb($primary_color) . ", 0.05);";
        $css .= "}";

        // Answer text color on hover (white for dark themes)
        if (isset($styles['input_bg_color'])) {
            $css .= ".single-choice .answer-label:hover .answer-text, .single-choice .answer-input:checked + .answer-label .answer-text {";
            $css .= "color: #ffffff;";
            $css .= "}";
        }

        $css .= ".single-choice .answer-input:checked + .answer-label .answer-indicator {";
        $css .= "border-color: {$primary_color};";
        $css .= "background: {$primary_color};";
        $css .= "}";

        // Progress bar
        $css .= ".progress-fill {";
        if ($button_style === 'gradient' && isset($styles['gradient_start']) && isset($styles['gradient_end'])) {
            $css .= "background: linear-gradient(90deg, {$styles['gradient_start']}, {$styles['gradient_end']});";
        } else {
            $css .= "background: linear-gradient(90deg, {$primary_color}, " . $this->darken_color($primary_color, 15) . ");";
        }
        $css .= "}";

        // Card shadow for elevated button style
        if ($button_style === 'elevated' && isset($styles['card_shadow'])) {
            $css .= ".participant-form-container, .question-container {";
            $css .= "box-shadow: {$styles['card_shadow']};";
            $css .= "}";
        }

        // Skip button styling
        if (isset($styles['input_bg_color'])) {
            // Dark theme skip button
            $css .= ".skip-question-btn {";
            $css .= "background: {$styles['input_bg_color']};";
            $css .= "border-color: {$styles['input_border_color']};";
            $css .= "color: {$text_color};";
            $css .= "}";
        }

        $css .= ".skip-question-btn:hover {";
        $css .= "border-color: {$primary_color};";
        $css .= "background: rgba(" . $this->hex_to_rgb($primary_color) . ", 0.05);";
        if (isset($styles['input_bg_color'])) {
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

        // Generate dynamic CSS
        $css = $this->generate_template_css();

        // Add inline styles to the frontend stylesheet
        // This ensures styles are properly enqueued rather than echoed as inline tags
        wp_add_inline_style('flowq-frontend', $css);
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
