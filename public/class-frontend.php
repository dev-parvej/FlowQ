<?php
/**
 * Frontend functionality for WP Dynamic Survey Plugin
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Survey Frontend class
 */
class WP_Dynamic_Survey_Frontend {

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
        // Enqueue frontend assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        // AJAX handlers for two-stage participant form submission
        add_action('wp_ajax_wp_dynamic_survey_submit_stage1_info', array($this, 'handle_stage1_submission'));
        add_action('wp_ajax_nopriv_wp_dynamic_survey_submit_stage1_info', array($this, 'handle_stage1_submission'));

        add_action('wp_ajax_wp_dynamic_survey_submit_stage2_info', array($this, 'handle_stage2_submission'));
        add_action('wp_ajax_nopriv_wp_dynamic_survey_submit_stage2_info', array($this, 'handle_stage2_submission'));

        // AJAX handlers for survey responses
        add_action('wp_ajax_wp_dynamic_survey_submit_answer', array($this, 'handle_answer_submission'));
        add_action('wp_ajax_nopriv_wp_dynamic_survey_submit_answer', array($this, 'handle_answer_submission'));

        // AJAX handler for skipping optional questions
        add_action('wp_ajax_wp_dynamic_survey_skip_question', array($this, 'handle_skip_question'));
        add_action('wp_ajax_nopriv_wp_dynamic_survey_skip_question', array($this, 'handle_skip_question'));

        // AJAX handlers for completion functionality
        add_action('wp_ajax_wp_dynamic_survey_get_completion_data', array($this, 'handle_get_completion_data'));
        add_action('wp_ajax_nopriv_wp_dynamic_survey_get_completion_data', array($this, 'handle_get_completion_data'));

        add_action('wp_ajax_wp_dynamic_survey_track_completion', array($this, 'handle_track_completion'));
        add_action('wp_ajax_nopriv_wp_dynamic_survey_track_completion', array($this, 'handle_track_completion'));

        // Handle token validation for thank you pages
        add_action('wp', array($this, 'handle_token_access'));

        // Filter page content for thank you pages
        add_filter('the_content', array($this, 'filter_thank_you_page_content'));

        // Add shortcode support
        add_action('init', array($this, 'register_shortcodes'));
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only enqueue on pages that have survey shortcodes or are survey pages
        if (!$this->should_load_assets()) {
            return;
        }

        // Frontend CSS
        wp_enqueue_style(
            'wp-dynamic-survey-frontend',
            WP_DYNAMIC_SURVEY_URL . 'assets/css/frontend.css',
            array(),
            WP_DYNAMIC_SURVEY_VERSION
        );

        // Survey utilities (loaded first)
        wp_enqueue_script(
            'wp-dynamic-survey-utils',
            WP_DYNAMIC_SURVEY_URL . 'assets/js/survey-utils.js',
            array(),
            WP_DYNAMIC_SURVEY_VERSION,
            true
        );

        // Enhanced frontend JavaScript
        wp_enqueue_script(
            'wp-dynamic-survey-frontend-enhanced',
            WP_DYNAMIC_SURVEY_URL . 'assets/js/frontend-enhanced.js',
            array('jquery', 'wp-dynamic-survey-utils'),
            WP_DYNAMIC_SURVEY_VERSION,
            true
        );

