<?php
/**
 * REST API Handler for WP Dynamic Survey Plugin
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API Handler class
 */
class WP_Dynamic_Survey_REST_API {

    /**
     * API namespace
     */
    const NAMESPACE = 'wp-dynamic-survey/v1';

    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Survey endpoints
        register_rest_route(self::NAMESPACE, '/surveys', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_surveys'),
                'permission_callback' => array($this, 'check_survey_permissions'),
                'args' => $this->get_survey_collection_params()
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_survey'),
                'permission_callback' => array($this, 'check_manage_permissions'),
                'args' => $this->get_survey_create_params()
            )
        ));

        register_rest_route(self::NAMESPACE, '/surveys/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_survey'),
                'permission_callback' => array($this, 'check_survey_permissions'),
                'args' => array(
                    'id' => array(
                        'description' => __('Survey ID', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                        'type' => 'integer',
                        'required' => true
                    )
                )
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_survey'),
                'permission_callback' => array($this, 'check_manage_permissions'),
                'args' => $this->get_survey_update_params()
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_survey'),
                'permission_callback' => array($this, 'check_manage_permissions')
            )
        ));

        // Survey statistics endpoint
        register_rest_route(self::NAMESPACE, '/surveys/(?P<id>\d+)/statistics', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_survey_statistics'),
            'permission_callback' => array($this, 'check_view_permissions'),
            'args' => array(
                'id' => array(
                    'description' => __('Survey ID', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                    'type' => 'integer',
                    'required' => true
                )
            )
        ));

        // Question endpoints
        register_rest_route(self::NAMESPACE, '/surveys/(?P<survey_id>\d+)/questions', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_questions'),
                'permission_callback' => array($this, 'check_survey_permissions'),
                'args' => array(
                    'survey_id' => array(
                        'description' => __('Survey ID', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                        'type' => 'integer',
                        'required' => true
                    )
                )
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_question'),
                'permission_callback' => array($this, 'check_manage_permissions'),
                'args' => $this->get_question_create_params()
            )
        ));

        register_rest_route(self::NAMESPACE, '/questions/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_question'),
                'permission_callback' => array($this, 'check_survey_permissions')
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_question'),
                'permission_callback' => array($this, 'check_manage_permissions'),
                'args' => $this->get_question_update_params()
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_question'),
                'permission_callback' => array($this, 'check_manage_permissions')
            )
        ));

        // Participant endpoints
        register_rest_route(self::NAMESPACE, '/participants', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_participants'),
                'permission_callback' => array($this, 'check_view_permissions'),
                'args' => $this->get_participant_collection_params()
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_participant'),
                'permission_callback' => '__return_true', // Public endpoint
                'args' => $this->get_participant_create_params()
            )
        ));

        register_rest_route(self::NAMESPACE, '/participants/(?P<session_id>[a-zA-Z0-9_-]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_participant'),
            'permission_callback' => array($this, 'check_participant_access'),
            'args' => array(
                'session_id' => array(
                    'description' => __('Participant session ID', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                    'type' => 'string',
                    'required' => true
                )
            )
        ));

        // Response endpoints
        register_rest_route(self::NAMESPACE, '/responses', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_responses'),
                'permission_callback' => array($this, 'check_view_permissions'),
                'args' => $this->get_response_collection_params()
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_response'),
                'permission_callback' => array($this, 'check_participant_access'),
                'args' => $this->get_response_create_params()
            )
        ));

        register_rest_route(self::NAMESPACE, '/surveys/(?P<survey_id>\d+)/responses', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_survey_responses'),
            'permission_callback' => array($this, 'check_view_permissions'),
            'args' => array(
                'survey_id' => array(
                    'description' => __('Survey ID', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                    'type' => 'integer',
                    'required' => true
                )
            )
        ));

        // Export endpoints
        register_rest_route(self::NAMESPACE, '/surveys/(?P<survey_id>\d+)/export', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'export_survey_data'),
            'permission_callback' => array($this, 'check_export_permissions'),
            'args' => array(
                'survey_id' => array(
                    'description' => __('Survey ID', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                    'type' => 'integer',
                    'required' => true
                ),
                'format' => array(
                    'description' => __('Export format', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                    'type' => 'string',
                    'enum' => array('json', 'csv', 'xml'),
                    'default' => 'json'
                )
            )
        ));

        // Validation endpoints
        register_rest_route(self::NAMESPACE, '/validate/session', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'validate_session'),
            'permission_callback' => '__return_true', // Public endpoint
            'args' => array(
                'session_id' => array(
                    'description' => __('Session ID to validate', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                    'type' => 'string',
                    'required' => true
                )
            )
        ));
    }

    /**
     * Get surveys
     */
    public function get_surveys($request) {
        $survey_manager = new WP_Dynamic_Survey_Manager();

        $args = array(
            'status' => $request->get_param('status'),
            'limit' => $request->get_param('per_page') ?: 10,
            'offset' => ($request->get_param('page') - 1) * ($request->get_param('per_page') ?: 10)
        );

        $surveys = $survey_manager->get_surveys($args);

        if (is_wp_error($surveys)) {
            return $surveys;
        }

        $formatted_surveys = array();
        foreach ($surveys as $survey) {
            $formatted_surveys[] = $this->format_survey_response($survey);
        }

        return rest_ensure_response($formatted_surveys);
    }

    /**
     * Get single survey
     */
    public function get_survey($request) {
        $survey_id = $request->get_param('id');
        $survey_manager = new WP_Dynamic_Survey_Manager();

        $survey = $survey_manager->get_survey($survey_id);

        if (!$survey) {
            return new WP_Error('survey_not_found', __('Survey not found.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN), array('status' => 404));
        }

        // Include questions if requested
        if ($request->get_param('include_questions')) {
            $question_manager = new WP_Dynamic_Survey_Question_Manager();
            $survey['questions'] = $question_manager->get_survey_questions($survey_id, true);
        }

        return rest_ensure_response($this->format_survey_response($survey));
    }

    /**
     * Create survey
     */
    public function create_survey($request) {
        $survey_manager = new WP_Dynamic_Survey_Manager();

        $survey_data = array(
            'title' => $request->get_param('title'),
            'description' => $request->get_param('description'),
            'status' => $request->get_param('status') ?: 'draft',
        );

        $result = $survey_manager->create_survey($survey_data);

        if (is_wp_error($result)) {
            return $result;
        }

        $survey = $survey_manager->get_survey($result['survey_id']);
        return rest_ensure_response($this->format_survey_response($survey));
    }

    /**
     * Update survey
     */
    public function update_survey($request) {
        $survey_id = $request->get_param('id');
        $survey_manager = new WP_Dynamic_Survey_Manager();

        $survey_data = array();

        if ($request->has_param('title')) {
            $survey_data['title'] = $request->get_param('title');
        }
        if ($request->has_param('description')) {
            $survey_data['description'] = $request->get_param('description');
        }
        if ($request->has_param('status')) {
            $survey_data['status'] = $request->get_param('status');
        }

        $result = $survey_manager->update_survey($survey_id, $survey_data);

        if (is_wp_error($result)) {
            return $result;
        }

        $survey = $survey_manager->get_survey($survey_id);
        return rest_ensure_response($this->format_survey_response($survey));
    }

    /**
     * Delete survey
     */
    public function delete_survey($request) {
        $survey_id = $request->get_param('id');
        $survey_manager = new WP_Dynamic_Survey_Manager();

        $result = $survey_manager->delete_survey($survey_id);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response(array(
            'deleted' => true,
            'survey_id' => $survey_id
        ));
    }

    /**
     * Get survey statistics
     */
    public function get_survey_statistics($request) {
        $survey_id = $request->get_param('id');
        $survey_manager = new WP_Dynamic_Survey_Manager();

        $statistics = $survey_manager->get_survey_statistics($survey_id);

        if (is_wp_error($statistics)) {
            return $statistics;
        }

        return rest_ensure_response($statistics);
    }

    /**
     * Create participant
     */
    public function create_participant($request) {
        $participant_manager = new WP_Dynamic_Survey_Participant_Manager();

        $survey_id = $request->get_param('survey_id');
        $participant_data = array(
            'name' => $request->get_param('name'),
            'email' => $request->get_param('email'),
            'phone' => $request->get_param('phone'),
            'address' => $request->get_param('address'),
            'zip_code' => $request->get_param('zip_code')
        );

        $result = $participant_manager->create_participant($survey_id, $participant_data);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response($result);
    }

    /**
     * Validate session
     */
    public function validate_session($request) {
        $session_id = $request->get_param('session_id');
        $participant_manager = new WP_Dynamic_Survey_Participant_Manager();

        $participant = $participant_manager->validate_session($session_id);

        if (is_wp_error($participant)) {
            return $participant;
        }

        return rest_ensure_response(array(
            'valid' => true,
            'session_id' => $session_id,
            'survey_id' => $participant['survey_id'],
            'participant_id' => $participant['id']
        ));
    }

    /**
     * Get questions for a survey
     */
    public function get_questions($request) {
        $survey_id = $request->get_param('survey_id');
        $question_manager = new WP_Dynamic_Survey_Question_Manager();

        $questions = $question_manager->get_survey_questions($survey_id, true);

        if (is_wp_error($questions)) {
            return $questions;
        }

        return rest_ensure_response($questions);
    }

    /**
     * Get single question
     */
    public function get_question($request) {
        $question_id = $request->get_param('id');
        $question_manager = new WP_Dynamic_Survey_Question_Manager();

        $question = $question_manager->get_question($question_id, true);

        if (!$question) {
            return new WP_Error('question_not_found', __('Question not found.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN), array('status' => 404));
        }

        return rest_ensure_response($question);
    }

    /**
     * Create question
     */
    public function create_question($request) {
        $question_manager = new WP_Dynamic_Survey_Question_Manager();

        $question_data = array(
            'survey_id' => $request->get_param('survey_id'),
            'title' => $request->get_param('title'),
            'description' => $request->get_param('description'),
            'extra_message' => $request->get_param('extra_message') ?: ''
        );

        $result = $question_manager->create_question($question_data);

        if (is_wp_error($result)) {
            return $result;
        }

        $question = $question_manager->get_question($result['question_id'], true);
        return rest_ensure_response($question);
    }

    /**
     * Update question
     */
    public function update_question($request) {
        $question_id = $request->get_param('id');
        $question_manager = new WP_Dynamic_Survey_Question_Manager();

        $question_data = array();

        if ($request->has_param('title')) {
            $question_data['title'] = $request->get_param('title');
        }
        if ($request->has_param('description')) {
            $question_data['description'] = $request->get_param('description');
        }
        if ($request->has_param('extra_message')) {
            $question_data['extra_message'] = $request->get_param('extra_message');
        }

        $result = $question_manager->update_question($question_id, $question_data);

        if (is_wp_error($result)) {
            return $result;
        }

        $question = $question_manager->get_question($question_id, true);
        return rest_ensure_response($question);
    }

    /**
     * Delete question
     */
    public function delete_question($request) {
        $question_id = $request->get_param('id');
        $question_manager = new WP_Dynamic_Survey_Question_Manager();

        $result = $question_manager->delete_question($question_id);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response(array(
            'deleted' => true,
            'question_id' => $question_id
        ));
    }

    /**
     * Get participants
     */
    public function get_participants($request) {
        $participant_manager = new WP_Dynamic_Survey_Participant_Manager();

        $args = array(
            'survey_id' => $request->get_param('survey_id'),
            'status' => $request->get_param('status'),
            'limit' => $request->get_param('per_page') ?: 10,
            'offset' => ($request->get_param('page') - 1) * ($request->get_param('per_page') ?: 10)
        );

        $participants = $participant_manager->get_survey_participants($args['survey_id'], $args);

        if (is_wp_error($participants)) {
            return $participants;
        }

        return rest_ensure_response($participants);
    }

    /**
     * Get single participant
     */
    public function get_participant($request) {
        $session_id = $request->get_param('session_id');
        $participant_manager = new WP_Dynamic_Survey_Participant_Manager();

        $participant = $participant_manager->get_participant($session_id);

        if (!$participant) {
            return new WP_Error('participant_not_found', __('Participant not found.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN), array('status' => 404));
        }

        return rest_ensure_response($participant);
    }

    /**
     * Get responses
     */
    public function get_responses($request) {
        $session_manager = new WP_Dynamic_Survey_Session_Manager();

        $filters = array();
        if ($request->get_param('survey_id')) {
            $filters['survey_id'] = $request->get_param('survey_id');
        }
        if ($request->get_param('participant_id')) {
            $filters['participant_id'] = $request->get_param('participant_id');
        }
        if ($request->get_param('question_id')) {
            $filters['question_id'] = $request->get_param('question_id');
        }

        // This would need to be implemented in the session manager
        // For now, return survey responses summary
        if (isset($filters['survey_id'])) {
            $responses = $session_manager->get_survey_responses_summary($filters['survey_id']);
        } else {
            return new WP_Error('missing_survey_id', __('Survey ID is required for responses.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN), array('status' => 400));
        }

        if (is_wp_error($responses)) {
            return $responses;
        }

        return rest_ensure_response($responses);
    }

    /**
     * Create response
     */
    public function create_response($request) {
        $session_manager = new WP_Dynamic_Survey_Session_Manager();

        $session_id = $request->get_param('session_id');
        $question_id = $request->get_param('question_id');
        $answer_data = array(
            'answer_id' => $request->get_param('answer_id'),
            'answer_text' => $request->get_param('answer_text')
        );

        $result = $session_manager->record_response($session_id, $question_id, $answer_data);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response($result);
    }

    /**
     * Get survey responses
     */
    public function get_survey_responses($request) {
        $survey_id = $request->get_param('survey_id');
        $session_manager = new WP_Dynamic_Survey_Session_Manager();

        $responses = $session_manager->get_survey_responses_summary($survey_id);

        if (is_wp_error($responses)) {
            return $responses;
        }

        return rest_ensure_response($responses);
    }

    /**
     * Export survey data
     */
    public function export_survey_data($request) {
        $survey_id = $request->get_param('survey_id');
        $format = $request->get_param('format');

        $session_manager = new WP_Dynamic_Survey_Session_Manager();

        switch ($format) {
            case 'csv':
                $data = $session_manager->export_responses_csv($survey_id);
                break;
            case 'json':
            default:
                $responses = $session_manager->get_survey_responses_summary($survey_id);
                $data = json_encode($responses, JSON_PRETTY_PRINT);
                break;
        }

        if (is_wp_error($data)) {
            return $data;
        }

        return rest_ensure_response(array(
            'survey_id' => $survey_id,
            'format' => $format,
            'data' => $data,
            'generated_at' => current_time('mysql')
        ));
    }

    /**
     * Format survey response
     */
    private function format_survey_response($survey) {
        return array(
            'id' => intval($survey['id']),
            'title' => $survey['title'],
            'description' => $survey['description'],
            'status' => $survey['status'],
            'created_at' => $survey['created_at'],
            'updated_at' => $survey['updated_at'],
            'questions' => $survey['questions'] ?? null
        );
    }

    /**
     * Permission callbacks
     */
    public function check_survey_permissions($request) {
        // Public surveys can be accessed by anyone
        $survey_id = $request->get_param('id');
        if ($survey_id) {
            $survey_manager = new WP_Dynamic_Survey_Manager();
            $survey = $survey_manager->get_survey($survey_id);
            if ($survey && $survey['status'] === 'published') {
                return true;
            }
        }

        return current_user_can('view_wp_dynamic_survey_responses');
    }

    public function check_manage_permissions($request) {
        return current_user_can('manage_wp_dynamic_surveys');
    }

    public function check_view_permissions($request) {
        return current_user_can('view_wp_dynamic_survey_responses');
    }

    public function check_export_permissions($request) {
        return current_user_can('export_wp_dynamic_survey_data');
    }

    public function check_participant_access($request) {
        // For participant-specific endpoints, validate session
        $session_id = $request->get_param('session_id');
        if ($session_id) {
            $participant_manager = new WP_Dynamic_Survey_Participant_Manager();
            $participant = $participant_manager->validate_session($session_id);
            return !is_wp_error($participant);
        }

        return current_user_can('view_wp_dynamic_survey_responses');
    }

    /**
     * Parameter definitions
     */
    private function get_survey_collection_params() {
        return array(
            'status' => array(
                'description' => __('Filter by status', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string',
                'enum' => array('draft', 'published', 'archived')
            ),
            'page' => array(
                'description' => __('Page number', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 1,
                'minimum' => 1
            ),
            'per_page' => array(
                'description' => __('Items per page', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 10,
                'minimum' => 1,
                'maximum' => 100
            )
        );
    }

    private function get_survey_create_params() {
        return array(
            'title' => array(
                'description' => __('Survey title', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string',
                'required' => true,
                'minLength' => 1,
                'maxLength' => 255
            ),
            'description' => array(
                'description' => __('Survey description', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string'
            ),
            'status' => array(
                'description' => __('Survey status', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string',
                'enum' => array('draft', 'published', 'archived'),
                'default' => 'draft'
            ),
        );
    }

    private function get_survey_update_params() {
        $params = $this->get_survey_create_params();

        // Make all fields optional for updates
        foreach ($params as $key => $param) {
            $params[$key]['required'] = false;
        }

        return $params;
    }

    private function get_participant_create_params() {
        return array(
            'survey_id' => array(
                'description' => __('Survey ID', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'integer',
                'required' => true
            ),
            'name' => array(
                'description' => __('Participant name', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string',
                'required' => true
            ),
            'email' => array(
                'description' => __('Participant email', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string',
                'format' => 'email',
                'required' => true
            ),
            'phone' => array(
                'description' => __('Participant phone', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string',
                'required' => true
            ),
            'address' => array(
                'description' => __('Participant address', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string'
            ),
            'zip_code' => array(
                'description' => __('Participant zip code', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string'
            )
        );
    }

    private function get_question_create_params() {
        return array(
            'survey_id' => array(
                'description' => __('Survey ID', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'integer',
                'required' => true
            ),
            'title' => array(
                'description' => __('Question title', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string',
                'required' => true
            ),
            'description' => array(
                'description' => __('Question description', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string'
            ),
            'extra_message' => array(
                'description' => __('Extra message for question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string',
                'default' => ''
            )
        );
    }

    private function get_question_update_params() {
        $params = $this->get_question_create_params();

        // Make all fields optional for updates except survey_id
        foreach ($params as $key => $param) {
            if ($key !== 'survey_id') {
                $params[$key]['required'] = false;
            }
        }

        return $params;
    }

    private function get_participant_collection_params() {
        return array(
            'survey_id' => array(
                'description' => __('Filter by survey ID', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'integer'
            ),
            'status' => array(
                'description' => __('Filter by completion status', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string',
                'enum' => array('active', 'completed', 'expired')
            ),
            'page' => array(
                'description' => __('Page number', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 1,
                'minimum' => 1
            ),
            'per_page' => array(
                'description' => __('Items per page', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 10,
                'minimum' => 1,
                'maximum' => 100
            )
        );
    }

    private function get_response_collection_params() {
        return array(
            'survey_id' => array(
                'description' => __('Filter by survey ID', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'integer'
            ),
            'participant_id' => array(
                'description' => __('Filter by participant ID', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'integer'
            ),
            'question_id' => array(
                'description' => __('Filter by question ID', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'integer'
            ),
            'page' => array(
                'description' => __('Page number', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 1,
                'minimum' => 1
            ),
            'per_page' => array(
                'description' => __('Items per page', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 10,
                'minimum' => 1,
                'maximum' => 100
            )
        );
    }

    private function get_response_create_params() {
        return array(
            'session_id' => array(
                'description' => __('Participant session ID', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string',
                'required' => true
            ),
            'question_id' => array(
                'description' => __('Question ID', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'integer',
                'required' => true
            ),
            'answer_id' => array(
                'description' => __('Answer ID (for multiple choice)', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'integer'
            ),
            'answer_text' => array(
                'description' => __('Text answer', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'type' => 'string'
            )
        );
    }
}