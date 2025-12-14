<?php
/**
 * Survey Builder Admin Interface for WP Dynamic Survey Plugin
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Survey Builder Admin class
 */
class FlowQ_Builder_Admin {

    /**
     * Survey manager instance
     */
    private $survey_manager;

    /**
     * Question manager instance
     */
    private $question_manager;

    /**
     * Constructor
     */
    public function __construct() {
        $this->survey_manager = new FlowQ_Survey_Manager();
        $this->question_manager = new FlowQ_Question_Manager();
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // AJAX handlers for builder
        add_action('wp_ajax_flowq_save_question', array($this, 'ajax_save_question'));
        add_action('wp_ajax_flowq_delete_question', array($this, 'ajax_delete_question'));
        add_action('wp_ajax_flowq_save_answer', array($this, 'ajax_save_answer'));
        add_action('wp_ajax_flowq_delete_answer', array($this, 'ajax_delete_answer'));
        add_action('wp_ajax_flowq_reorder_questions', array($this, 'ajax_reorder_questions'));
        add_action('wp_ajax_flowq_reorder_answers', array($this, 'ajax_reorder_answers'));
        add_action('wp_ajax_flowq_preview', array($this, 'ajax_preview_survey'));

        // Enqueue builder assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_builder_assets'));
    }

    /**
     * Render survey builder interface
     *
     * @param int $survey_id Survey ID
     */
    public function render_survey_builder($survey_id) {
        $survey = $this->survey_manager->get_survey($survey_id);
        if (!$survey) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Survey not found.', 'flowq') . '</p></div>';
            return;
        }

        $questions = $this->question_manager->get_survey_questions($survey_id, true);

