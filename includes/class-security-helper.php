<?php
/**
 * Security Helper for FlowQ Plugin
 *
 * Provides reusable security functions for rate limiting,
 * input validation, and sanitization.
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Security Helper class
 */
class FlowQ_Security_Helper {

    /**
     * Check rate limit for a given action
     *
     * @param string $action     Action identifier (e.g., 'session_validate')
     * @param int    $max_requests Maximum requests allowed in the time window
     * @param int    $time_window  Time window in seconds (default: 60)
     * @return bool|WP_Error True if within limit, WP_Error if exceeded
     */
    public static function check_rate_limit($action, $max_requests = 10, $time_window = 60) {
        $ip_address = self::get_client_ip();
        $rate_limit_key = 'flowq_' . sanitize_key($action) . '_' . md5($ip_address);
        $rate_limit_count = get_transient($rate_limit_key);

        if ($rate_limit_count !== false && $rate_limit_count >= $max_requests) {
            return new WP_Error(
                'rate_limit_exceeded',
                __('Too many requests. Please try again later.', 'flowq'),
                array('status' => 429)
            );
        }

        // Increment rate limit counter
        if ($rate_limit_count === false) {
            set_transient($rate_limit_key, 1, $time_window);
        } else {
            set_transient($rate_limit_key, $rate_limit_count + 1, $time_window);
        }

        return true;
    }

    /**
     * Get client IP address
     *
     * @return string Client IP address
     */
    public static function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        );

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = sanitize_text_field(wp_unslash($_SERVER[$key]));
                // Handle comma-separated IPs (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Validate session ID format
     *
     * Expected format: timestamp_randomstring (e.g., 1234567890_abc123def456)
     *
     * @param string $session_id Session ID to validate
     * @return bool True if valid format, false otherwise
     */
    public static function validate_session_format($session_id) {
        return (bool) preg_match('/^\d{10,}_[a-zA-Z0-9]{12,}$/', $session_id);
    }

    /**
     * Sanitize hex color value
     *
     * @param string $color Hex color value
     * @return string Sanitized hex color or default
     */
    public static function sanitize_hex_color($color) {
        // Use WordPress function if available
        if (function_exists('sanitize_hex_color')) {
            $sanitized = sanitize_hex_color($color);
            return $sanitized ? $sanitized : '#000000';
        }

        // Fallback validation
        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
            return $color;
        }

        return '#000000';
    }

    /**
     * Sanitize CSS property value
     *
     * Removes potentially dangerous CSS values while preserving safe ones.
     *
     * @param string $value CSS property value
     * @return string Sanitized CSS value
     */
    public static function sanitize_css_value($value) {
        $value = wp_strip_all_tags($value);

        // Remove potentially dangerous patterns
        $dangerous_patterns = array(
            '/expression\s*\(/i',           // IE expression()
            '/javascript\s*:/i',            // javascript: URLs
            '/vbscript\s*:/i',              // vbscript: URLs
            '/data\s*:/i',                  // data: URLs (can contain scripts)
            '/@import/i',                   // @import rules
            '/behavior\s*:/i',              // IE behavior
            '/-moz-binding/i',              // Firefox XBL binding
            '/url\s*\(\s*["\']?\s*javascript/i', // url(javascript:)
        );

        foreach ($dangerous_patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return '';
            }
        }

        return $value;
    }

    /**
     * Sanitize complete CSS block
     *
     * @param string $css CSS content to sanitize
     * @return string Sanitized CSS
     */
    public static function sanitize_css($css) {
        $css = wp_strip_all_tags($css);

        // Remove dangerous patterns
        $dangerous_patterns = array(
            '/expression\s*\(/i',
            '/javascript\s*:/i',
            '/vbscript\s*:/i',
            '/@import/i',
            '/behavior\s*:/i',
            '/-moz-binding/i',
        );

        foreach ($dangerous_patterns as $pattern) {
            $css = preg_replace($pattern, '', $css);
        }

        // Remove url() with anything other than safe protocols
        $css = preg_replace('/url\s*\(\s*["\']?\s*(?!https?:\/\/)[^)]+\)/i', '', $css);

        return $css;
    }

    /**
     * Validate and sanitize gradient CSS value
     *
     * @param string $gradient Gradient CSS value
     * @return string|false Sanitized gradient or false if invalid
     */
    public static function sanitize_gradient($gradient) {
        // Only allow linear-gradient and radial-gradient with hex/rgb colors
        $gradient = trim($gradient);

        // Check for dangerous content first
        if (preg_match('/expression|javascript|vbscript|data:|@import|behavior|-moz-binding/i', $gradient)) {
            return false;
        }

        // Validate gradient syntax (basic check)
        if (!preg_match('/^(linear|radial)-gradient\s*\(/i', $gradient)) {
            return false;
        }

        return wp_strip_all_tags($gradient);
    }
}
