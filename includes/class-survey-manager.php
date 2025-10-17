<?php
/**
 * Survey Manager for WP Dynamic Survey Plugin
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Survey Manager class
 */
class WP_Dynamic_Survey_Manager {

    /**
     * WordPress database object
     */
    private $wpdb;

    /**
     * Plugin table prefix
     */
    private $table_prefix;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_prefix = $this->wpdb->prefix . 'wp_dynamic_survey_';
    }

    /**
     * Create a new survey
     *
     * @param array $data Survey data
     * @return int|WP_Error Survey ID on success, WP_Error on failure
     */
    public function create_survey($data) {
        // Validate required fields
        if (empty($data['title'])) {
            return new WP_Error('missing_title', __('Survey title is required.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
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
            return new WP_Error('invalid_status', __('Invalid survey status.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }


        // Insert survey
        $table_name = $this->table_prefix . 'surveys';
        $result = $this->wpdb->insert($table_name, $survey_data);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create survey.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        $survey_id = $this->wpdb->insert_id;

        // Trigger action hook
        do_action('wp_dynamic_survey_created', $survey_id, $survey_data);

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
        $table_name = $this->table_prefix . 'surveys';

        $survey = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $survey_id),
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
        // Check if survey exists
        if (!$this->get_survey($survey_id)) {
            return new WP_Error('survey_not_found', __('Survey not found.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Prepare update data
        $update_data = array();

        if (isset($data['title'])) {
            if (empty($data['title'])) {
                return new WP_Error('missing_title', __('Survey title is required.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
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
                return new WP_Error('invalid_status', __('Invalid survey status.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
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
        $result = $this->wpdb->update(
            $table_name,
            $update_data,
            array('id' => $survey_id)
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update survey.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Trigger action hook
        do_action('wp_dynamic_survey_updated', $survey_id, $update_data);

        return true;
    }

    /**
     * Delete survey
     *
     * @param int $survey_id Survey ID
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function delete_survey($survey_id) {
        // Check if survey exists
        if (!$this->get_survey($survey_id)) {
            return new WP_Error('survey_not_found', __('Survey not found.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Delete related data first
        $this->delete_survey_questions($survey_id);
        $this->delete_survey_responses($survey_id);
        $this->delete_survey_participants($survey_id);

        // Delete survey
        $table_name = $this->table_prefix . 'surveys';
        $result = $this->wpdb->delete(
            $table_name,
            array('id' => $survey_id),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete survey.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Trigger action hook
        do_action('wp_dynamic_survey_deleted', $survey_id);

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
                'message' => __('Survey has no questions.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)
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
                'message' => sprintf(__('Reference to non-existent question ID: %d', WP_DYNAMIC_SURVEY_TEXT_DOMAIN), $orphaned_id)
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
        $questions_table = $this->table_prefix . 'questions';

        $first_question = $this->wpdb->get_row(
            $this->wpdb->prepare(
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
        $participants_table = $this->table_prefix . 'participants';
        $responses_table = $this->table_prefix . 'responses';

        // Get participant counts
        $total_participants = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$participants_table} WHERE survey_id = %d",
                $survey_id
            )
        );

        $completed_participants = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$participants_table}
                WHERE survey_id = %d AND completed_at IS NOT NULL",
                $survey_id
            )
        );

        // Get response counts
        $total_responses = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$responses_table} WHERE survey_id = %d",
                $survey_id
            )
        );

        // Calculate completion rate
        $completion_rate = $total_participants > 0 ?
            round(($completed_participants / $total_participants) * 100, 2) : 0;

        // Get average completion time
        $avg_completion_time = $this->wpdb->get_var(
            $this->wpdb->prepare(
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

        $where_clauses = array();
        $where_values = array();

        if ($args['status']) {
            $where_clauses[] = 's.status = %s';
            $where_values[] = $args['status'];
        }

        $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

        // Include question count if requested
        if ($args['include_question_count']) {
            $sql = "SELECT s.*, COUNT(q.id) as question_count
                    FROM {$table_name} s
                    LEFT JOIN {$questions_table} q ON s.id = q.survey_id
                    {$where_sql}
                    GROUP BY s.id
                    ORDER BY s.{$args['orderby']} {$args['order']}
                    LIMIT %d OFFSET %d";
        } else {
            $sql = "SELECT s.* FROM {$table_name} s {$where_sql}
                    ORDER BY s.{$args['orderby']} {$args['order']}
                    LIMIT %d OFFSET %d";
        }

        $where_values[] = $args['limit'];
        $where_values[] = $args['offset'];

        $surveys = $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $where_values),
            ARRAY_A
        );

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
        $questions_table = $this->table_prefix . 'questions';

        return $this->wpdb->get_results(
            $this->wpdb->prepare(
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
        $answers_table = $this->table_prefix . 'answers';

        return $this->wpdb->get_results(
            $this->wpdb->prepare(
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
        $questions_table = $this->table_prefix . 'questions';
        $answers_table = $this->table_prefix . 'answers';

        // Get question IDs
        $question_ids = $this->wpdb->get_col(
            $this->wpdb->prepare(
                "SELECT id FROM {$questions_table} WHERE survey_id = %d",
                $survey_id
            )
        );

        if (!empty($question_ids)) {
            $placeholders = implode(',', array_fill(0, count($question_ids), '%d'));

            // Delete answers
            $this->wpdb->query(
                $this->wpdb->prepare(
                    "DELETE FROM {$answers_table} WHERE question_id IN ($placeholders)",
                    $question_ids
                )
            );
        }

        // Delete questions
        $this->wpdb->delete(
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
        $responses_table = $this->table_prefix . 'responses';

        $this->wpdb->delete(
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
        $participants_table = $this->table_prefix . 'participants';

        $this->wpdb->delete(
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
                'message' => sprintf(__('Circular reference detected involving question ID: %d', WP_DYNAMIC_SURVEY_TEXT_DOMAIN), $question_id)
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