        include FLOWQ_PATH . 'admin/templates/survey-builder.php';
    }

    /**
     * Enqueue builder-specific assets
     */
    public function enqueue_builder_assets($hook) {
        if (strpos($hook, 'flowq') === false) {
            return;
        }

        // jQuery UI for drag and drop
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');

        // Builder JavaScript
        wp_enqueue_script(
            'flowq-builder',
            FLOWQ_URL . 'assets/js/survey-builder.js',
            array('jquery', 'jquery-ui-sortable', 'wp-util'),
            FLOWQ_VERSION,
            true
        );

        // Builder CSS
        wp_enqueue_style(
            'flowq-builder',
            FLOWQ_URL . 'assets/css/survey-builder.css',
            array(),
            FLOWQ_VERSION
        );

        // Localize builder script
        wp_localize_script('flowq-builder', 'flowqSurveyBuilder', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('flowq_builder_nonce'),
            'strings' => array(
                'add_question' => __('Add Question', 'flowq'),
                'edit_question' => __('Edit Question', 'flowq'),
                'delete_question' => __('Delete Question', 'flowq'),
                'add_answer' => __('Add Answer', 'flowq'),
                'edit_answer' => __('Edit Answer', 'flowq'),
                'delete_answer' => __('Delete Answer', 'flowq'),
                'confirm_delete_question' => __('Are you sure you want to delete this question?', 'flowq'),
                'confirm_delete_answer' => __('Are you sure you want to delete this answer?', 'flowq'),
                'saving' => __('Saving...', 'flowq'),
                'saved' => __('Saved!', 'flowq'),
                'error' => __('Error occurred. Please try again.', 'flowq'),
                'required_field' => __('This field is required.', 'flowq'),
                'invalid_url' => __('Please enter a valid URL.', 'flowq')
            ),
            'question_types' => array()
        ));
    }

    /**
     * AJAX: Save question
     */
    public function ajax_save_question() {
        check_ajax_referer('flowq_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $question_id = intval($_POST['question_id']);
        $survey_id = intval($_POST['survey_id']);

        $question_data = array(
            'title' => sanitize_textarea_field($_POST['question_title']),
            'description' => sanitize_textarea_field($_POST['question_description']),
            // Note: question_order and next_question_id removed from questions table
            // redirect_url is only for answers, not questions
        );

        if ($question_id) {
            // Update existing question
            $result = $this->question_manager->update_question($question_id, $question_data);
        } else {
            // Create new question
            $result = $this->question_manager->create_question($survey_id, $question_data);
            if (!is_wp_error($result)) {
                $question_id = $result;
            }
        }

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        // Get updated question data
        $question = $this->question_manager->get_question($question_id, true);

        wp_send_json_success(array(
            'question' => $question,
            'message' => __('Question saved successfully.', 'flowq')
        ));
    }

    /**
     * AJAX: Delete question
     */
    public function ajax_delete_question() {
        check_ajax_referer('flowq_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $question_id = intval($_POST['question_id']);
        $result = $this->question_manager->delete_question($question_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(array(
            'message' => __('Question deleted successfully.', 'flowq')
        ));
    }

    /**
     * AJAX: Save answer
     */
    public function ajax_save_answer() {
        check_ajax_referer('flowq_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $answer_id = intval($_POST['answer_id']);
        $question_id = intval($_POST['question_id']);

        $answer_data = array(
            'answer_text' => sanitize_textarea_field($_POST['answer_text']),
            'answer_value' => sanitize_text_field($_POST['answer_value']),
            'answer_order' => intval($_POST['answer_order']),
            'next_question_id' => !empty($_POST['next_question_id']) ? intval($_POST['next_question_id']) : null,
            'redirect_url' => !empty($_POST['redirect_url']) ? esc_url_raw($_POST['redirect_url']) : null
        );

        if ($answer_id) {
            // Update existing answer
            $result = $this->question_manager->update_answer($answer_id, $answer_data);
        } else {
            // Create new answer
            $result = $this->question_manager->create_answer($question_id, $answer_data);
            if (!is_wp_error($result)) {
                $answer_id = $result;
            }
        }

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        // Get updated answers for the question
        $answers = $this->question_manager->get_question_answers($question_id);

        wp_send_json_success(array(
            'answer_id' => $answer_id,
            'answers' => $answers,
            'message' => __('Answer saved successfully.', 'flowq')
        ));
    }

    /**
     * AJAX: Delete answer
     */
    public function ajax_delete_answer() {
        check_ajax_referer('flowq_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $answer_id = intval($_POST['answer_id']);
        $result = $this->question_manager->delete_answer($answer_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(array(
            'message' => __('Answer deleted successfully.', 'flowq')
        ));
    }

    /**
     * AJAX: Reorder questions
     */
    public function ajax_reorder_questions() {
        check_ajax_referer('flowq_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        // Note: question_order column was removed, questions are ordered by ID
        // This reordering functionality is no longer needed
        $result = true;

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(array(
            'message' => __('Questions reordered successfully.', 'flowq')
        ));
    }

    /**
     * AJAX: Reorder answers
     */
    public function ajax_reorder_answers() {
        check_ajax_referer('flowq_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        // Validate and sanitize input
        if (!isset($_POST['answers']) || !is_array($_POST['answers'])) {
            wp_send_json_error(__('Invalid answers data', 'flowq'));
        }

        // Sanitize the array first before iterating
        $answers_raw = array_map('absint', (array) $_POST['answers']);

        $answer_orders = array();
        foreach ($answers_raw as $index => $answer_id) {
            $index = absint($index);
            $answer_id = absint($answer_id);
            $answer_orders[$answer_id] = $index + 1;
        }

        $result = $this->question_manager->reorder_answers($answer_orders);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(array(
            'message' => __('Answers reordered successfully.', 'flowq')
        ));
    }

    /**
     * AJAX: Preview survey
     */
    public function ajax_preview_survey() {
        check_ajax_referer('flowq_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $survey_id = intval($_POST['survey_id']);
        $survey = $this->survey_manager->get_survey($survey_id);
        $questions = $this->question_manager->get_survey_questions($survey_id, true);

        if (!$survey) {
            wp_send_json_error('Survey not found');
        }

        // Validate survey flow
        $validation_result = $this->survey_manager->validate_survey_flow($survey_id);

        ob_start();
        include FLOWQ_PATH . 'admin/templates/survey-preview.php';
        $preview_html = ob_get_clean();

        wp_send_json_success(array(
            'preview_html' => $preview_html,
            'validation_issues' => $validation_result,
            'questions_count' => count($questions)
        ));
    }

    /**
     * Get available question types (simplified to single choice only)
     */
    private function get_question_types() {
        return array(
            'single_choice' => array(
                'label' => __('Single Choice', 'flowq'),
                'description' => __('Single selection from multiple options', 'flowq'),
                'icon' => 'dashicons-list-view',
                'has_answers' => true
            )
        );
    }

    /**
     * Get question type configuration
     */
    public function get_question_type_config($type) {
        $types = $this->get_question_types();
        return isset($types[$type]) ? $types[$type] : null;
    }

    /**
     * Validate question data
     */
    public function validate_question_data($data) {
        $errors = array();

        if (empty($data['title'])) {
            $errors[] = __('Question title is required.', 'flowq');
        }


        if (!empty($data['redirect_url']) && !filter_var($data['redirect_url'], FILTER_VALIDATE_URL)) {
            $errors[] = __('Invalid redirect URL.', 'flowq');
        }

        return $errors;
    }

    /**
     * Validate answer data
     */
    public function validate_answer_data($data) {
        $errors = array();

        if (empty($data['answer_text'])) {
            $errors[] = __('Answer text is required.', 'flowq');
        }

        if (!empty($data['redirect_url']) && !filter_var($data['redirect_url'], FILTER_VALIDATE_URL)) {
            $errors[] = __('Invalid redirect URL.', 'flowq');
        }

        return $errors;
    }

    /**
     * Generate default answers for questions (always single choice)
     */
    public function get_default_answers($question_type = 'single_choice') {
        return array(
            array('answer_text' => __('Option 1', 'flowq'), 'answer_value' => 'option1'),
            array('answer_text' => __('Option 2', 'flowq'), 'answer_value' => 'option2')
        );
    }

    /**
     * Export survey structure for backup/import
     */
    public function export_survey_structure($survey_id) {
        $survey = $this->survey_manager->get_survey($survey_id);
        $questions = $this->question_manager->get_survey_questions($survey_id, true);

        if (!$survey) {
            return false;
        }

        return array(
            'survey' => $survey,
            'questions' => $questions,
            'export_date' => current_time('mysql'),
            'plugin_version' => FLOWQ_VERSION
        );
    }
}