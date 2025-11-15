<?php
/**
 * Session Manager for WP Dynamic Survey Plugin
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Survey Session Manager class
 */
class FlowQ_Session_Manager {

    /**
     * WordPress database object
     */
    private $wpdb;

    /**
     * Plugin table prefix
     */
    private $table_prefix;

    /**
     * Participant manager instance
     */
    private $participant_manager;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_prefix = $this->wpdb->prefix . 'flowq_';
        $this->participant_manager = new FlowQ_Participant_Manager();
    }

    /**
     * Record a survey response
     *
     * @param array $participant Participant data
     * @param array $question Question
     * @param array $answer_data Answer data
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function record_response($participant, $question, $answer_data) {
        // Validate session
        $session_id = $participant['session_id'];

        if ($question['survey_id'] != $participant['survey_id']) {
            return new WP_Error('question_mismatch', __('Question does not belong to this survey.', 'flowq'));
        }

        // Validate required fields - treat all questions as required since is_required column doesn't exist
        if (empty($answer_data['answer_text']) && empty($answer_data['answer_id']) && filter_var($question['is_required'], FILTER_VALIDATE_BOOLEAN)) {
            return new WP_Error('required_answer', __('This question requires an answer.', 'flowq'));
        }

        // Validate answer ID if provided
        if (!empty($answer_data['answer_id'])) {
            $valid_answer = $this->validate_answer_for_question($answer_data['answer_id'], $question['id']);
            if (!$valid_answer) {
                return new WP_Error('invalid_answer', __('Invalid answer for this question.', 'flowq'));
            }
        }

        // Handle duplicate responses (update existing)
        $existing_response = $this->get_existing_response($session_id, $question['id']  );

        // Prepare response data
        $response_data = array(
            'participant_id' => $participant['id'],
            'survey_id' => $participant['survey_id'],
            'session_id' => $session_id,
            'question_id' => $question['id'],
            'answer_id' => !empty($answer_data['answer_id']) ? intval($answer_data['answer_id']) : null,
            'answer_text' => sanitize_textarea_field($answer_data['answer_text'] ?? ''),
            'responded_at' => current_time('mysql')
        );

        $table_name = $this->table_prefix . 'responses';

        if ($existing_response) {
            // Update existing response
            $result = $this->wpdb->update(
                $table_name,
                $response_data,
                array('id' => $existing_response['id']),
                array('%d', '%d', '%s', '%d', '%d', '%s', '%s'),
                array('%d')
            );
        } else {
            // Insert new response
            $result = $this->wpdb->insert($table_name, $response_data);
        }

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to record response.', 'flowq'));
        }

        // Update participant progress
        $this->participant_manager->update_current_question($session_id, $question['id']);
        $this->participant_manager->add_to_question_chain($session_id, $question['id']);

        // Trigger action hook
        do_action('flowq_response_recorded', $session_id, $question['id'], $answer_data);

        return true;
    }

    /**
     * Get all responses for a session
     *
     * @param string $session_id Session ID
     * @return array|WP_Error Responses array or WP_Error
     */
    public function get_session_responses($session_id) {
        // Validate session
        $participant = $this->participant_manager->validate_session($session_id);
        if (is_wp_error($participant)) {
            return $participant;
        }

        $table_name = $this->table_prefix . 'responses';

        $responses = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE session_id = %s
                ORDER BY responded_at ASC",
                $session_id
            ),
            ARRAY_A
        );

        return $responses;
    }

    /**
     * Get responses for multiple participants by their session IDs
     *
     * @param array $session_ids Array of session IDs
     * @return array Associative array with session_id as key and responses as value
     */
    public function get_multiple_session_responses($session_ids) {
        if (empty($session_ids)) {
            return array();
        }

        $table_name = $this->table_prefix . 'responses';
        $placeholders = implode(',', array_fill(0, count($session_ids), '%s'));

        $responses = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE session_id IN ($placeholders)
                ORDER BY session_id, responded_at ASC",
                ...$session_ids
            ),
            ARRAY_A
        );

        // Group responses by session_id
        $grouped_responses = array();
        foreach ($responses as $response) {
            $session_id = $response['session_id'];
            if (!isset($grouped_responses[$session_id])) {
                $grouped_responses[$session_id] = array();
            }
            $grouped_responses[$session_id][] = $response;
        }

        return $grouped_responses;
    }

    /**
     * Mark survey as complete for session
     *
     * @param string $session_id Session ID
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function mark_survey_complete($session_id) {
        // Validate session
        $participant = $this->participant_manager->validate_session($session_id);
        if (is_wp_error($participant)) {
            return $participant;
        }

        // Check if already completed
        if (!empty($participant['completed_at'])) {
            return new WP_Error('already_completed', __('Survey already completed.', 'flowq'));
        }

        // Validate all required questions are answered
        $validation_result = $this->validate_survey_completion($session_id, $participant['survey_id']);
        if (is_wp_error($validation_result)) {
            return $validation_result;
        }

        // Mark as completed
        $result = $this->participant_manager->mark_completed($session_id);
        if (is_wp_error($result)) {
            return $result;
        }

        // Trigger action hook
        do_action('flowq_survey_completed', $participant['survey_id'], $session_id, $participant['id']);

        return true;
    }

    /**
     * Handle return from external redirect
     *
     * @param string $session_id Session ID
     * @return array|WP_Error Session state or WP_Error
     */
    public function handle_redirect_return($session_id) {
        // Validate session
        $participant = $this->participant_manager->validate_session($session_id);
        if (is_wp_error($participant)) {
            return $participant;
        }

        // Get current state
        $responses = $this->get_session_responses($session_id);
        if (is_wp_error($responses)) {
            return $responses;
        }

        // Determine where user should continue
        $last_response = end($responses);
        $current_question_id = $participant['current_question_id'];

        return array(
            'session_id' => $session_id,
            'participant' => $participant,
            'last_question_id' => $last_response ? $last_response['question_id'] : null,
            'current_question_id' => $current_question_id,
            'responses_count' => count($responses),
            'is_completed' => !empty($participant['completed_at'])
        );
    }

    /**
     * Get session progress summary
     *
     * @param string $session_id Session ID
     * @return array|WP_Error Progress data or WP_Error
     */
    public function get_session_progress($session_id) {
        // Validate session
        $participant = $this->participant_manager->validate_session($session_id);
        if (is_wp_error($participant)) {
            return $participant;
        }

        // Get total questions for survey
        $question_manager = new FlowQ_Question_Manager();
        $all_questions = $question_manager->get_survey_questions($participant['survey_id'], false);
        $total_questions = count($all_questions);

        // Get answered questions
        $responses = $this->get_session_responses($session_id);
        if (is_wp_error($responses)) {
            return $responses;
        }

        $answered_questions = count($responses);
        $progress_percentage = $total_questions > 0 ?
            round(($answered_questions / $total_questions) * 100, 2) : 0;

        return array(
            'total_questions' => $total_questions,
            'answered_questions' => $answered_questions,
            'progress_percentage' => $progress_percentage,
            'current_question_id' => $participant['current_question_id'],
            'is_completed' => !empty($participant['completed_at']),
            'started_at' => $participant['started_at'],
            'completed_at' => $participant['completed_at']
        );
    }

    /**
     * Get survey responses summary for analysis
     *
     * @param int $survey_id Survey ID
     * @param array $args Query arguments
     * @return array Responses summary
     */
    public function get_survey_responses_summary($survey_id, $args = array()) {
        $defaults = array(
            'completed_only' => true,
            'question_id' => null,
            'limit' => 1000,
            'offset' => 0
        );

        $args = wp_parse_args($args, $defaults);

        $responses_table = $this->table_prefix . 'responses';
        $participants_table = $this->table_prefix . 'participants';

        $where_clauses = array("r.survey_id = %d");
        $where_values = array($survey_id);

        if ($args['completed_only']) {
            $where_clauses[] = "p.completed_at IS NOT NULL";
        }

        if ($args['question_id']) {
            $where_clauses[] = "r.question_id = %d";
            $where_values[] = $args['question_id'];
        }

        $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

        $sql = "SELECT r.*, p.participant_name, p.participant_email, p.completed_at as survey_completed_at
                FROM {$responses_table} r
                LEFT JOIN {$participants_table} p ON r.participant_id = p.id
                {$where_sql}
                ORDER BY r.responded_at DESC
                LIMIT %d OFFSET %d";

        $where_values[] = $args['limit'];
        $where_values[] = $args['offset'];

        return $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $where_values),
            ARRAY_A
        );
    }

    /**
     * Validate answer belongs to question
     *
     * @param int $answer_id Answer ID
     * @param int $question_id Question ID
     * @return bool True if valid
     */
    private function validate_answer_for_question($answer_id, $question_id) {
        $answers_table = $this->table_prefix . 'answers';

        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$answers_table}
                WHERE id = %d AND question_id = %d",
                $answer_id,
                $question_id
            )
        );

        return $count > 0;
    }

    /**
     * Get existing response for session and question
     *
     * @param string $session_id Session ID
     * @param int $question_id Question ID
     * @return array|null Existing response or null
     */
    private function get_existing_response($session_id, $question_id) {
        $table_name = $this->table_prefix . 'responses';

        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE session_id = %s AND question_id = %d",
                $session_id,
                $question_id
            ),
            ARRAY_A
        );
    }

    /**
     * Validate survey completion requirements
     *
     * @param string $session_id Session ID
     * @param int $survey_id Survey ID
     * @return bool|WP_Error True if valid, WP_Error if not
     */
    private function validate_survey_completion($session_id, $survey_id) {
        // Get all required questions
        $questions_table = $this->table_prefix . 'questions';
        // Since is_required column doesn't exist, treat all questions as required
        $required_questions = $this->wpdb->get_col(
            $this->wpdb->prepare(
                "SELECT id FROM {$questions_table}
                WHERE survey_id = %d",
                $survey_id
            )
        );

        if (empty($required_questions)) {
            return true; // No required questions
        }

        // Get answered question IDs
        $responses_table = $this->table_prefix . 'responses';
        $answered_questions = $this->wpdb->get_col(
            $this->wpdb->prepare(
                "SELECT DISTINCT question_id FROM {$responses_table}
                WHERE session_id = %s",
                $session_id
            )
        );

        // Check if all required questions are answered
        $unanswered_required = array_diff($required_questions, $answered_questions);

        if (!empty($unanswered_required)) {
            return new WP_Error(
                'incomplete_survey',
                sprintf(
                    __('Required questions not answered: %s', 'flowq'),
                    implode(', ', $unanswered_required)
                )
            );
        }

        return true;
    }

    /**
     * Get response statistics for a question
     *
     * @param int $question_id Question ID
     * @return array Statistics
     */
    public function get_question_statistics($question_id) {
        $responses_table = $this->table_prefix . 'responses';
        $participants_table = $this->table_prefix . 'participants';

        // Total responses (include all responses, not just from completed surveys)
        $total_responses = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$responses_table} r
                WHERE r.question_id = %d",
                $question_id
            )
        );

        // Answer distribution (include all responses, not just from completed surveys)
        $answer_distribution = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT r.answer_id, a.answer_text, COUNT(*) as count
                FROM {$responses_table} r
                LEFT JOIN {$this->table_prefix}answers a ON r.answer_id = a.id
                WHERE r.question_id = %d AND r.answer_id IS NOT NULL
                GROUP BY r.answer_id
                ORDER BY count DESC",
                $question_id
            ),
            ARRAY_A
        );

        // Text responses (for text/textarea questions) - include all responses
        $text_responses = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT r.answer_text, r.responded_at
                FROM {$responses_table} r
                WHERE r.question_id = %d
                AND r.answer_text IS NOT NULL AND r.answer_text != ''
                ORDER BY r.responded_at DESC
                LIMIT 100",
                $question_id
            ),
            ARRAY_A
        );

        return array(
            'question_id' => $question_id,
            'total_responses' => intval($total_responses),
            'answer_distribution' => $answer_distribution,
            'text_responses' => $text_responses
        );
    }

    /**
     * Export session responses as CSV data
     *
     * @param int $survey_id Survey ID
     * @return array CSV data
     */
    public function export_responses_csv($survey_id) {
        $survey_manager = new FlowQ_Survey_Manager();
        $question_manager = new FlowQ_Question_Manager();

        // Get survey and questions
        $survey = $survey_manager->get_survey($survey_id);
        if (!$survey) {
            return array();
        }

        $questions = $question_manager->get_survey_questions($survey_id, true);

        // Get all completed participants
        $participant_manager = new FlowQ_Participant_Manager();
        $participants = $participant_manager->get_survey_participants($survey_id, array(
            'status' => 'completed',
            'limit' => 10000
        ));

        // Build CSV header
        $headers = array(
            'Session ID',
            'Name',
            'Email',
            'Phone',
            'Address',
            'Zip Code',
            'Started At',
            'Completed At'
        );

        foreach ($questions as $question) {
            $headers[] = 'Q' . $question['id'] . ': ' . wp_strip_all_tags($question['title']);
        }

        // Build CSV rows
        $rows = array($headers);

        foreach ($participants as $participant) {
            $responses = $this->get_session_responses($participant['session_id']);

            // Create response map for quick lookup
            $response_map = array();
            if (!is_wp_error($responses)) {
                foreach ($responses as $response) {
                    $response_map[$response['question_id']] = $response;
                }
            }

            // Build row
            $row = array(
                $participant['session_id'],
                $participant['participant_name'],
                $participant['participant_email'],
                $participant['participant_phone'],
                $participant['participant_address'],
                $participant['participant_zip_code'] ?? '',
                $participant['started_at'],
                $participant['completed_at']
            );

            foreach ($questions as $question) {
                $response_text = '';
                if (isset($response_map[$question['id']])) {
                    $response = $response_map[$question['id']];
                    if ($response['answer_id']) {
                        // Find answer text
                        foreach ($question['answers'] as $answer) {
                            if ($answer['id'] == $response['answer_id']) {
                                $response_text = $answer['answer_text'];
                                break;
                            }
                        }
                    } else {
                        $response_text = $response['answer_text'];
                    }
                }
                $row[] = $response_text;
            }

            $rows[] = $row;
        }

        return $rows;
    }
}