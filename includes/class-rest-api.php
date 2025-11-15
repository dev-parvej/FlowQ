<?php
/**
 * REST API Handler for WP Dynamic Survey Plugin
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API Handler class
 */
class FlowQ_REST_API {

    /**
     * API namespace
     */
    const NAMESPACE = 'flowq/v1';

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
            )
        ));

        register_rest_route(self::NAMESPACE, '/surveys/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_survey'),
                'permission_callback' => array($this, 'check_survey_permissions'),
                'args' => array(
                    'id' => array(
                        'description' => __('Survey ID', 'flowq'),
                        'type' => 'integer',
                        'required' => true
                    )
                )
            )
        ));

        // Survey statistics endpoint
        register_rest_route(self::NAMESPACE, '/surveys/(?P<id>\d+)/statistics', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_survey_statistics'),
            'permission_callback' => array($this, 'check_view_permissions'),
            'args' => array(
                'id' => array(
                    'description' => __('Survey ID', 'flowq'),
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
                        'description' => __('Survey ID', 'flowq'),
                        'type' => 'integer',
                        'required' => true
                    )
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
                    'description' => __('Session ID to validate', 'flowq'),
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
        $survey_manager = new FlowQ_Survey_Manager();

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
        $survey_manager = new FlowQ_Survey_Manager();

        $survey = $survey_manager->get_survey($survey_id);

        if (!$survey) {
            return new WP_Error('survey_not_found', __('Survey not found.', 'flowq'), array('status' => 404));
        }

        // Include questions if requested
        if ($request->get_param('include_questions')) {
            $question_manager = new FlowQ_Question_Manager();
            $survey['questions'] = $question_manager->get_survey_questions($survey_id, true);
        }

        return rest_ensure_response($this->format_survey_response($survey));
    }




    /**
     * Get survey statistics
     */
    public function get_survey_statistics($request) {
        $survey_id = $request->get_param('id');
        $survey_manager = new FlowQ_Survey_Manager();

        $statistics = $survey_manager->get_survey_statistics($survey_id);

        if (is_wp_error($statistics)) {
            return $statistics;
        }

        return rest_ensure_response($statistics);
    }


    /**
     * Validate session
     */
    public function validate_session($request) {
        $session_id = $request->get_param('session_id');
        $participant_manager = new FlowQ_Participant_Manager();

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
        $question_manager = new FlowQ_Question_Manager();

        $questions = $question_manager->get_survey_questions($survey_id, true);

        if (is_wp_error($questions)) {
            return $questions;
        }

        return rest_ensure_response($questions);
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
            $survey_manager = new FlowQ_Survey_Manager();
            $survey = $survey_manager->get_survey($survey_id);
            if ($survey && $survey['status'] === 'published') {
                return true;
            }
        }

        return current_user_can('view_flowq_responses');
    }


    public function check_view_permissions($request) {
        return current_user_can('view_flowq_responses');
    }



    /**
     * Parameter definitions
     */
    private function get_survey_collection_params() {
        return array(
            'status' => array(
                'description' => __('Filter by status', 'flowq'),
                'type' => 'string',
                'enum' => array('draft', 'published', 'archived')
            ),
            'page' => array(
                'description' => __('Page number', 'flowq'),
                'type' => 'integer',
                'default' => 1,
                'minimum' => 1
            ),
            'per_page' => array(
                'description' => __('Items per page', 'flowq'),
                'type' => 'integer',
                'default' => 10,
                'minimum' => 1,
                'maximum' => 100
            )
        );
    }








}