        // Original frontend JavaScript (for backward compatibility)
        wp_enqueue_script(
            'wp-dynamic-survey-frontend',
            WP_DYNAMIC_SURVEY_URL . 'assets/js/frontend.js',
            array('jquery', 'wp-dynamic-survey-frontend-enhanced'),
            WP_DYNAMIC_SURVEY_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script('wp-dynamic-survey-frontend', 'wpDynamicSurvey', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_dynamic_survey_frontend_nonce'),
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
            'version' => WP_DYNAMIC_SURVEY_VERSION,
            'config' => array(
                'enableAnalytics' => true,
                'enableKeyboardShortcuts' => true,
                'enableAccessibility' => true,
                'autoSave' => true,
                'animationDuration' => 300,
                'heartbeatInterval' => 30000,
                'retryAttempts' => 3,
                'retryDelay' => 1000,
                'cacheTimeout' => 300000
            ),
            'strings' => array(
                'loading' => __('Loading...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'submitting' => __('Submitting...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'error' => __('An error occurred. Please try again.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'required_field' => __('This field is required.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'invalid_email' => __('Please enter a valid email address.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'invalid_phone' => __('Please enter a valid phone number.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'thank_you' => __('Thank you for participating!', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'session_expired' => __('Your session has expired. Please start over.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'network_error' => __('Network error. Please check your connection.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'validation_error' => __('Please fix the errors below.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'save_progress' => __('Progress saved automatically.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'offline_mode' => __('You are offline. Responses will be saved locally.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'back_online' => __('Connection restored. Syncing responses...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)
            )
        ));
    }

    /**
     * Check if we should load assets
     */
    private function should_load_assets() {
        global $post;

        if (is_admin()) {
            return false;
        }

        // Check if current post has survey shortcode
        if ($post && has_shortcode($post->post_content, 'wp_dynamic_survey')) {
            return true;
        }

        // Homepage survey feature removed - only shortcode-based surveys supported

        return false;
    }

    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('wp_dynamic_survey', array($this, 'render_survey_shortcode'));
    }

    /**
     * Render survey shortcode
     */
    public function render_survey_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'theme' => 'default'
        ), $atts, 'wp_dynamic_survey');

        $survey_id = intval($atts['id']);
        $theme = sanitize_text_field($atts['theme']);

        if (!$survey_id) {
            return '<div class="wp-dynamic-survey-error">' .
                   esc_html__('Survey ID is required.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) .
                   '</div>';
        }

        return $this->render_survey($survey_id, $theme);
    }

    /**
     * Render survey interface
     */
    public function render_survey($survey_id, $theme = 'default') {
        $survey_manager = new WP_Dynamic_Survey_Manager();
        $survey = $survey_manager->get_survey($survey_id);

        if (!$survey) {
            return '<div class="wp-dynamic-survey-error">' .
                   esc_html__('Survey not found.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) .
                   '</div>';
        }

        if ($survey['status'] !== 'published') {
            return '<div class="wp-dynamic-survey-error">' .
                   esc_html__('This survey is not currently available.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) .
                   '</div>';
        }

        // Get questions for the survey
        $question_manager = new WP_Dynamic_Survey_Question_Manager();
        $questions = $question_manager->get_survey_questions($survey_id, true);

        if (empty($questions)) {
            return '<div class="wp-dynamic-survey-error">' .
                   esc_html__('This survey has no questions.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) .
                   '</div>';
        }

        ob_start();
        include WP_DYNAMIC_SURVEY_PATH . 'public/templates/survey-container.php';
        return ob_get_clean();
    }

    /**
     * Handle Stage 1 submission (name, email, address, zip code)
     */
    public function handle_stage1_submission() {
        check_ajax_referer('wp_dynamic_survey_frontend_nonce', 'nonce');

        $survey_id = intval($_POST['survey_id']);

        // Validate survey exists and is published
        $survey_manager = new WP_Dynamic_Survey_Manager();
        $survey = $survey_manager->get_survey($survey_id);

        if (!$survey || $survey['status'] !== 'published') {
            wp_send_json_error(__('Survey not available.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Collect and validate stage 1 data
        $stage1_data = array(
            'name' => sanitize_text_field($_POST['participant_name'] ?? ''),
            'email' => sanitize_email($_POST['participant_email'] ?? ''),
            'address' => sanitize_textarea_field($_POST['participant_address'] ?? ''),
            'zip_code' => sanitize_text_field($_POST['participant_zip_code'] ?? '')
        );

        // Validate required fields for stage 1
        $errors = array();

        if (empty($stage1_data['name'])) {
            $errors[] = __('Name is required.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
        }

        if (empty($stage1_data['email'])) {
            $errors[] = __('Email address is required.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
        } elseif (!is_email($stage1_data['email'])) {
            $errors[] = __('Please enter a valid email address.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
        }

        if (!empty($errors)) {
            wp_send_json_error(implode(' ', $errors));
        }

        // Create participant with stage 1 data (phone will be empty for now)
        $participant_data = array_merge($stage1_data, array('phone' => ''));
        $participant_manager = new WP_Dynamic_Survey_Participant_Manager();
        $result = $participant_manager->create_participant($survey_id, $participant_data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        // Get two-stage form setting
        $two_stage_form = get_option('wp_dynamic_survey_two_stage_form', '1');
        $field_phone = get_option('wp_dynamic_survey_field_phone', '1');

        // If phone is disabled, force single-stage
        if ($field_phone == '0') {
            $two_stage_form = '0';
        }

        $response_data = array(
            'session_id' => $result['session_id'],
            'participant_id' => $result['participant_id'],
            'stage' => 2,
            'two_stage_form' => $two_stage_form == '1'
        );

        // If single-stage mode, include first question to start survey immediately
        if ($two_stage_form != '1') {
            $question_manager = new WP_Dynamic_Survey_Question_Manager();
            $questions = $question_manager->get_survey_questions($survey_id, true);

            if (!empty($questions)) {
                $response_data['first_question'] = $questions[0];
                $response_data['survey_title'] = $survey['title'];
                $response_data['survey_description'] = $survey['description'];
                $response_data['total_questions'] = count($questions);
            }
        }

        wp_send_json_success($response_data);
    }

    /**
     * Handle Stage 2 submission (phone number) and start survey
     */
    public function handle_stage2_submission() {
        check_ajax_referer('wp_dynamic_survey_frontend_nonce', 'nonce');

        $survey_id = intval($_POST['survey_id']);
        $stage1_data = json_decode(stripslashes($_POST['stage1_data'] ?? ''), true);
        $participant_phone = sanitize_text_field($_POST['participant_phone'] ?? '');

        // Check if phone is optional
        $phone_optional = get_option('wp_dynamic_survey_phone_optional', '0');

        // Validate phone number (only if not optional or if provided)
        if (empty($participant_phone) && $phone_optional != '1') {
            wp_send_json_error(__('Phone number is required.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Update participant with phone number
        $participant_manager = new WP_Dynamic_Survey_Participant_Manager();

        // Get participant by session ID from stage 1
        $session_id = $stage1_data['session_id'] ?? '';
        if (empty($session_id)) {
            wp_send_json_error(__('Invalid session. Please start over.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        $participant = $participant_manager->get_participant($session_id);
        if (!$participant) {
            wp_send_json_error(__('Session not found. Please start over.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

         if (empty($participant_phone) && $phone_optional != '1') {
            // Update participant with phone number using participant manager
            $result = $participant_manager->update_phone_number($session_id, $participant_phone);
            
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }

        }

        // Get first question to start survey
        $question_manager = new WP_Dynamic_Survey_Question_Manager();
        $questions = $question_manager->get_survey_questions($survey_id, true);

        if (empty($questions)) {
            wp_send_json_error(__('No questions found for this survey.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        $first_question = $questions[0];

        // Get survey details
        $survey_manager = new WP_Dynamic_Survey_Manager();
        $survey = $survey_manager->get_survey($survey_id);

        wp_send_json_success(array(
            'session_id' => $session_id,
            'participant_id' => $participant['id'],
            'first_question' => $first_question,
            'survey_title' => $survey['title'],
            'survey_description' => $survey['description'],
            'total_questions' => count($questions)
        ));
    }

    /**
     * Handle survey answer submission
     */
    public function handle_answer_submission() {
        check_ajax_referer('wp_dynamic_survey_frontend_nonce', 'nonce');

        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        $question_id = intval($_POST['question_id'] ?? 0);
        $answer_id = !empty($_POST['answer_id']) ? intval($_POST['answer_id']) : null;
        $answer_text = sanitize_textarea_field($_POST['answer_text'] ?? '');

        // Validate session
        $participant_manager = new WP_Dynamic_Survey_Participant_Manager();
        $participant = $participant_manager->validate_session($session_id);

        if (is_wp_error($participant)) {
            wp_send_json_error($participant->get_error_message());
        }

        // Record response
        $session_manager = new WP_Dynamic_Survey_Session_Manager();
        $answer_data = array(
            'answer_id' => $answer_id,
            'answer_text' => $answer_text
        );

        $questionManger = new WP_Dynamic_Survey_Question_Manager();
        $question = $questionManger->get_question($question_id);

        if (!$question) {
            wp_send_json_error(__('Question not found.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        $result = $session_manager->record_response($participant, $question, $answer_data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        // Determine next step
        $next_step = $this->get_next_step($session_id, $question_id, $answer_id);

        wp_send_json_success($next_step);
    }

    /**
     * Handle skip question (for optional questions)
     */
    public function handle_skip_question() {
        check_ajax_referer('wp_dynamic_survey_frontend_nonce', 'nonce');

        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        $question_id = intval($_POST['question_id'] ?? 0);

        // Validate session
        $participant_manager = new WP_Dynamic_Survey_Participant_Manager();
        $participant = $participant_manager->validate_session($session_id);

        if (is_wp_error($participant)) {
            wp_send_json_error($participant->get_error_message());
        }

        // Get question details
        $question_manager = new WP_Dynamic_Survey_Question_Manager();
        $question = $question_manager->get_question($question_id, true);

        if (!$question) {
            wp_send_json_error(__('Question not found.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Verify question is optional (not required)
        if (filter_var($question['is_required'], FILTER_VALIDATE_BOOLEAN)) {
            wp_send_json_error(__('This question is required and cannot be skipped.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Record the skip as a response with null answer
        $session_manager = new WP_Dynamic_Survey_Session_Manager();
        $answer_data = array(
            'answer_id' => null,
            'answer_text' => '',
            'skipped' => true
        );

        $result = $session_manager->record_response($participant, $question, $answer_data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        // Determine next step based on skip_next_question_id
        if (!empty($question['skip_next_question_id'])) {
            $next_question = $question_manager->get_question($question['skip_next_question_id'], true);
            if ($next_question) {
                wp_send_json_success(array(
                    'type' => 'question',
                    'question' => $next_question
                ));
            }
        }

        // No skip destination set - end survey
        $session_manager->mark_survey_complete($session_id);
        $completion_response = $this->handle_survey_completion($session_id);

        wp_send_json_success($completion_response);
    }

    /**
     * Get next step in survey flow
     */
    private function get_next_step($session_id, $current_question_id, $answer_id) {
        $question_manager = new WP_Dynamic_Survey_Question_Manager();

        // Get current question and answer
        $question = $question_manager->get_question($current_question_id, true);
        $selected_answer = null;

        if ($answer_id) {
            foreach ($question['answers'] as $answer) {
                if ($answer['id'] == $answer_id) {
                    $selected_answer = $answer;
                    break;
                }
            }
        }

        // Check for answer-level routing first
        if ($selected_answer) {
            if (!empty($selected_answer['redirect_url'])) {
                $redirects = [];
                if (empty($selected_answer['next_question_id'])) {
                    $redirects = $this->handle_survey_completion($session_id);
                }
                
                return array_merge($redirects, array(
                    'type' => 'redirect',
                    'url' => $selected_answer['redirect_url'],
                    'question' => $selected_answer['next_question_id'] ? 
                        $question_manager->get_question($selected_answer['next_question_id'], true) : null
                ));
            }

            if (!empty($selected_answer['next_question_id'])) {
                $next_question = $question_manager->get_question($selected_answer['next_question_id'], true);
                if ($next_question) {
                    return array(
                        'type' => 'question',
                        'question' => $next_question
                    );
                }
            }
        }

        // No more questions - survey complete
        $session_manager = new WP_Dynamic_Survey_Session_Manager();
        $session_manager->mark_survey_complete($session_id);

        return $this->handle_survey_completion($session_id);
    }

    /**
     * Handle survey completion - check for custom thank you page and redirect
     *
     * @param string $session_id Session ID
     * @return array Completion response array
     */
    private function handle_survey_completion($session_id) {
        $participant_manager = new WP_Dynamic_Survey_Participant_Manager();
        $participant = $participant_manager->get_participant($session_id);

        if (!$participant) {
            return array(
                'type' => 'complete',
                'session_id' => $session_id
            );
        }

        $survey_manager = new WP_Dynamic_Survey_Manager();
        $survey = $survey_manager->get_survey($participant['survey_id']);

        // If survey has custom thank you page, generate token and redirect
        if ($survey && !empty($survey['thank_you_page_slug'])) {
            $token = $participant_manager->generate_completion_token($session_id);

            if (!is_wp_error($token)) {
                // Check if page exists
                $thank_you_page = get_page_by_path($survey['thank_you_page_slug']);
                if ($thank_you_page) {
                    $redirect_url = get_permalink($thank_you_page->ID) . '?token=' . $token;
                    return array(
                        'type' => 'complete',
                        'redirect_url' => $redirect_url,
                        'session_id' => $session_id
                    );
                }
            }
        }

        // Default completion response
        return array(
            'type' => 'complete',
            'session_id' => $session_id
        );
    }

    /**
     * Render participant form
     */
    public function render_participant_form($survey) {
        ob_start();
        include WP_DYNAMIC_SURVEY_PATH . 'public/templates/participant-form.php';
        return ob_get_clean();
    }

    /**
     * Handle get completion data AJAX request
     */
    public function handle_get_completion_data() {
        check_ajax_referer('wp_dynamic_survey_frontend_nonce', 'nonce');

        $session_id = sanitize_text_field($_POST['session_id'] ?? '');

        if (empty($session_id)) {
            wp_send_json_error(__('Session ID is required.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Validate session
        $participant_manager = new WP_Dynamic_Survey_Participant_Manager();
        $participant = $participant_manager->get_participant($session_id);

        if (!$participant) {
            wp_send_json_error(__('Session not found.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Get survey data
        $survey_manager = new WP_Dynamic_Survey_Manager();
        $survey = $survey_manager->get_survey($participant['survey_id']);

        if (!$survey) {
            wp_send_json_error(__('Survey not found.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Get session responses
        $session_manager = new WP_Dynamic_Survey_Session_Manager();
        $responses = $session_manager->get_session_responses($session_id);

        // Calculate completion metrics
        $completion_time_seconds = $this->calculate_completion_time($participant);
        $completion_time_formatted = $this->format_completion_time($completion_time_seconds);

        // Prepare completion data
        $completion_data = array(
            'survey' => array(
                'title' => $survey['title'],
                'description' => $survey['description'],
                'created_at' => $survey['created_at']
            ),
            'participant' => array(
                'name' => $participant['participant_name'],
                'email' => $participant['participant_email'],
                'phone' => $participant['participant_phone'] ?? '',
                'address' => $participant['participant_address'] ?? '',
                'started_at' => $participant['started_at'],
                'completed_at' => $participant['completed_at']
            ),
            'completion' => array(
                'total_questions' => count($responses),
                'completion_time' => $completion_time_seconds,
                'completion_time_formatted' => $completion_time_formatted,
                'average_time_per_question' => $this->calculate_average_time_per_question($participant, count($responses))
            ),
            'responses' => $this->format_responses_for_display($responses)
        );

        wp_send_json_success($completion_data);
    }

    /**
     * Handle track completion AJAX request
     */
    public function handle_track_completion() {
        check_ajax_referer('wp_dynamic_survey_frontend_nonce', 'nonce');

        $session_id = sanitize_text_field($_POST['session_id'] ?? '');

        if (empty($session_id)) {
            wp_send_json_error(__('Session ID is required.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Mark participant as completed using the participant manager
        $participant_manager = new WP_Dynamic_Survey_Participant_Manager();
        $result = $participant_manager->mark_completed($session_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(array(
            'tracked' => true
        ));
    }

    /**
     * Calculate completion time
     */
    private function calculate_completion_time($participant) {
        if (!$participant['completed_at']) {
            return 0;
        }

        $start_time = strtotime($participant['created_at']);
        $end_time = strtotime($participant['completed_at']);

        return $end_time - $start_time;
    }

    /**
     * Calculate average time per question
     */
    private function calculate_average_time_per_question($participant, $question_count) {
        if ($question_count === 0) {
            return 0;
        }

        $total_time = $this->calculate_completion_time($participant);
        return $total_time / $question_count;
    }

    /**
     * Format responses for display
     */
    private function format_responses_for_display($responses) {
        $question_manager = new WP_Dynamic_Survey_Question_Manager();
        $formatted_responses = array();

        foreach ($responses as $response) {
            $question = $question_manager->get_question($response['question_id'], true);

            if (!$question) {
                continue;
            }

            $formatted_response = array(
                'question_id' => $response['question_id'],
                'question_title' => $question['title'],
                'question_type' => $question['question_type'],
                'response_time' => $response['created_at']
            );

            if ($response['answer_id']) {
                // Find the answer text
                foreach ($question['answers'] as $answer) {
                    if ($answer['id'] == $response['answer_id']) {
                        $formatted_response['answer_text'] = $answer['answer_text'];
                        break;
                    }
                }
            }

            if ($response['answer_text']) {
                $formatted_response['custom_text'] = $response['answer_text'];
            }

            $formatted_responses[] = $formatted_response;
        }

        return $formatted_responses;
    }

    /**
     * Format completion time in a human-readable format
     */
    private function format_completion_time($seconds) {
        if ($seconds < 60) {
            return sprintf(_n('%d second', '%d seconds', $seconds, WP_DYNAMIC_SURVEY_TEXT_DOMAIN), $seconds);
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remaining_seconds = $seconds % 60;
            if ($remaining_seconds > 0) {
                return sprintf(__('%d minutes %d seconds', WP_DYNAMIC_SURVEY_TEXT_DOMAIN), $minutes, $remaining_seconds);
            } else {
                return sprintf(_n('%d minute', '%d minutes', $minutes, WP_DYNAMIC_SURVEY_TEXT_DOMAIN), $minutes);
            }
        } else {
            $hours = floor($seconds / 3600);
            $remaining_minutes = floor(($seconds % 3600) / 60);
            if ($remaining_minutes > 0) {
                return sprintf(__('%d hours %d minutes', WP_DYNAMIC_SURVEY_TEXT_DOMAIN), $hours, $remaining_minutes);
            } else {
                return sprintf(_n('%d hour', '%d hours', $hours, WP_DYNAMIC_SURVEY_TEXT_DOMAIN), $hours);
            }
        }
    }

    /**
     * Generate download link for response summary
     */
    private function generate_download_link($session_id) {
        $download_url = add_query_arg(array(
            'action' => 'wp_dynamic_survey_download_summary',
            'session_id' => $session_id,
            'nonce' => wp_create_nonce('wp_dynamic_survey_download_' . $session_id)
        ), admin_url('admin-ajax.php'));

        return '<a href="' . esc_url($download_url) . '" class="wp-dynamic-survey-download-link button button-primary" target="_blank">' .
               esc_html__('Download Your Responses', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) . '</a>';
    }

    /**
     * Handle token validation for thank you pages
     */
    public function handle_token_access() {
        global $post;

        if (!$post || !is_page()) {
            return;
        }

        // Check if this page is a thank you page
        $survey_manager = new WP_Dynamic_Survey_Manager();
        $surveys = $survey_manager->get_surveys(['status' => 'published']);

        $matching_survey = null;
        foreach ($surveys as $survey) {
            if (!empty($survey['thank_you_page_slug']) && $post->post_name === $survey['thank_you_page_slug']) {
                $matching_survey = $survey;
                break;
            }
        }

        // If this is not a thank you page, continue normally
        if (!$matching_survey) {
            return;
        }

        // This is a thank you page - validate token
        $token = $_GET['token'] ?? null;

        if (!$token) {
            // No token - store error for content filter
            global $wp_dynamic_survey_access_error;
            $wp_dynamic_survey_access_error = __('Access denied. This page requires a valid access token.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
            return;
        }

        // Validate the token
        $participant_manager = new WP_Dynamic_Survey_Participant_Manager();
        $participant = $participant_manager->validate_completion_token($token);

        if (is_wp_error($participant)) {
            global $wp_dynamic_survey_access_error;
            $wp_dynamic_survey_access_error = __('Invalid or expired access token.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
            return;
        }

        // Check if token belongs to the correct survey
        if ($participant['survey_id'] != $matching_survey['id']) {
            global $wp_dynamic_survey_access_error;
            $wp_dynamic_survey_access_error = __('Access token is not valid for this page.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
            return;
        }

        // Token is valid - store completion data
        global $wp_dynamic_survey_completion_data;
        $wp_dynamic_survey_completion_data = array(
            'survey' => $matching_survey,
            'participant' => $participant,
            'token' => $token
        );
    }

    /**
     * Filter content for thank you pages to show access denied or completion data
     */
    public function filter_thank_you_page_content($content) {
        global $post, $wp_dynamic_survey_access_error, $wp_dynamic_survey_completion_data;

        if (!$post || !is_page()) {
            return $content;
        }

        // Check if this page is a thank you page
        $survey_manager = new WP_Dynamic_Survey_Manager();
        $surveys = $survey_manager->get_surveys(['status' => 'published']);

        $is_thank_you_page = false;
        foreach ($surveys as $survey) {
            if (!empty($survey['thank_you_page_slug']) && $post->post_name === $survey['thank_you_page_slug']) {
                $is_thank_you_page = true;
                break;
            }
        }

        if (!$is_thank_you_page) {
            return $content;
        }

        // If there's an access error, show it instead of content
        if ($wp_dynamic_survey_access_error) {
            return '<div class="wp-dynamic-survey-access-denied">' .
                   '<h2>' . __('Access Denied', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) . '</h2>' .
                   '<p>' . esc_html($wp_dynamic_survey_access_error) . '</p>' .
                   '</div>';
        }

        // If we have completion data, just return the original content without enhancement
        if ($wp_dynamic_survey_completion_data) {
            return $content;
        }

        return $content;
    }
}