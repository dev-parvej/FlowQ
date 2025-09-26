<?php
/**
 * Centralized AJAX Handler for WP Dynamic Survey Plugin
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Centralized AJAX Handler class
 */
class WP_Dynamic_Survey_Ajax_Handler {

    /**
     * Rate limiting storage
     */
    private static $rate_limits = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Frontend AJAX handlers
        add_action('wp_ajax_wp_dynamic_survey_validate_session', array($this, 'validate_session'));
        add_action('wp_ajax_nopriv_wp_dynamic_survey_validate_session', array($this, 'validate_session'));

        add_action('wp_ajax_wp_dynamic_survey_get_survey_data', array($this, 'get_survey_data'));
        add_action('wp_ajax_nopriv_wp_dynamic_survey_get_survey_data', array($this, 'get_survey_data'));

        add_action('wp_ajax_wp_dynamic_survey_heartbeat', array($this, 'heartbeat'));
        add_action('wp_ajax_nopriv_wp_dynamic_survey_heartbeat', array($this, 'heartbeat'));

        // Admin AJAX handlers
        add_action('wp_ajax_wp_dynamic_survey_get_survey_statistics', array($this, 'get_survey_statistics'));
        add_action('wp_ajax_wp_dynamic_survey_bulk_export_responses', array($this, 'bulk_export_responses'));

