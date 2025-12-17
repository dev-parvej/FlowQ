<?php
/**
 * Survey Manager for WP Dynamic Survey Plugin
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Survey Manager class
 */
class FlowQ_Survey_Manager {

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
     * Create a new survey
     *
     * @param array $data Survey data
     * @return int|WP_Error Survey ID on success, WP_Error on failure
     */
    public function create_survey($data) {
        global $wpdb;

        // Validate required fields
        if (empty($data['title'])) {
            return new WP_Error('missing_title', __('Survey title is required.', 'flowq'));
        }

        // Sanitize and prepare data
        $survey_data = array(
            'title' => sanitize_text_field($data['title']),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'thank_you_page_slug' => sanitize_text_field($data['thank_you_page_slug'] ?? ''),
            'created_by' => get_current_user_id(),
            'status' => sanitize_text_field($data['status'] ?? 'draft'),
            'settings' => wp_json_encode($data['settings'] ?? array()),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
            'show_header' => intval($data['show_header'] ?? 1),
            'form_header' => sanitize_text_field($data['form_header'] ?? ''),
            'form_subtitle' => wp_kses_post($data['form_subtitle'] ?? '')
        );

        // Validate status
        if (!in_array($survey_data['status'], ['draft', 'published', 'archived'])) {
            return new WP_Error('invalid_status', __('Invalid survey status.', 'flowq'));
        }


        // Insert survey
        $table_name = $this->table_prefix . 'surveys';
        $result = $wpdb->insert($table_name, $survey_data);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create survey.', 'flowq'));
        }

        $survey_id = $wpdb->insert_id;

        // Trigger action hook
        do_action('flowq_survey_created', $survey_id, $survey_data);

