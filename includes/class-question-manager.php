<?php
/**
 * Question Manager for WP Dynamic Survey Plugin
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Survey Question Manager class
 */
class FlowQ_Question_Manager {

    /**
     * Plugin table prefix
     */
    private $table_prefix;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_prefix = $wpdb->prefix . 'flowq_';
    }

    /**
     * Create a new question
     *
     * @param int $survey_id Survey ID
     * @param array $question_data Question data
     * @return int|WP_Error Question ID on success, WP_Error on failure
     */
    public function create_question($survey_id, $question_data) {
        global $wpdb;

        // Validate required fields
        if (empty($question_data['title'])) {
            return new WP_Error('missing_title', __('Question title is required.', 'flowq'));
        }

        // Check if survey exists
        $survey_manager = new FlowQ_Survey_Manager();
        if (!$survey_manager->get_survey($survey_id)) {
            return new WP_Error('survey_not_found', __('Survey not found.', 'flowq'));
        }

        // Prepare question data
        $question_record = array(
            'survey_id' => $survey_id,
            'title' => sanitize_textarea_field($question_data['title']),
            'description' => sanitize_textarea_field($question_data['description'] ?? ''),
            'extra_message' => sanitize_textarea_field($question_data['extra_message'] ?? ''),
            'type' => 'single_choice', // Always single choice
            'is_required' => !isset($question_data['is_required']) ? 0 : intval($question_data['is_required']),
            'skip_next_question_id' => isset($question_data['skip_next_question_id']) ? intval($question_data['skip_next_question_id']) : null,
            'created_at' => current_time('mysql')
        );

        // No validation for skip_next_question_id - warnings will be shown in admin

        // Insert question
        $table_name = $this->table_prefix . 'questions';
        $result = $wpdb->insert($table_name, $question_record);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create question.', 'flowq'));
        }

        $question_id = $wpdb->insert_id;

        // Trigger action hook
        do_action('flowq_question_created', $question_id, $question_data, $survey_id);

        return $question_id;
    }

    /**
     * Get question by ID
     *
     * @param int $question_id Question ID
     * @param bool $include_answers Whether to include answers
     * @return array|null Question data or null if not found
     */
    public function get_question($question_id, $include_answers = false) {
        global $wpdb;

        $table_name = $this->table_prefix . 'questions';

        $question = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $question_id),
            ARRAY_A
        );

        if (!$question) {
            return null;
        }

        // Include answers if requested
        if ($include_answers) {
            $question['answers'] = $this->get_question_answers($question_id);
        }

        return $question;
    }

    /**
     * Update question
     *
     * @param int $question_id Question ID
     * @param array $data Update data
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function update_question($question_id, $data) {
        global $wpdb;

        // Check if question exists
        if (!$this->get_question($question_id)) {
            return new WP_Error('question_not_found', __('Question not found.', 'flowq'));
        }

        if (isset($data['title'])) {
            if (empty($data['title'])) {
                return new WP_Error('missing_title', __('Question title is required.', 'flowq'));
            }
            $update_data['title'] = sanitize_textarea_field($data['title']);
        }

        if (isset($data['description'])) {
            $update_data['description'] = sanitize_textarea_field($data['description']);
        }

        if (isset($data['extra_message'])) {
            $update_data['extra_message'] = sanitize_textarea_field($data['extra_message']);
        }

        if (!isset($data['is_required'])) {
            $update_data['is_required'] = 0;
        } else {
            $update_data['is_required'] = intval($data['is_required']);
        }

        if (isset($data['skip_next_question_id'])) {
            $update_data['skip_next_question_id'] = $data['skip_next_question_id'] ? intval($data['skip_next_question_id']) : null;
        }

        // Type is always single_choice, no need to validate or update


        // Update question
        $table_name = $this->table_prefix . 'questions';
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $question_id)
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update question.', 'flowq'));
        }

        // Trigger action hook
        do_action('flowq_question_updated', $question_id, $update_data);

        return true;
    }

    /**
     * Delete question
     *
     * @param int $question_id Question ID
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function delete_question($question_id) {
        global $wpdb;

        // Check if question exists
        if (!$this->get_question($question_id)) {
            return new WP_Error('question_not_found', __('Question not found.', 'flowq'));
        }

        // Delete related answers first
        $this->delete_question_answers($question_id);

        // Delete related responses
        $this->delete_question_responses($question_id);

        // Delete question
        $table_name = $this->table_prefix . 'questions';
        $result = $wpdb->delete(
            $table_name,
            array('id' => $question_id),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete question.', 'flowq'));
        }

        // Trigger action hook
        do_action('flowq_question_deleted', $question_id);

        return true;
    }

    /**
     * Get all questions for a survey (optimized for frontend loading)
     *
     * @param int $survey_id Survey ID
     * @param bool $include_answers Whether to include answers
     * @return array Questions with answers
     */
    public function get_survey_questions($survey_id, $include_answers = true) {
        global $wpdb;

        $questions_table = $this->table_prefix . 'questions';

        $questions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$questions_table}
                WHERE survey_id = %d
                ORDER BY id ASC",
                $survey_id
            ),
            ARRAY_A
        );

        if ($include_answers && !empty($questions)) {
            $questions = $this->load_answers_for_questions($questions);
        }

        return $questions;
    }

    /**
     * Create a new answer
     *
     * @param int $question_id Question ID
     * @param array $answer_data Answer data
     * @return int|WP_Error Answer ID on success, WP_Error on failure
     */
    public function create_answer($question_id, $answer_data) {
        global $wpdb;

        // Validate required fields
        if (empty($answer_data['answer_text'])) {
            return new WP_Error('missing_text', __('Answer text is required.', 'flowq'));
        }

        // Check if question exists
        if (!$this->get_question($question_id)) {
            return new WP_Error('question_not_found', __('Question not found.', 'flowq'));
        }

        // Get next answer order
        $next_order = $this->get_next_answer_order($question_id);

        // Prepare answer data
        $answer_record = array(
            'question_id' => $question_id,
            'answer_text' => sanitize_textarea_field($answer_data['answer_text']),
            'answer_value' => sanitize_text_field($answer_data['answer_value'] ?? ''),
            'next_question_id' => isset($answer_data['next_question_id']) ?
                intval($answer_data['next_question_id']) : null,
            'redirect_url' => isset($answer_data['redirect_url']) ?
                esc_url_raw($answer_data['redirect_url']) : null,
            'answer_order' => intval($answer_data['answer_order'] ?? $next_order)
        );

        // Validate redirect URL if provided
        if ($answer_record['redirect_url'] && !filter_var($answer_record['redirect_url'], FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', __('Invalid redirect URL.', 'flowq'));
        }

        // Insert answer
        $table_name = $this->table_prefix . 'answers';
        $result = $wpdb->insert($table_name, $answer_record);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create answer.', 'flowq'));
        }

        $answer_id = $wpdb->insert_id;

        // Trigger action hook
        do_action('flowq_answer_created', $answer_id, $answer_data, $question_id);

        return $answer_id;
    }

    /**
     * Get answers for a question
     *
     * @param int $question_id Question ID
     * @return array Answers list
     */
    public function get_question_answers($question_id) {
        global $wpdb;

        $table_name = $this->table_prefix . 'answers';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE question_id = %d
                ORDER BY answer_order ASC, id ASC",
                $question_id
            ),
            ARRAY_A
        );
    }

    /**
     * Get multiple questions by their IDs
     *
     * @param array $question_ids Array of question IDs
     * @param bool $include_answers Whether to include answers
     * @return array Associative array with question_id as key and question data as value
     */
    public function get_multiple_questions($question_ids, $include_answers = false) {
        global $wpdb;

        if (empty($question_ids)) {
            return array();
        }

        $questions_table = $this->table_prefix . 'questions';
        $placeholders = implode(',', array_fill(0, count($question_ids), '%d'));

        $questions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$questions_table}
                WHERE id IN ($placeholders)",
                ...$question_ids
            ),
            ARRAY_A
        );

        // Index questions by ID
        $indexed_questions = array();
        foreach ($questions as $question) {
            $indexed_questions[$question['id']] = $question;
        }

        // Include answers if requested
        if ($include_answers && !empty($questions)) {
            $answers_table = $this->table_prefix . 'answers';

            $all_answers = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$answers_table}
                    WHERE question_id IN ($placeholders)
                    ORDER BY question_id, answer_order ASC, id ASC",
                    ...$question_ids
                ),
                ARRAY_A
            );

            // Group answers by question_id
            foreach ($all_answers as $answer) {
                $question_id = $answer['question_id'];
                if (isset($indexed_questions[$question_id])) {
                    if (!isset($indexed_questions[$question_id]['answers'])) {
                        $indexed_questions[$question_id]['answers'] = array();
                    }
                    $indexed_questions[$question_id]['answers'][] = $answer;
                }
            }
        }

        return $indexed_questions;
    }

    /**
     * Update answer
     *
     * @param int $answer_id Answer ID
     * @param array $data Update data
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function update_answer($answer_id, $data) {
        global $wpdb;

        // Check if answer exists
        if (!$this->get_answer($answer_id)) {
            return new WP_Error('answer_not_found', __('Answer not found.', 'flowq'));
        }

        // Prepare update data
        $update_data = array();

        if (isset($data['answer_text'])) {
            if (empty($data['answer_text'])) {
                return new WP_Error('missing_text', __('Answer text is required.', 'flowq'));
            }
            $update_data['answer_text'] = sanitize_textarea_field($data['answer_text']);
        }

        if (isset($data['answer_value'])) {
            $update_data['answer_value'] = sanitize_text_field($data['answer_value']);
        }

        if (isset($data['next_question_id'])) {
            $update_data['next_question_id'] = $data['next_question_id'] ? intval($data['next_question_id']) : null;
        }

        if (isset($data['redirect_url'])) {
            if ($data['redirect_url'] && !filter_var($data['redirect_url'], FILTER_VALIDATE_URL)) {
                return new WP_Error('invalid_url', __('Invalid redirect URL.', 'flowq'));
            }
            $update_data['redirect_url'] = $data['redirect_url'] ? esc_url_raw($data['redirect_url']) : null;
        }

        if (isset($data['answer_order'])) {
            $update_data['answer_order'] = intval($data['answer_order']);
        }

        // Update answer
        $table_name = $this->table_prefix . 'answers';
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $answer_id)
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update answer.', 'flowq'));
        }

        // Trigger action hook
        do_action('flowq_answer_updated', $answer_id, $update_data);

        return true;
    }

    /**
     * Delete answer
     *
     * @param int $answer_id Answer ID
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function delete_answer($answer_id) {
        global $wpdb;

        // Check if answer exists
        if (!$this->get_answer($answer_id)) {
            return new WP_Error('answer_not_found', __('Answer not found.', 'flowq'));
        }

        // Delete related responses
        $this->delete_answer_responses($answer_id);

        // Delete answer
        $table_name = $this->table_prefix . 'answers';
        $result = $wpdb->delete(
            $table_name,
            array('id' => $answer_id),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete answer.', 'flowq'));
        }

        // Trigger action hook
        do_action('flowq_answer_deleted', $answer_id);

        return true;
    }


    /**
     * Reorder answers
     *
     * @param array $answer_orders Array of answer_id => order
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function reorder_answers($answer_orders) {
        global $wpdb;

        $table_name = $this->table_prefix . 'answers';

        foreach ($answer_orders as $answer_id => $order) {
            $result = $wpdb->update(
                $table_name,
                array('answer_order' => intval($order)),
                array('id' => intval($answer_id)),
                array('%d'),
                array('%d')
            );

            if ($result === false) {
                return new WP_Error('db_error', __('Failed to reorder answers.', 'flowq'));
            }
        }

        return true;
    }

    /**
     * Get answer by ID
     *
     * @param int $answer_id Answer ID
     * @return array|null Answer data or null
     */
    private function get_answer($answer_id) {
        global $wpdb;

        $table_name = $this->table_prefix . 'answers';

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $answer_id),
            ARRAY_A
        );
    }


    /**
     * Get next answer order for question
     *
     * @param int $question_id Question ID
     * @return int Next order number
     */
    private function get_next_answer_order($question_id) {
        global $wpdb;

        $table_name = $this->table_prefix . 'answers';

        $max_order = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT MAX(answer_order) FROM {$table_name} WHERE question_id = %d",
                $question_id
            )
        );

        return intval($max_order) + 1;
    }

    /**
     * Delete all answers for a question
     *
     * @param int $question_id Question ID
     */
    private function delete_question_answers($question_id) {
        global $wpdb;

        $table_name = $this->table_prefix . 'answers';

        $wpdb->delete(
            $table_name,
            array('question_id' => $question_id),
            array('%d')
        );
    }

    /**
     * Delete responses for a question
     *
     * @param int $question_id Question ID
     */
    private function delete_question_responses($question_id) {
        global $wpdb;

        $table_name = $this->table_prefix . 'responses';

        $wpdb->delete(
            $table_name,
            array('question_id' => $question_id),
            array('%d')
        );
    }

    /**
     * Delete responses for an answer
     *
     * @param int $answer_id Answer ID
     */
    private function delete_answer_responses($answer_id) {
        global $wpdb;

        $table_name = $this->table_prefix . 'responses';

        $wpdb->delete(
            $table_name,
            array('answer_id' => $answer_id),
            array('%d')
        );
    }

    /**
     * Get survey questions with response counts (for admin use)
     *
     * @param int $survey_id Survey ID
     * @param bool $include_answers Whether to include answers
     * @return array Questions with response counts
     */
    public function get_survey_questions_with_response_count($survey_id, $include_answers = true) {
        global $wpdb;

        $questions_table = $this->table_prefix . 'questions';
        $responses_table = $this->table_prefix . 'responses';

        $questions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT q.*, COALESCE(COUNT(r.id), 0) as response_count
                FROM {$questions_table} q
                LEFT JOIN {$responses_table} r ON q.id = r.question_id
                WHERE q.survey_id = %d
                GROUP BY q.id
                ORDER BY q.id ASC",
                $survey_id
            ),
            ARRAY_A
        );

        if ($include_answers && !empty($questions)) {
            $questions = $this->load_answers_for_questions($questions);
        }

        return $questions;
    }

    /**
     * Load answers for multiple questions efficiently (reusable helper)
     *
     * @param array $questions Array of questions
     * @return array Questions with answers loaded
     */
    private function load_answers_for_questions($questions) {
        global $wpdb;

        if (empty($questions)) {
            return $questions;
        }

        // Get all question IDs
        $question_ids = array_column($questions, 'id');

        // Load all answers in a single query
        $answers_table = $this->table_prefix . 'answers';
        $placeholders = implode(',', array_fill(0, count($question_ids), '%d'));

        $all_answers = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$answers_table}
                WHERE question_id IN ({$placeholders})
                ORDER BY question_id ASC, answer_order ASC, id ASC",
                ...$question_ids
            ),
            ARRAY_A
        );

        // Group answers by question_id
        $answers_by_question = array();
        foreach ($all_answers as $answer) {
            $answers_by_question[$answer['question_id']][] = $answer;
        }

        // Assign answers to questions
        foreach ($questions as &$question) {
            $question['answers'] = isset($answers_by_question[$question['id']])
                ? $answers_by_question[$question['id']]
                : array();
        }

        return $questions;
    }
}