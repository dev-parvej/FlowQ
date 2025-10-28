<?php
/**
 * Question Management Admin for WP Dynamic Survey Plugin
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Question Admin class for handling question management operations
 */
class FlowQ_Question_Admin {

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
        // Question management AJAX handlers
        add_action('wp_ajax_flowq_get_question', array($this, 'ajax_get_question'));
        add_action('wp_ajax_flowq_create_question', array($this, 'ajax_create_question'));
        add_action('wp_ajax_flowq_update_question', array($this, 'ajax_update_question'));
        add_action('wp_ajax_flowq_delete_question', array($this, 'ajax_delete_question'));
        add_action('wp_ajax_flowq_duplicate_question', array($this, 'ajax_duplicate_question'));
        add_action('wp_ajax_flowq_reorder_questions', array($this, 'ajax_reorder_questions'));
        add_action('wp_ajax_flowq_update_answer_next_question', array($this, 'ajax_update_answer_next_question'));
        add_action('wp_ajax_flowq_update_question_skip_destination', array($this, 'ajax_update_question_skip_destination'));
    }

    /**
     * AJAX: Get question data
     */
    public function ajax_get_question() {
        check_ajax_referer('flowq_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', FLOWQ_TEXT_DOMAIN));
        }

        $question_id = intval($_POST['question_id']);
        if (!$question_id) {
            wp_send_json_error(__('Invalid question ID.', FLOWQ_TEXT_DOMAIN));
        }

        $question_manager = new FlowQ_Question_Manager();
        $question = $question_manager->get_question($question_id);

        if (is_wp_error($question)) {
            wp_send_json_error($question->get_error_message());
        }

        // Get answers for this question
        $answers = $question_manager->get_question_answers($question_id);
        $question['answers'] = $answers;

        wp_send_json_success($question);
    }

    /**
     * AJAX: Create new question
     */
    public function ajax_create_question() {
        check_ajax_referer('flowq_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', FLOWQ_TEXT_DOMAIN));
        }

        $survey_id = intval($_POST['survey_id']);
        if (!$survey_id) {
            wp_send_json_error(__('Invalid survey ID.', FLOWQ_TEXT_DOMAIN));
        }

        // Prepare question data
        $question_data = array(
            'title' => sanitize_textarea_field($_POST['question_title']),
            'description' => sanitize_textarea_field($_POST['question_description']),
            'extra_message' => sanitize_textarea_field($_POST['question_extra_message'] ?? ''),
            'is_required' => isset($_POST['question_is_required']) ? intval($_POST['question_is_required']) : 1,
            'skip_next_question_id' => !empty($_POST['question_skip_next_question_id']) ? intval($_POST['question_skip_next_question_id']) : null
        );

        $question_manager = new FlowQ_Question_Manager();
        $question_id = $question_manager->create_question($survey_id, $question_data);

        if (is_wp_error($question_id)) {
            wp_send_json_error($question_id->get_error_message());
        }

        // Create answer options if provided
        $this->save_answer_options($question_id, $_POST);

        wp_send_json_success(array(
            'question_id' => $question_id,
            'message' => __('Question created successfully.', FLOWQ_TEXT_DOMAIN)
        ));
    }

    /**
     * AJAX: Update existing question
     */
    public function ajax_update_question() {
        check_ajax_referer('flowq_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', FLOWQ_TEXT_DOMAIN));
        }

        $question_id = intval($_POST['question_id']);
        if (!$question_id) {
            wp_send_json_error(__('Invalid question ID.', FLOWQ_TEXT_DOMAIN));
        }

        // Prepare question data
        $question_data = array(
            'title' => sanitize_textarea_field($_POST['question_title']),
            'description' => sanitize_textarea_field($_POST['question_description']),
            'extra_message' => sanitize_textarea_field($_POST['question_extra_message'] ?? ''),
            'is_required' => isset($_POST['question_is_required']) ? intval($_POST['question_is_required']) : 1,
            'skip_next_question_id' => !empty($_POST['question_skip_next_question_id']) ? intval($_POST['question_skip_next_question_id']) : null
        );

        $question_manager = new FlowQ_Question_Manager();
        $result = $question_manager->update_question($question_id, $question_data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        // Update answer options
        $this->save_answer_options($question_id, $_POST);

        wp_send_json_success(array(
            'message' => __('Question updated successfully.', FLOWQ_TEXT_DOMAIN)
        ));
    }

    /**
     * AJAX: Delete question
     */
    public function ajax_delete_question() {
        check_ajax_referer('flowq_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', FLOWQ_TEXT_DOMAIN));
        }

        $question_id = intval($_POST['question_id']);
        if (!$question_id) {
            wp_send_json_error(__('Invalid question ID.', FLOWQ_TEXT_DOMAIN));
        }

        // Check for dependencies before deleting
        $this->check_question_dependencies($question_id);

        $question_manager = new FlowQ_Question_Manager();
        $result = $question_manager->delete_question($question_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(array(
            'message' => __('Question deleted successfully.', FLOWQ_TEXT_DOMAIN)
        ));
    }

    /**
     * AJAX: Duplicate question
     */
    public function ajax_duplicate_question() {
        check_ajax_referer('flowq_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', FLOWQ_TEXT_DOMAIN));
        }

        $question_id = intval($_POST['question_id']);
        if (!$question_id) {
            wp_send_json_error(__('Invalid question ID.', FLOWQ_TEXT_DOMAIN));
        }

        $question_manager = new FlowQ_Question_Manager();

        // Get original question
        $original_question = $question_manager->get_question($question_id);
        if (is_wp_error($original_question)) {
            wp_send_json_error($original_question->get_error_message());
        }

        // Create duplicate question data
        $duplicate_data = array(
            'title' => $original_question['title'] . ' (Copy)',
            'description' => $original_question['description'],
            'extra_message' => $original_question['extra_message'] ?? '',
            'is_required' => $original_question['is_required'] ?? 1,
            'skip_next_question_id' => $original_question['skip_next_question_id'] ?? null
        );

        $new_question_id = $question_manager->create_question($original_question['survey_id'], $duplicate_data);

        if (is_wp_error($new_question_id)) {
            wp_send_json_error($new_question_id->get_error_message());
        }

        // Duplicate answers
        $original_answers = $question_manager->get_question_answers($question_id);
        foreach ($original_answers as $answer) {
            $answer_data = array(
                'answer_text' => $answer['answer_text'],
                'answer_value' => $answer['answer_value'],
                'redirect_url' => $answer['redirect_url'],
                'answer_order' => $answer['answer_order']
            );
            $question_manager->create_answer($new_question_id, $answer_data);
        }

        wp_send_json_success(array(
            'question_id' => $new_question_id,
            'message' => __('Question duplicated successfully.', FLOWQ_TEXT_DOMAIN)
        ));
    }

    /**
     * AJAX: Reorder questions
     */
    public function ajax_reorder_questions() {
        check_ajax_referer('flowq_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', FLOWQ_TEXT_DOMAIN));
        }

        $survey_id = intval($_POST['survey_id']);
        $question_orders = json_decode(stripslashes($_POST['question_orders']), true);

        if (!$survey_id || !is_array($question_orders)) {
            wp_send_json_error(__('Invalid data provided.', FLOWQ_TEXT_DOMAIN));
        }

        $question_manager = new FlowQ_Question_Manager();

        // Note: question_order column was removed, questions are ordered by ID
        // This reordering functionality is no longer needed

        wp_send_json_success(array(
            'message' => __('Questions reordered successfully.', FLOWQ_TEXT_DOMAIN)
        ));
    }

    /**
     * Save answer options for a question
     */
    private function save_answer_options($question_id, $post_data) {
        if (!isset($post_data['answer_text']) || !is_array($post_data['answer_text'])) {
            return;
        }

        $question_manager = new FlowQ_Question_Manager();

        // Get existing answers
        $existing_answers = $question_manager->get_question_answers($question_id);
        $existing_answer_ids = array_column($existing_answers, 'id');

        $answer_texts = $post_data['answer_text'];
        $answer_ids = isset($post_data['answer_id']) ? $post_data['answer_id'] : array();
        $next_question_ids = isset($post_data['next_question_id']) ? $post_data['next_question_id'] : array();
        $redirect_urls = isset($post_data['answer_redirect_url']) ? $post_data['answer_redirect_url'] : array();

        $processed_answer_ids = array();

        for ($i = 0; $i < count($answer_texts); $i++) {
            $answer_text = trim($answer_texts[$i]);
            if (empty($answer_text)) {
                continue;
            }

            $answer_data = array(
                'answer_text' => sanitize_text_field($answer_text),
                'next_question_id' => !empty($next_question_ids[$i]) ? intval($next_question_ids[$i]) : null,
                'redirect_url' => !empty($redirect_urls[$i]) ? esc_url_raw($redirect_urls[$i]) : null,
                'answer_order' => $i + 1
            );

            $answer_id = !empty($answer_ids[$i]) ? intval($answer_ids[$i]) : 0;

            if ($answer_id && in_array($answer_id, $existing_answer_ids)) {
                // Update existing answer
                $question_manager->update_answer($answer_id, $answer_data);
                $processed_answer_ids[] = $answer_id;
            } else {
                // Create new answer
                $new_answer_id = $question_manager->create_answer($question_id, $answer_data);
                if (!is_wp_error($new_answer_id)) {
                    $processed_answer_ids[] = $new_answer_id;
                }
            }
        }

        // Delete answers that were removed
        foreach ($existing_answer_ids as $existing_id) {
            if (!in_array($existing_id, $processed_answer_ids)) {
                $question_manager->delete_answer($existing_id);
            }
        }
    }

    /**
     * Check for question dependencies before deletion
     */
    private function check_question_dependencies($question_id) {
        global $wpdb;

        $table_prefix = $wpdb->prefix . 'flowq_';

        // Check if any answers point to this question as next question
        $dependent_answers = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_prefix}answers WHERE next_question_id = %d",
            $question_id
        ));

        if ($dependent_answers > 0) {
            wp_send_json_error(__('Cannot delete this question because other questions reference it in their flow logic. Please update the question flow first.', FLOWQ_TEXT_DOMAIN));
        }

        // Check if any questions point to this question as skip destination
        $dependent_questions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_prefix}questions WHERE skip_next_question_id = %d",
            $question_id
        ));

        if ($dependent_questions > 0) {
            wp_send_json_error(__('Cannot delete this question because other questions reference it as a skip destination. Please update the question skip settings first.', FLOWQ_TEXT_DOMAIN));
        }

        // Check if there are any responses for this question
        $responses_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_prefix}responses WHERE question_id = %d",
            $question_id
        ));

        if ($responses_count > 0) {
            wp_send_json_error(sprintf(
                __('Cannot delete this question because it has %d response(s). Deleting it would affect survey analytics.', FLOWQ_TEXT_DOMAIN),
                $responses_count
            ));
        }
    }

    /**
     * AJAX: Update answer next question
     */
    public function ajax_update_answer_next_question() {
        check_ajax_referer('flowq_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', FLOWQ_TEXT_DOMAIN));
        }

        $answer_id = intval($_POST['answer_id']);
        $next_question_id = !empty($_POST['next_question_id']) ? intval($_POST['next_question_id']) : null;

        if (!$answer_id) {
            wp_send_json_error(__('Invalid answer ID.', FLOWQ_TEXT_DOMAIN));
        }

        $question_manager = new FlowQ_Question_Manager();

        // Update the answer
        $result = $question_manager->update_answer($answer_id, array(
            'next_question_id' => $next_question_id
        ));

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(array(
            'message' => __('Next question updated successfully.', FLOWQ_TEXT_DOMAIN),
            'next_question_id' => $next_question_id
        ));
    }

    /**
     * AJAX: Update question skip destination
     */
    public function ajax_update_question_skip_destination() {
        check_ajax_referer('flowq_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', FLOWQ_TEXT_DOMAIN));
        }

        $question_id = intval($_POST['question_id']);
        $skip_next_question_id = !empty($_POST['skip_next_question_id']) ? intval($_POST['skip_next_question_id']) : null;

        if (!$question_id) {
            wp_send_json_error(__('Invalid question ID.', FLOWQ_TEXT_DOMAIN));
        }

        $question_manager = new FlowQ_Question_Manager();

        // Update the question skip destination
        $result = $question_manager->update_question($question_id, array(
            'skip_next_question_id' => $skip_next_question_id
        ));

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(array(
            'message' => __('Skip destination updated successfully.', FLOWQ_TEXT_DOMAIN),
            'skip_next_question_id' => $skip_next_question_id
        ));
    }
}

// Initialize the class
new FlowQ_Question_Admin();