        // Security and validation
        add_action('wp_ajax_wp_dynamic_survey_check_permissions', array($this, 'check_permissions'));
    }

    /**
     * Rate limiting check
     */
    private function check_rate_limit($action, $limit = 60, $window = 60) {
        $ip = $this->get_client_ip();
        $key = $action . '_' . $ip;
        $now = time();

        if (!isset(self::$rate_limits[$key])) {
            self::$rate_limits[$key] = array();
        }

        // Clean old entries
        self::$rate_limits[$key] = array_filter(self::$rate_limits[$key], function($timestamp) use ($now, $window) {
            return ($now - $timestamp) < $window;
        });

        // Check limit
        if (count(self::$rate_limits[$key]) >= $limit) {
            wp_send_json_error(array(
                'message' => __('Rate limit exceeded. Please try again later.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'code' => 'rate_limit_exceeded'
            ), 429);
        }

        // Add current request
        self::$rate_limits[$key][] = $now;
    }

    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Validate session AJAX handler
     */
    public function validate_session() {
        $this->check_rate_limit('validate_session', 30, 60);
        check_ajax_referer('wp_dynamic_survey_frontend_nonce', 'nonce');

        $session_id = sanitize_text_field($_POST['session_id'] ?? '');

        if (empty($session_id)) {
            wp_send_json_error(__('Session ID is required.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        $participant_manager = new WP_Dynamic_Survey_Participant_Manager();
        $participant = $participant_manager->validate_session($session_id);

        if (is_wp_error($participant)) {
            wp_send_json_error($participant->get_error_message());
        }

        wp_send_json_success(array(
            'valid' => true,
            'session_id' => $session_id,
            'survey_id' => $participant['survey_id'],
            'expires_at' => $participant['expires_at']
        ));
    }

    /**
     * Get survey data AJAX handler
     */
    public function get_survey_data() {
        $this->check_rate_limit('get_survey_data', 20, 60);
        check_ajax_referer('wp_dynamic_survey_frontend_nonce', 'nonce');

        $survey_id = intval($_POST['survey_id'] ?? 0);

        if (!$survey_id) {
            wp_send_json_error(__('Survey ID is required.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        $survey_manager = new WP_Dynamic_Survey_Manager();
        $survey = $survey_manager->get_survey($survey_id);

        if (!$survey || $survey['status'] !== 'published') {
            wp_send_json_error(__('Survey not available.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        $question_manager = new WP_Dynamic_Survey_Question_Manager();
        $questions = $question_manager->get_survey_questions($survey_id, true);

        wp_send_json_success(array(
            'survey' => $survey,
            'questions' => $questions,
            'total_questions' => count($questions)
        ));
    }

    /**
     * Heartbeat AJAX handler - keeps session alive
     */
    public function heartbeat() {
        $this->check_rate_limit('heartbeat', 10, 60);
        check_ajax_referer('wp_dynamic_survey_frontend_nonce', 'nonce');

        $session_id = sanitize_text_field($_POST['session_id'] ?? '');

        if (!empty($session_id)) {
            $participant_manager = new WP_Dynamic_Survey_Participant_Manager();
            $participant = $participant_manager->get_participant($session_id);

            if ($participant && !$participant['completed_at']) {
                // Update last activity timestamp
                global $wpdb;
                $table_name = $wpdb->prefix . 'dynamic_survey_participants';
                $wpdb->update(
                    $table_name,
                    array('updated_at' => current_time('mysql')),
                    array('session_id' => $session_id),
                    array('%s'),
                    array('%s')
                );
            }
        }

        wp_send_json_success(array(
            'timestamp' => current_time('timestamp'),
            'server_time' => current_time('mysql')
        ));
    }

    /**
     * Get survey statistics AJAX handler (Admin only)
     */
    public function get_survey_statistics() {
        $this->check_rate_limit('get_survey_statistics', 10, 60);

        if (!current_user_can('manage_wp_dynamic_surveys')) {
            wp_send_json_error(__('Insufficient permissions.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        check_ajax_referer('wp_dynamic_survey_admin_nonce', 'nonce');

        $survey_id = intval($_POST['survey_id'] ?? 0);

        if (!$survey_id) {
            wp_send_json_error(__('Survey ID is required.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        $survey_manager = new WP_Dynamic_Survey_Manager();
        $stats = $survey_manager->get_survey_statistics($survey_id);

        if (is_wp_error($stats)) {
            wp_send_json_error($stats->get_error_message());
        }

        wp_send_json_success($stats);
    }

    /**
     * Bulk export responses AJAX handler (Admin only)
     */
    public function bulk_export_responses() {
        $this->check_rate_limit('bulk_export_responses', 5, 300); // More restrictive for exports

        if (!current_user_can('manage_wp_dynamic_surveys')) {
            wp_send_json_error(__('Insufficient permissions.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        check_ajax_referer('wp_dynamic_survey_admin_nonce', 'nonce');

        $survey_ids = array_map('intval', $_POST['survey_ids'] ?? array());
        $format = sanitize_text_field($_POST['format'] ?? 'csv');

        if (empty($survey_ids)) {
            wp_send_json_error(__('At least one survey must be selected.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        $session_manager = new WP_Dynamic_Survey_Session_Manager();

        try {
            $export_data = array();
            foreach ($survey_ids as $survey_id) {
                $responses = $session_manager->export_responses_csv($survey_id);
                if (!is_wp_error($responses)) {
                    $export_data[$survey_id] = $responses;
                }
            }

            $filename = 'bulk-survey-export-' . date('Y-m-d-H-i-s') . '.' . $format;

            wp_send_json_success(array(
                'data' => $export_data,
                'filename' => $filename,
                'format' => $format,
                'total_surveys' => count($export_data)
            ));

        } catch (Exception $e) {
            wp_send_json_error(__('Export failed: ', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) . $e->getMessage());
        }
    }
    
    /**
     * Check permissions AJAX handler
     */
    public function check_permissions() {
        $action = sanitize_text_field($_POST['action_check'] ?? '');

        $permissions = array(
            'manage_surveys' => current_user_can('manage_wp_dynamic_surveys'),
            'view_responses' => current_user_can('view_wp_dynamic_survey_responses'),
            'export_data' => current_user_can('export_wp_dynamic_survey_data'),
            'is_admin' => current_user_can('administrator')
        );

        wp_send_json_success($permissions);
    }

    /**
     * Log AJAX errors for debugging
     */
    public static function log_ajax_error($action, $error_message, $data = array()) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[WP Dynamic Survey AJAX] Action: %s, Error: %s, Data: %s',
                $action,
                $error_message,
                json_encode($data)
            ));
        }
    }

    /**
     * Sanitize AJAX response data
     */
    public static function sanitize_response_data($data) {
        if (is_array($data)) {
            return array_map(array(self::class, 'sanitize_response_data'), $data);
        } elseif (is_string($data)) {
            return sanitize_text_field($data);
        } elseif (is_int($data) || is_float($data) || is_bool($data)) {
            return $data;
        } else {
            return null;
        }
    }
}