        return $survey_id;
    }

    /**
     * Get survey by ID
     *
     * @param int $survey_id Survey ID
     * @param bool $include_questions Whether to include questions
     * @return array|null Survey data or null if not found
     */
    public function get_survey($survey_id, $include_questions = false) {
        global $wpdb;

        $table_name = $this->table_prefix . 'surveys';

        $survey = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $survey_id),
            ARRAY_A
        );

        if (!$survey) {
            return null;
        }

        // Parse JSON settings
        $survey['settings'] = json_decode($survey['settings'], true) ?? array();

        // Include questions if requested
        if ($include_questions) {
            $survey['questions'] = $this->get_survey_questions($survey_id);
        }

        return $survey;
    }

    /**
     * Update survey
     *
     * @param int $survey_id Survey ID
     * @param array $data Update data
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function update_survey($survey_id, $data) {
        global $wpdb;

        // Check if survey exists
        if (!$this->get_survey($survey_id)) {
            return new WP_Error('survey_not_found', __('Survey not found.', 'flowq'));
        }

        // Prepare update data
        $update_data = array();

        if (isset($data['title'])) {
            if (empty($data['title'])) {
                return new WP_Error('missing_title', __('Survey title is required.', 'flowq'));
            }
            $update_data['title'] = sanitize_text_field($data['title']);
        }

        if (isset($data['description'])) {
            $update_data['description'] = sanitize_textarea_field($data['description']);
        }

        if (isset($data['thank_you_page_slug'])) {
            $update_data['thank_you_page_slug'] = sanitize_text_field($data['thank_you_page_slug']);
        }

        if (isset($data['status'])) {
            if (!in_array($data['status'], ['draft', 'published', 'archived'])) {
                return new WP_Error('invalid_status', __('Invalid survey status.', 'flowq'));
            }
            $update_data['status'] = sanitize_text_field($data['status']);
        }


        if (isset($data['show_header'])) {
            $update_data['show_header'] = intval($data['show_header']);
        }

        if (isset($data['form_header'])) {
            $update_data['form_header'] = sanitize_text_field($data['form_header']);
        }

        if (isset($data['form_subtitle'])) {
            $update_data['form_subtitle'] = wp_kses_post($data['form_subtitle']);
        }



        if (isset($data['settings'])) {
            $update_data['settings'] = wp_json_encode($data['settings']);
        }

        // Always update the modified timestamp
        $update_data['updated_at'] = current_time('mysql');

        // Update survey
        $table_name = $this->table_prefix . 'surveys';
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $survey_id)
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update survey.', 'flowq'));
        }

        // Trigger action hook
        do_action('flowq_survey_updated', $survey_id, $update_data);

        return true;
    }

    /**
     * Delete survey
     *
     * @param int $survey_id Survey ID
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function delete_survey($survey_id) {
        global $wpdb;

        // Check if survey exists
        if (!$this->get_survey($survey_id)) {
            return new WP_Error('survey_not_found', __('Survey not found.', 'flowq'));
        }

        // Delete related data first
        $this->delete_survey_questions($survey_id);
        $this->delete_survey_responses($survey_id);
        $this->delete_survey_participants($survey_id);

        // Delete survey
        $table_name = $this->table_prefix . 'surveys';
        $result = $wpdb->delete(
            $table_name,
            array('id' => $survey_id),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete survey.', 'flowq'));
        }

        // Trigger action hook
        do_action('flowq_survey_deleted', $survey_id);

        return true;
    }

    /**
     * Validate survey flow
     *
     * @param int $survey_id Survey ID
     * @return array Validation results
     */
    public function validate_survey_flow($survey_id) {
        $issues = array();
        $questions = $this->get_survey_questions($survey_id);

        if (empty($questions)) {
            $issues[] = array(
                'type' => 'error',
                'message' => __('Survey has no questions.', 'flowq')
            );
            return $issues;
        }

        // Check for orphaned questions
        $question_ids = array_column($questions, 'id');
        $referenced_ids = array();

        foreach ($questions as $question) {
            // Questions don't have next_question_id, only answers do

            // Check question answers for references
            $answers = $this->get_question_answers($question['id']);
            foreach ($answers as $answer) {
                if ($answer['next_question_id']) {
                    $referenced_ids[] = $answer['next_question_id'];
                }
            }
        }

        $orphaned_refs = array_diff($referenced_ids, $question_ids);
        foreach ($orphaned_refs as $orphaned_id) {
            $issues[] = array(
                'type' => 'error',
                /* translators: %d: question ID */
                'message' => sprintf(__('Reference to non-existent question ID: %d', 'flowq'), $orphaned_id)
            );
        }

        // Check for circular references
        $this->check_circular_references($questions, $issues);

        return $issues;
    }

    /**
     * Get first question of survey
     *
     * @param int $survey_id Survey ID
     * @return array|null First question data or null
     */
    public function get_first_question($survey_id) {
        global $wpdb;

        $questions_table = $this->table_prefix . 'questions';

        $first_question = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$questions_table}
                WHERE survey_id = %d
                ORDER BY id ASC
                LIMIT 1",
                $survey_id
            ),
            ARRAY_A
        );

        if (!$first_question) {
            return null;
        }

        // Include answers
        $first_question['answers'] = $this->get_question_answers($first_question['id']);

        return $first_question;
    }

    /**
     * Get survey statistics
     *
     * @param int $survey_id Survey ID
     * @return array Statistics data
     */
    public function get_survey_statistics($survey_id) {
        global $wpdb;

        $participants_table = $this->table_prefix . 'participants';
        $responses_table = $this->table_prefix . 'responses';

        // Get participant counts
        $total_participants = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$participants_table} WHERE survey_id = %d",
                $survey_id
            )
        );

        $completed_participants = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$participants_table}
                WHERE survey_id = %d AND completed_at IS NOT NULL",
                $survey_id
            )
        );

        // Get response counts
        $total_responses = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$responses_table} WHERE survey_id = %d",
                $survey_id
            )
        );

        // Calculate completion rate
        $completion_rate = $total_participants > 0 ?
            round(($completed_participants / $total_participants) * 100, 2) : 0;

        // Get average completion time
        $avg_completion_time = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at))
                FROM {$participants_table}
                WHERE survey_id = %d AND completed_at IS NOT NULL",
                $survey_id
            )
        );

        return array(
            'total_participants' => intval($total_participants),
            'completed_participants' => intval($completed_participants),
            'completion_rate' => $completion_rate,
            'total_responses' => intval($total_responses),
            'average_completion_time' => $avg_completion_time !== null ? round($avg_completion_time, 2) : 0,
            'survey_id' => $survey_id
        );
    }

    /**
     * Get all surveys
     *
     * @param array $args Query arguments
     * @return array Surveys list
     */
    public function get_surveys($args = array()) {
        global $wpdb;

        $defaults = array(
            'status' => null,
            'limit' => 50,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'include_question_count' => false
        );

        $args = wp_parse_args($args, $defaults);
        $table_name = $this->table_prefix . 'surveys';
        $questions_table = $this->table_prefix . 'questions';

        // SECURITY: Whitelist validation for ORDER BY column (cannot use prepare() placeholders for column names)
        $allowed_orderby = array('created_at', 'updated_at', 'title', 'status', 'id');
        if (!in_array($args['orderby'], $allowed_orderby, true)) {
            $args['orderby'] = 'created_at'; // Safe default
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

        if ($args['status']) {
            $where_clauses[] = 's.status = %s';
            $prepare_values[] = sanitize_text_field($args['status']);
        }

        $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

        // Add LIMIT and OFFSET to prepare values
        $prepare_values[] = absint($args['limit']);
        $prepare_values[] = absint($args['offset']);

        // Include question count if requested
        if ($args['include_question_count']) {
            // Execute query - ORDER BY uses whitelisted+escaped values, other params use prepare()
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- ORDER BY column/direction are whitelisted and escaped
            $surveys = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT s.*, COUNT(q.id) as question_count
                    FROM {$table_name} s
                    LEFT JOIN {$questions_table} q ON s.id = q.survey_id
                    {$where_sql}
                    GROUP BY s.id
                    ORDER BY s.{$orderby} {$order}
                    LIMIT %d OFFSET %d",
                    $prepare_values
                ),
                ARRAY_A
            );
        } else {
            // Execute query - ORDER BY uses whitelisted+escaped values, other params use prepare()
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- ORDER BY column/direction are whitelisted and escaped
            $surveys = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT s.* FROM {$table_name} s {$where_sql}
                    ORDER BY s.{$orderby} {$order}
                    LIMIT %d OFFSET %d",
                    $prepare_values
                ),
                ARRAY_A
            );
        }

        // Parse settings for each survey
        foreach ($surveys as &$survey) {
            $survey['settings'] = json_decode($survey['settings'], true) ?? array();
        }

        return $surveys;
    }




    /**
     * Get survey questions
     *
     * @param int $survey_id Survey ID
     * @return array Questions list
     */
    private function get_survey_questions($survey_id) {
        global $wpdb;

        $questions_table = $this->table_prefix . 'questions';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$questions_table}
                WHERE survey_id = %d
                ORDER BY id ASC",
                $survey_id
            ),
            ARRAY_A
        );
    }

    /**
     * Get question answers
     *
     * @param int $question_id Question ID
     * @return array Answers list
     */
    private function get_question_answers($question_id) {
        global $wpdb;

        $answers_table = $this->table_prefix . 'answers';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$answers_table}
                WHERE question_id = %d
                ORDER BY answer_order ASC, id ASC",
                $question_id
            ),
            ARRAY_A
        );
    }

    /**
     * Delete survey questions
     *
     * @param int $survey_id Survey ID
     */
    private function delete_survey_questions($survey_id) {
        global $wpdb;

        $questions_table = $this->table_prefix . 'questions';
        $answers_table = $this->table_prefix . 'answers';

        // Get question IDs
        $question_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM {$questions_table} WHERE survey_id = %d",
                $survey_id
            )
        );

        if (!empty($question_ids)) {
            $placeholders = implode(',', array_fill(0, count($question_ids), '%d'));

            // Delete answers
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$answers_table} WHERE question_id IN ($placeholders)",
                    $question_ids
                )
            );
        }

        // Delete questions
        $wpdb->delete(
            $questions_table,
            array('survey_id' => $survey_id),
            array('%d')
        );
    }

    /**
     * Delete survey responses
     *
     * @param int $survey_id Survey ID
     */
    private function delete_survey_responses($survey_id) {
        global $wpdb;

        $responses_table = $this->table_prefix . 'responses';

        $wpdb->delete(
            $responses_table,
            array('survey_id' => $survey_id),
            array('%d')
        );
    }

    /**
     * Delete survey participants
     *
     * @param int $survey_id Survey ID
     */
    private function delete_survey_participants($survey_id) {
        global $wpdb;

        $participants_table = $this->table_prefix . 'participants';

        $wpdb->delete(
            $participants_table,
            array('survey_id' => $survey_id),
            array('%d')
        );
    }

    /**
     * Check for circular references in question flow
     *
     * @param array $questions Questions list
     * @param array &$issues Issues array to populate
     */
    private function check_circular_references($questions, &$issues) {
        foreach ($questions as $question) {
            $visited = array();
            $this->detect_circular_path($question['id'], $questions, $visited, $issues);
        }
    }

    /**
     * Detect circular path in question flow
     *
     * @param int $question_id Current question ID
     * @param array $questions All questions
     * @param array &$visited Visited questions
     * @param array &$issues Issues array
     */
    private function detect_circular_path($question_id, $questions, &$visited, &$issues) {
        if (in_array($question_id, $visited)) {
            $issues[] = array(
                'type' => 'error',
                /* translators: %d: question ID */
                'message' => sprintf(__('Circular reference detected involving question ID: %d', 'flowq'), $question_id)
            );
            return;
        }

        $visited[] = $question_id;

        // Find question and check its next question references
        foreach ($questions as $question) {
            if ($question['id'] == $question_id) {
                // Questions don't have next_question_id, only answers do

                // Check answer-level references
                $answers = $this->get_question_answers($question_id);
                foreach ($answers as $answer) {
                    if ($answer['next_question_id']) {
                        $this->detect_circular_path($answer['next_question_id'], $questions, $visited, $issues);
                    }
                }
                break;
            }
        }
    }
}