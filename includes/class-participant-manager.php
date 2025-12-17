<?php
/**
 * Participant Manager for WP Dynamic Survey Plugin
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Survey Participant Manager class
 */
class FlowQ_Participant_Manager {

    /**
     * Plugin table prefix
     */
    private $table_prefix;

    /**
     * Session timeout in seconds (default: 1 hour)
     */
    private $session_timeout;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_prefix = $wpdb->prefix . 'flowq_';
        $this->session_timeout = flowq_plugin()->get_option('session_timeout', 3600);
    }

    /**
     * Create a new participant
     *
     * @param int $survey_id Survey ID
     * @param array $participant_data Participant information
     * @return array|WP_Error Array with session_id and participant_id on success, WP_Error on failure
     */
    public function create_participant($survey_id, $participant_data) {
        global $wpdb;
        // Validate required fields (phone is now optional for staged registration)
        $required_fields = ['name', 'email'];
        foreach ($required_fields as $field) {
            if (empty($participant_data[$field])) {
                return new WP_Error(
                    'missing_field',
                    /* translators: %s: field name */
                    sprintf(__('Required field missing: %s', 'flowq'), $field)
                );
            }
        }

        // Validate email format
        if (!is_email($participant_data['email'])) {
            return new WP_Error('invalid_email', __('Invalid email address.', 'flowq'));
        }

        // Check if survey exists
        $survey_manager = new FlowQ_Survey_Manager();
        $survey = $survey_manager->get_survey($survey_id);
        if (!$survey) {
            return new WP_Error('survey_not_found', __('Survey not found.', 'flowq'));
        }

        // Check if survey is accessible
        if ($survey['status'] !== 'published') {
            return new WP_Error('survey_not_published', __('Survey is not published.', 'flowq'));
        }

        // Check for duplicate email if setting is disabled
        $allow_duplicate_emails = get_option('flowq_allow_duplicate_emails', '0');
        if ($allow_duplicate_emails != '1') {
            $email_exists = $this->email_exists_for_survey($survey_id, $participant_data['email']);
            if ($email_exists) {
                return new WP_Error(
                    'duplicate_email',
                    __('You have already submitted this survey with this email address.', 'flowq')
                );
            }
        }

        // Generate unique session ID
        $session_id = $this->generate_session_id();

        // Prepare participant data
        $participant_record = array(
            'session_id' => $session_id,
            'survey_id' => $survey_id,
            'participant_name' => sanitize_text_field($participant_data['name']),
            'participant_phone' => sanitize_text_field($participant_data['phone']),
            'participant_email' => sanitize_email($participant_data['email']),
            'participant_address' => isset($participant_data['address']) ?
                sanitize_textarea_field($participant_data['address']) : null,
            'participant_zip_code' => isset($participant_data['zip_code']) ?
                sanitize_text_field($participant_data['zip_code']) : null,
            'current_question_id' => null,
            'question_chain' => wp_json_encode(array()),
            'started_at' => current_time('mysql'),
            'completed_at' => null,
            'user_ip' => $this->get_user_ip(),
            'user_agent' => $this->get_user_agent()
        );

        // Insert participant
        $table_name = $this->table_prefix . 'participants';
        $result = $wpdb->insert($table_name, $participant_record);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create participant record.', 'flowq'));
        }

        $participant_id = $wpdb->insert_id;

        // Trigger action hook
        do_action('flowq_participant_registered', $participant_id, $participant_data, $survey_id);

        return array(
            'session_id' => $session_id,
            'participant_id' => $participant_id
        );
    }

    /**
     * Get participant by session ID
     *
     * @param string $session_id Session ID
     * @return array|null Participant data or null if not found
     */
    public function get_participant($session_id) {
        global $wpdb;
        $table_name = $this->table_prefix . 'participants';

        $participant = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE session_id = %s",
                $session_id
            ),
            ARRAY_A
        );

        if (!$participant) {
            return null;
        }

        // Check session timeout
        if ($this->is_session_expired($participant)) {
            return null;
        }

        // Parse JSON question chain
        $participant['question_chain'] = json_decode($participant['question_chain'], true) ?? array();

        return $participant;
    }

    /**
     * Update current question for participant (for progress tracking)
     *
     * @param string $session_id Session ID
     * @param int $question_id Question ID
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function update_current_question($session_id, $question_id) {
        global $wpdb;
        // Validate session
        $participant = $this->get_participant($session_id);
        if (!$participant) {
            return new WP_Error('invalid_session', __('Invalid or expired session.', 'flowq'));
        }

        // Update current question
        $table_name = $this->table_prefix . 'participants';
        $result = $wpdb->update(
            $table_name,
            array('current_question_id' => $question_id),
            array('session_id' => $session_id),
            array('%d'),
            array('%s')
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update current question.', 'flowq'));
        }

        return true;
    }

    /**
     * Add question to participant's question chain (for loop prevention tracking)
     *
     * @param string $session_id Session ID
     * @param int $question_id Question ID
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function add_to_question_chain($session_id, $question_id) {
        global $wpdb;
        // Get current participant
        $participant = $this->get_participant($session_id);
        if (!$participant) {
            return new WP_Error('invalid_session', __('Invalid or expired session.', 'flowq'));
        }

        // Add to question chain if not already present
        $question_chain = $participant['question_chain'];
        if (!in_array($question_id, $question_chain)) {
            $question_chain[] = $question_id;
        }

        // Update question chain
        $table_name = $this->table_prefix . 'participants';
        $result = $wpdb->update(
            $table_name,
            array('question_chain' => wp_json_encode($question_chain)),
            array('session_id' => $session_id),
            array('%s'),
            array('%s')
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update question chain.', 'flowq'));
        }

        return true;
    }

    /**
     * Generate unique session ID
     *
     * @return string Session ID
     */
    public function generate_session_id() {
        global $wpdb;
        $attempts = 0;
        $max_attempts = 10;

        do {
            // Generate session ID: timestamp + random string
            $session_id = time() . '_' . wp_generate_password(20, false);
            $attempts++;

            // Check if session ID already exists
            $table_name = $this->table_prefix . 'participants';
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table_name} WHERE session_id = %s",
                    $session_id
                )
            );

        } while ($exists > 0 && $attempts < $max_attempts);

        if ($attempts >= $max_attempts) {
            // Fallback to UUID-like generation
            $session_id = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                wp_rand(0, 0xffff), wp_rand(0, 0xffff),
                wp_rand(0, 0xffff),
                wp_rand(0, 0x0fff) | 0x4000,
                wp_rand(0, 0x3fff) | 0x8000,
                wp_rand(0, 0xffff), wp_rand(0, 0xffff), wp_rand(0, 0xffff)
            );
        }

        return $session_id;
    }

    /**
     * Get user IP address
     *
     * @return string IP address
     */
    public function get_user_ip() {
        $ip = '';

        // Check for IP from shared internet
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
        }
        // Check for IP passed from proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
        }
        // Check for IP from remote address
        elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        }

        // Validate IP after sanitization
        $ip = filter_var($ip, FILTER_VALIDATE_IP);
        return $ip ? $ip : '0.0.0.0';
    }

    /**
     * Get user agent string
     *
     * @return string User agent
     */
    private function get_user_agent() {
        return isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
    }

    /**
     * Mark participant as completed
     *
     * @param string $session_id Session ID
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function mark_completed($session_id) {
        global $wpdb;
        // Validate session
        $participant = $this->get_participant($session_id);
        if (!$participant) {
            return new WP_Error('invalid_session', __('Invalid or expired session.', 'flowq'));
        }

        // Update completion timestamp
        $table_name = $this->table_prefix . 'participants';
        $result = $wpdb->update(
            $table_name,
            array('completed_at' => current_time('mysql')),
            array('session_id' => $session_id),
            array('%s'),
            array('%s')
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to mark participant as completed.', 'flowq'));
        }

        return true;
    }

    /**
     * Update participant phone number
     *
     * @param string $session_id Session ID
     * @param string $phone_number Phone number to update
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function update_phone_number($session_id, $phone_number) {
        global $wpdb;
        // Validate session exists
        $participant = $this->get_participant($session_id);
        if (!$participant) {
            return new WP_Error('invalid_session', __('Invalid or expired session.', 'flowq'));
        }

        // Validate phone number
        if (empty($phone_number)) {
            return new WP_Error('missing_phone', __('Phone number is required.', 'flowq'));
        }

        // Update phone number
        $table_name = $this->table_prefix . 'participants';
        $result = $wpdb->update(
            $table_name,
            array('participant_phone' => sanitize_text_field($phone_number)),
            array('session_id' => $session_id),
            array('%s'),
            array('%s')
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update phone number.', 'flowq'));
        }

        return true;
    }

    /**
     * Check if session is expired
     *
     * @param array $participant Participant data
     * @return bool True if expired
     */
    private function is_session_expired($participant) {
        if (!$participant['started_at']) {
            return true;
        }

        $started_timestamp = strtotime($participant['started_at']);
        $current_timestamp = current_time('timestamp');

        return ($current_timestamp - $started_timestamp) > $this->session_timeout;
    }

    /**
     * Validate session and get participant
     *
     * @param string $session_id Session ID
     * @return array|WP_Error Participant data or WP_Error
     */
    public function validate_session($session_id) {
        if (empty($session_id)) {
            return new WP_Error('missing_session', __('Session ID is required.', 'flowq'));
        }

        $participant = $this->get_participant($session_id);
        if (!$participant) {
            return new WP_Error('invalid_session', __('Invalid or expired session.', 'flowq'));
        }

        return $participant;
    }

    /**
     * Get participant by ID
     *
     * @param int $participant_id Participant ID
     * @return array|null Participant data or null
     */
    public function get_participant_by_id($participant_id) {
        global $wpdb;
        $table_name = $this->table_prefix . 'participants';

        $participant = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d",
                $participant_id
            ),
            ARRAY_A
        );

        if ($participant) {
            $participant['question_chain'] = json_decode($participant['question_chain'], true) ?? array();
        }

        return $participant;
    }

    /**
     * Get participants for a survey
     *
     * @param int $survey_id Survey ID
     * @param array $args Query arguments
     * @return array Participants list
     */
    public function get_survey_participants($survey_id, $args = array()) {
        global $wpdb;
        $defaults = array(
            'status' => null, // 'completed', 'in_progress'
            'limit' => 50,
            'offset' => 0,
            'orderby' => 'started_at',
            'order' => 'DESC'
        );

        $args = wp_parse_args($args, $defaults);
        $table_name = $this->table_prefix . 'participants';

        // SECURITY: Whitelist validation for ORDER BY column (cannot use prepare() placeholders for column names)
        $allowed_orderby = array('started_at', 'completed_at', 'updated_at', 'id');
        if (!in_array($args['orderby'], $allowed_orderby, true)) {
            $args['orderby'] = 'started_at'; // Safe default
        }

        // SECURITY: Whitelist validation for ORDER direction (ASC/DESC keywords cannot use placeholders)
        $args['order'] = strtoupper($args['order']);
        if (!in_array($args['order'], array('ASC', 'DESC'), true)) {
            $args['order'] = 'DESC'; // Safe default
        }

        // After whitelist validation, escape values for SQL (defense in depth)
        $orderby = esc_sql($args['orderby']); // Whitelisted then escaped
        $order = esc_sql($args['order']); // Whitelisted then escaped

        // Build WHERE clause with prepare() placeholders
        $where_clauses = array();
        $prepare_values = array();

        // Always filter by survey_id
        $where_clauses[] = 'survey_id = %d';
        $prepare_values[] = absint($survey_id);

        // Optional status filter
        if ($args['status'] === 'completed') {
            $where_clauses[] = 'completed_at IS NOT NULL';
        } elseif ($args['status'] === 'in_progress') {
            $where_clauses[] = 'completed_at IS NULL';
        }

        // Construct WHERE clause
        $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

        // Add LIMIT and OFFSET to prepare values
        $prepare_values[] = absint($args['limit']);
        $prepare_values[] = absint($args['offset']);

        // Execute query - ORDER BY uses whitelisted+escaped values, other params use prepare()
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- ORDER BY column/direction are whitelisted and escaped
        $participants = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
                $prepare_values
            ),
            ARRAY_A
        );

        // Parse question chains
        foreach ($participants as &$participant) {
            $participant['question_chain'] = json_decode($participant['question_chain'], true) ?? array();
        }

        return $participants;
    }

    /**
     * Check if participant has completed survey
     *
     * @param string $session_id Session ID
     * @return bool True if completed
     */
    public function is_completed($session_id) {
        $participant = $this->get_participant($session_id);
        return $participant && !empty($participant['completed_at']);
    }

    /**
     * Generate completion token for thank you page access
     *
     * @param string $session_id Session ID
     * @return string|WP_Error Token on success, WP_Error on failure
     */
    public function generate_completion_token($session_id) {
        global $wpdb;
        // Validate session exists
        $participant = $this->get_participant($session_id);
        if (!$participant) {
            return new WP_Error('invalid_session', __('Invalid session.', 'flowq'));
        }

        // Generate unique token
        $token = wp_generate_uuid4();
        $expires_at = gmdate('Y-m-d H:i:s', strtotime('+1 hour'));

        // Update participant with token
        $table_name = $this->table_prefix . 'participants';
        $result = $wpdb->update(
            $table_name,
            array(
                'completion_token' => $token,
                'token_expires_at' => $expires_at
            ),
            array('session_id' => $session_id),
            array('%s', '%s'),
            array('%s')
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to generate completion token.', 'flowq'));
        }

        return $token;
    }

    /**
     * Validate completion token and get participant data
     *
     * @param string $token Completion token
     * @return array|WP_Error Participant data on success, WP_Error on failure
     */
    public function validate_completion_token($token) {
        global $wpdb;
        if (empty($token)) {
            return new WP_Error('missing_token', __('Token is required.', 'flowq'));
        }

        $table_name = $this->table_prefix . 'participants';

        $participant = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE completion_token = %s AND token_expires_at > NOW()",
                $token
            ),
            ARRAY_A
        );

        if (!$participant) {
            return new WP_Error('invalid_token', __('Invalid or expired token.', 'flowq'));
        }

        return [
            'survey' => (new FlowQ_Survey_Manager())->get_survey($participant['survey_id']), 
            'participant' => $participant
        ];
    }

    /**
     * Check if email already exists for a survey
     *
     * @param int $survey_id Survey ID
     * @param string $email Email address
     * @return bool True if email exists, false otherwise
     */
    public function email_exists_for_survey($survey_id, $email) {
        global $wpdb;
        $table_name = $this->table_prefix . 'participants';

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE survey_id = %d AND participant_email = %s",
                $survey_id,
                $email
            )
        );

        return $count > 0;
    }
}