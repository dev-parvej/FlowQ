<?php
/**
 * Admin Interface for WP Dynamic Survey Plugin
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Survey Admin class
 */
class FlowQ_Admin {

    /**
     * Plugin version
     */
    private $version;

    /**
     * Survey manager instance
     */
    private $survey_manager;

    /**
     * Menu slugs
     */
    private $menu_slugs = array(
        'main' => 'flowq',
        'all_surveys' => 'flowq',
        'add_survey' => 'flowq-add',
        'questions' => 'flowq-questions',
        'participants' => 'flowq-participants',
        'analytics' => 'flowq-analytics',
        'contact' => 'flowq-contact',
        'user_guide' => 'flowq-user-guide'
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->version = FLOWQ_VERSION;
        $this->survey_manager = new FlowQ_Survey_Manager();
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // Admin notices
        add_action('admin_notices', array($this, 'show_admin_notices'));

        // AJAX handlers
        add_action('wp_ajax_flowq_admin_action', array($this, 'handle_admin_ajax'));

        // Form submission handlers
        add_action('admin_post_flowq_save_survey', array($this, 'handle_save_survey_action'));
        add_action('admin_post_flowq_save_question', array($this, 'handle_save_question_action'));
        add_action('admin_post_flowq_delete_question', array($this, 'handle_delete_question_action'));
        add_action('admin_post_flowq_survey_action', array($this, 'handle_survey_action'));

        // Custom capabilities
        add_action('init', array($this, 'add_custom_capabilities'));
    }

    /**
     * Add admin menu structure
     */
    public function add_admin_menu() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            return;
        }

        // Main menu item
        add_menu_page(
            __('FlowQ', 'flowq'),           // Page title
            __('FlowQ', 'flowq'),           // Menu title
            'manage_options',                                        // Capability
            $this->menu_slugs['main'],                              // Menu slug
            array($this, 'display_all_surveys_page'),               // Callback
            'dashicons-forms',                                    // Icon
            30                                                       // Position
        );

        // All Surveys submenu (same as main)
        add_submenu_page(
            $this->menu_slugs['main'],
            __('All Surveys', 'flowq'),
            __('All Surveys', 'flowq'),
            'manage_options',
            $this->menu_slugs['all_surveys'],
            array($this, 'display_all_surveys_page')
        );

        // Add New Survey submenu
        add_submenu_page(
            $this->menu_slugs['main'],
            __('Add New Survey', 'flowq'),
            __('Add New Survey', 'flowq'),
            'manage_options',
            $this->menu_slugs['add_survey'],
            array($this, 'display_add_survey_page')
        );

        // Questions Management submenu
        add_submenu_page(
            $this->menu_slugs['main'],
            __('Manage Questions', 'flowq'),
            __('Questions', 'flowq'),
            'manage_options',
            $this->menu_slugs['questions'],
            array($this, 'display_questions_page')
        );

        // Participants submenu
        add_submenu_page(
            $this->menu_slugs['main'],
            __('Survey Participants', 'flowq'),
            __('Participants', 'flowq'),
            'manage_options',
            $this->menu_slugs['participants'],
            array($this, 'display_participants_page')
        );

        // Analytics submenu
        add_submenu_page(
            $this->menu_slugs['main'],
            __('Survey Analytics', 'flowq'),
            __('Analytics', 'flowq'),
            'manage_options',
            $this->menu_slugs['analytics'],
            array($this, 'display_analytics_page')
        );

        // Contact / Hire Developer submenu
        add_submenu_page(
            $this->menu_slugs['main'],
            __('Contact Developer', 'flowq'),
            __('Hire Developer', 'flowq'),
            'manage_options',
            $this->menu_slugs['contact'],
            array($this, 'display_contact_page')
        );

        // User Guide submenu
        add_submenu_page(
            $this->menu_slugs['main'],
            __('User Guide', 'flowq'),
            __('User Guide', 'flowq'),
            'manage_options',
            $this->menu_slugs['user_guide'],
            array($this, 'display_user_guide_page')
        );

    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our admin pages
        if (!$this->is_survey_admin_page($hook)) {
            return;
        }

        // Admin CSS
        wp_enqueue_style(
            'flowq-admin',
            FLOWQ_URL . 'assets/css/admin.css',
            array(),
            $this->version
        );

        // All Surveys page styles
        if ($hook === 'toplevel_page_' . $this->menu_slugs['all_surveys']) {
            wp_enqueue_style(
                'flowq-all-surveys',
                FLOWQ_URL . 'assets/css/admin-all-surveys.css',
                array('flowq-admin'),
                $this->version
            );
        }

        // Admin JavaScript
        wp_enqueue_script(
            'flowq-admin',
            FLOWQ_URL . 'assets/js/admin.js',
            array('jquery', 'wp-util'),
            $this->version,
            true
        );

        // Admin AJAX JavaScript
        wp_enqueue_script(
            'flowq-admin-ajax',
            FLOWQ_URL . 'assets/js/admin-ajax.js',
            array('jquery', 'flowq-admin'),
            $this->version,
            true
        );

        // Localize script for AJAX
        wp_localize_script('flowq-admin', 'flowqAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('flowq_admin_nonce'),
            'strings' => array(
                'confirm_delete' => __('Are you sure you want to delete this survey?', 'flowq'),
                'saving' => __('Saving...', 'flowq'),
                'saved' => __('Saved!', 'flowq'),
                'error' => __('An error occurred. Please try again.', 'flowq'),
                'loading' => __('Loading...', 'flowq'),
                'refresh' => __('Refresh', 'flowq'),
                'export' => __('Export Selected', 'flowq'),
                'exporting' => __('Exporting...', 'flowq'),
                'cleanup' => __('Cleanup Sessions', 'flowq'),
                'cleaning' => __('Cleaning...', 'flowq'),
                // Question management strings
                'add_question' => __('Add New Question', 'flowq'),
                'edit_question' => __('Edit Question', 'flowq'),
                'save_question' => __('Save Question', 'flowq'),
                'confirm_delete_question' => __('Are you sure you want to delete this question? This action cannot be undone.', 'flowq')
            )
        ));

        // WordPress media library for file uploads
        if ($hook === 'flowq_page_' . $this->menu_slugs['add_survey']) {
            wp_enqueue_media();

            // Add/Edit Survey page styles
            wp_enqueue_style(
                'flowq-add-survey',
                FLOWQ_URL . 'assets/css/admin-add-survey.css',
                array('flowq-admin'),
                $this->version
            );

            // jQuery UI Sortable for question reordering
            wp_enqueue_script('jquery-ui-sortable');

            // Question Management JavaScript
            wp_enqueue_script(
                'flowq-question-management',
                FLOWQ_URL . 'assets/js/question-management.js',
                array('jquery', 'jquery-ui-sortable', 'flowq-admin'),
                $this->version,
                true
            );

            // Add/Edit Survey page JavaScript
            wp_enqueue_script(
                'flowq-add-survey',
                FLOWQ_URL . 'assets/js/admin-add-survey.js',
                array('jquery', 'flowq-admin'),
                $this->version,
                true
            );
        }

        // Additional libraries for specific pages
        if ($hook === 'flowq_page_' . $this->menu_slugs['analytics']) {
            // Chart.js for analytics - loaded locally per WordPress.org requirements
            wp_enqueue_script(
                'chart-js',
                FLOWQ_URL . 'assets/js/chart.min.js',
                array(),
                '3.9.1',
                true  // Load in footer with other scripts
            );

            // Analytics page styles
            wp_enqueue_style(
                'flowq-analytics',
                FLOWQ_URL . 'assets/css/admin-analytics.css',
                array('flowq-admin'),
                $this->version
            );

            // Analytics page scripts
            wp_enqueue_script(
                'flowq-analytics',
                FLOWQ_URL . 'assets/js/admin-analytics.js',
                array('jquery', 'flowq-admin', 'chart-js'),
                $this->version,
                true
            );

            // Localize script for analytics-specific strings
            // Note: Chart data will be added via wp_add_inline_script in the template
            wp_localize_script('flowq-analytics', 'flowqAnalytics', array(
                'exportingText' => __('Exporting...', 'flowq'),
                'exportText' => __('Export CSV', 'flowq'),
                'failText' => __('Export failed. Please try again.', 'flowq')
            ));
        }

        // Survey builder page assets
        if (strpos($hook, 'flowq-survey-builder') !== false) {
            wp_enqueue_style(
                'flowq-survey-builder',
                FLOWQ_URL . 'assets/css/admin-survey-builder.css',
                array('flowq-admin'),
                $this->version
            );
        }

        // Contact page assets
        if ($hook === 'flowq_page_' . $this->menu_slugs['contact']) {
            wp_enqueue_style(
                'flowq-contact',
                FLOWQ_URL . 'assets/css/admin-contact.css',
                array('flowq-admin'),
                $this->version
            );
        }

        // Participants page assets
        if ($hook === 'flowq_page_' . $this->menu_slugs['participants']) {
            wp_enqueue_style(
                'flowq-participants',
                FLOWQ_URL . 'assets/css/admin-participants.css',
                array('flowq-admin'),
                $this->version
            );

            wp_enqueue_script(
                'flowq-participants',
                FLOWQ_URL . 'assets/js/admin-participants.js',
                array('jquery'),
                $this->version,
                true
            );
        }

        // Questions page assets
        if ($hook === 'flowq_page_' . $this->menu_slugs['questions']) {
            wp_enqueue_style(
                'flowq-questions-page',
                FLOWQ_URL . 'assets/css/admin-questions-page.css',
                array('flowq-admin'),
                $this->version
            );

            wp_enqueue_script(
                'flowq-questions-page',
                FLOWQ_URL . 'assets/js/admin-questions-page.js',
                array('jquery'),
                $this->version,
                true
            );

            wp_localize_script(
                'flowq-questions-page',
                'flowqQuestionsPage',
                array(
                    'nonce' => wp_create_nonce('flowq_admin_nonce'),
                    'i18n'  => array(
                        'endSurvey'   => __('End Survey', 'flowq'),
                        'saving'      => __('Saving...', 'flowq'),
                        'saved'       => __('Saved!', 'flowq'),
                        'save'        => __('Save', 'flowq'),
                        'errorSaving' => __('Error saving skip destination.', 'flowq'),
                    ),
                )
            );

            wp_enqueue_style(
                'flowq-question-form',
                FLOWQ_URL . 'assets/css/admin-question-form.css',
                array('flowq-admin'),
                $this->version
            );

            wp_enqueue_script(
                'flowq-question-form',
                FLOWQ_URL . 'assets/js/admin-question-form.js',
                array('jquery'),
                $this->version,
                true
            );

            wp_localize_script(
                'flowq-question-form',
                'flowqQuestionForm',
                array(
                    'i18n' => array(
                        'newAnswerOption' => __('New Answer Option', 'flowq'),
                    ),
                )
            );
        }

        // User Guide page assets
        if ($hook === 'flowq_page_' . $this->menu_slugs['user_guide']) {
            wp_enqueue_style(
                'flowq-user-guide',
                FLOWQ_URL . 'assets/css/admin-user-guide.css',
                array('flowq-admin'),
                $this->version
            );

            wp_enqueue_script(
                'flowq-user-guide',
                FLOWQ_URL . 'assets/js/admin-user-guide.js',
                array('jquery'),
                $this->version,
                true
            );
        }
    }

    /**
     * Display All Surveys page
     */
    public function display_all_surveys_page() {

        // Get surveys
        $survey_manager = new FlowQ_Survey_Manager();
        $surveys = $survey_manager->get_surveys(array(
            'limit' => 50,
            'orderby' => 'updated_at',
            'order' => 'DESC',
            'include_question_count' => true
        ));

        // Include template
        include FLOWQ_PATH . 'admin/templates/all-surveys.php';
    }

    /**
     * Display Add/Edit Survey page
     */
    public function display_add_survey_page() {
        // Sanitize GET parameter with absint()
        $survey_id = isset($_GET['survey_id']) ? absint($_GET['survey_id']) : 0;
        $survey = null;
        $questions = array();

        if ($survey_id) {
            $survey_manager = new FlowQ_Survey_Manager();
            $survey = $survey_manager->get_survey($survey_id);

            if ($survey) {
                $question_manager = new FlowQ_Question_Manager();
                $questions = $question_manager->get_survey_questions($survey_id, true);
            }
        }

        // Now safe to output HTML
        include FLOWQ_PATH . 'admin/templates/add-survey.php';
    }

    /**
     * Display Analytics page
     */
    public function display_analytics_page() {
        $survey_manager = new FlowQ_Survey_Manager();
        $surveys = $survey_manager->get_surveys(array('status' => 'published'));

        // Sanitize GET parameter with absint()
        $selected_survey_id = isset($_GET['survey_id']) ? absint($_GET['survey_id']) : 0;
        $analytics_data = array();

        if ($selected_survey_id) {
            $analytics_data = $this->get_survey_analytics($selected_survey_id);
        }

        // Include template
        include FLOWQ_PATH . 'admin/templates/analytics.php';
    }

    /**
     * Display Contact/Hire Developer page
     */
    public function display_contact_page() {
        // Include template
        include FLOWQ_PATH . 'admin/templates/contact-page.php';
    }

    /**
     * Display User Guide page
     */
    public function display_user_guide_page() {
        // Include template
        include FLOWQ_PATH . 'admin/templates/user-guide-page.php';
    }

    /**
     * Display Participants page
     */
    public function display_participants_page() {
        $survey_manager = new FlowQ_Survey_Manager();
        $participant_manager = new FlowQ_Participant_Manager();
        $question_manager = new FlowQ_Question_Manager();
        $session_manager = new FlowQ_Session_Manager();

        // Get all surveys for dropdown
        $surveys = $survey_manager->get_surveys();
        $selected_survey_id = isset($_GET['survey_id']) ? absint($_GET['survey_id']) : 0;
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';

        // Pagination parameters
        $per_page = isset($_GET['per_page']) ? sanitize_text_field($_GET['per_page']) : '20';
        $current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;

        // Validate per_page value
        $allowed_per_page = array('10', '20', '30', '40', 'all');
        if (!in_array($per_page, $allowed_per_page)) {
            $per_page = '20';
        }

        $participants_data = array();
        $survey = null;
        $stats = array('total' => 0, 'completed' => 0, 'in_progress' => 0);
        $pagination = array('total_items' => 0, 'total_pages' => 0, 'current_page' => $current_page, 'per_page' => $per_page);

        if ($selected_survey_id) {
            $survey = $survey_manager->get_survey($selected_survey_id);
            if ($survey) {
                // Get stats using direct DB queries
                global $wpdb;
                $table_name = $wpdb->prefix . 'flowq_participants';

                $result = $wpdb->get_row($wpdb->prepare(
                    "SELECT
                        COUNT(*) as total,
                        SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed
                     FROM {$table_name} WHERE survey_id = %d",
                    $selected_survey_id
                ), ARRAY_A);

                $stats['total'] = (int) $result['total'];
                $stats['completed'] = (int) $result['completed'];

                $stats['in_progress'] = $stats['total'] - $stats['completed'];

                // Calculate pagination for filtered results
                $filtered_count_query = "SELECT COUNT(*) FROM {$table_name} WHERE survey_id = %d";
                $count_params = array($selected_survey_id);

                if ($status_filter === 'completed') {
                    $filtered_count_query .= " AND completed_at IS NOT NULL";
                } elseif ($status_filter === 'in_progress') {
                    $filtered_count_query .= " AND completed_at IS NULL";
                }

                $total_filtered_items = $wpdb->get_var($wpdb->prepare($filtered_count_query, $count_params));

                // Set up pagination
                if ($per_page === 'all') {
                    $limit = (int)$total_filtered_items;
                    $offset = 0;
                    $pagination['total_pages'] = 1;
                } else {
                    $limit = (int)$per_page;
                    $offset = ($current_page - 1) * $limit;
                    $pagination['total_pages'] = ceil($total_filtered_items / $limit);
                }

                $pagination['total_items'] = $total_filtered_items;

                // Get participants based on status filter with pagination
                $participant_args = array(
                    'limit' => $limit,
                    'offset' => $offset,
                    'orderby' => 'started_at',
                    'order' => 'DESC'
                );

                if ($status_filter === 'completed') {
                    $participant_args['status'] = 'completed';
                } elseif ($status_filter === 'in_progress') {
                    $participant_args['status'] = 'in_progress';
                }
                // For 'all', don't add status filter

                $participants = $participant_manager->get_survey_participants($selected_survey_id, $participant_args);
                $participants_data = $this->build_participants_data($participants, $question_manager, $session_manager);
            }
        }

        // Include template
        include FLOWQ_PATH . 'admin/templates/participants.php';
    }

    /**
     * Handle survey actions (delete, activate, etc.)
     */
    private function handle_survey_actions() {
        if (!isset($_GET['survey_action']) || !isset($_GET['survey_id']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'survey_action')) {
            return;
        }

        $survey_id = absint($_GET['survey_id']);
        $action = sanitize_text_field($_GET['survey_action']);
        $survey_manager = new FlowQ_Survey_Manager();

        switch ($action) {
            case 'delete':
                // Get survey details to check status
                $survey = $survey_manager->get_survey($survey_id);
                if ($survey && $survey['status'] === 'published') {
                    $this->add_admin_notice(__('Cannot delete published survey. Please change the survey status to draft or archived first.', 'flowq'), 'error');
                } else {
                    $result = $survey_manager->delete_survey($survey_id);
                    if (is_wp_error($result)) {
                        $this->add_admin_notice($result->get_error_message(), 'error');
                    } else {
                        $this->add_admin_notice(__('Survey deleted successfully.', 'flowq'), 'success');
                    }
                }
                break;


            case 'publish':
                $result = $survey_manager->update_survey($survey_id, array('status' => 'published'));
                if (is_wp_error($result)) {
                    $this->add_admin_notice($result->get_error_message(), 'error');
                } else {
                    $this->add_admin_notice(__('Survey published.', 'flowq'), 'success');
                }
                break;

            case 'draft':
                $result = $survey_manager->update_survey($survey_id, array('status' => 'draft'));
                if (is_wp_error($result)) {
                    $this->add_admin_notice($result->get_error_message(), 'error');
                } else {
                    $this->add_admin_notice(__('Survey moved to draft.', 'flowq'), 'success');
                }
                break;
        }

        // Redirect to remove action from URL
        wp_safe_redirect(admin_url('admin.php?page=' . $this->menu_slugs['all_surveys']));
        exit;
    }

    /**
     * Handle save survey
     */
    private function handle_save_survey() {
        $survey_id = isset($_POST['survey_id']) ? intval($_POST['survey_id']) : 0;
        $survey_manager = new FlowQ_Survey_Manager();

        // Process header fields
        $show_header = isset($_POST['show_header']) ? 1 : 0;
        $form_header = sanitize_text_field($_POST['form_header'] ?? '');
        $form_subtitle = wp_kses_post($_POST['form_subtitle'] ?? '');

        // Validation: If show_header is enabled, form_header must not be empty
        if ($show_header && empty(trim($form_header))) {
            $this->add_admin_notice(__('Survey Form Header is required when Show Custom Header is enabled', 'flowq'), 'error');

            // Redirect back to form
            if ($survey_id) {
                wp_safe_redirect(admin_url('admin.php?page=' . $this->menu_slugs['add_survey'] . '&survey_id=' . $survey_id));
            } else {
                wp_safe_redirect(admin_url('admin.php?page=' . $this->menu_slugs['add_survey']));
            }
            exit;
        }

        $survey_data = array(
            'title' => sanitize_text_field($_POST['survey_title']),
            'description' => sanitize_textarea_field($_POST['survey_description']),
            'thank_you_page_slug' => sanitize_text_field($_POST['thank_you_page_slug'] ?? ''),
            'status' => sanitize_text_field($_POST['survey_status']),
            'show_header' => $show_header,
            'form_header' => $form_header,
            'form_subtitle' => wp_kses_post($form_subtitle),
        );

        if ($survey_id) {
            // Update existing survey
            $result = $survey_manager->update_survey($survey_id, $survey_data);
        } else {
            // Create new survey
            $result = $survey_manager->create_survey($survey_data);
            if (!is_wp_error($result)) {
                $survey_id = $result;
            }
        }

        if (is_wp_error($result)) {
            $this->add_admin_notice($result->get_error_message(), 'error');
        } else {

            $this->add_admin_notice(__('Survey saved successfully.', 'flowq'), 'success');

            // Redirect to edit page
            wp_safe_redirect(admin_url('admin.php?page=' . $this->menu_slugs['all_surveys'] . '&survey_id=' . $survey_id));
            exit;
        }
    }

    /**
     * Handle admin AJAX requests
     */
    public function handle_admin_ajax() {
        check_ajax_referer('flowq_admin_nonce', 'nonce');

        $action = sanitize_text_field($_POST['admin_action']);

        switch ($action) {
            case 'get_survey_stats':
                $this->ajax_get_survey_stats();
                break;

            case 'export_responses':
                $this->ajax_export_responses();
                break;

            default:
                wp_send_json_error('Invalid action');
        }
    }

    /**
     * AJAX: Get survey statistics
     */
    private function ajax_get_survey_stats() {
        $survey_id = intval($_POST['survey_id']);
        $survey_manager = new FlowQ_Survey_Manager();
        $stats = $survey_manager->get_survey_statistics($survey_id);
        wp_send_json_success($stats);
    }

    /**
     * AJAX: Export survey responses
     */
    private function ajax_export_responses() {
        $survey_id = intval($_POST['survey_id']);
        $session_manager = new FlowQ_Session_Manager();
        $csv_data = $session_manager->export_responses_csv($survey_id);
        wp_send_json_success(array('csv_data' => $csv_data));
    }

    /**
     * Get survey analytics data
     */
    private function get_survey_analytics($survey_id) {
        $survey_manager = new FlowQ_Survey_Manager();
        $session_manager = new FlowQ_Session_Manager();
        $question_manager = new FlowQ_Question_Manager();

        $survey_stats = $survey_manager->get_survey_statistics($survey_id);
        $questions = $question_manager->get_survey_questions($survey_id, false);

        $question_stats = array();
        foreach ($questions as $question) {
            $question_stats[] = $session_manager->get_question_statistics($question['id']);
        }

        return array(
            'survey_stats' => $survey_stats,
            'question_stats' => $question_stats,
            'questions' => $questions
        );
    }

    /**
     * Display questions management page
     */
    public function display_questions_page() {
        $survey_manager = new FlowQ_Survey_Manager();
        $question_manager = new FlowQ_Question_Manager();

        // Get all surveys for dropdown
        $surveys = $survey_manager->get_surveys();

        // Get selected survey ID from URL parameter - sanitized with absint()
        $selected_survey_id = isset($_GET['survey_id']) ? absint($_GET['survey_id']) : 0;
        $selected_question_id = isset($_GET['question_id']) ? absint($_GET['question_id']) : 0;

        // Get survey and questions data if survey is selected
        $survey = null;
        $questions = array();
        $question_data = null;

        if ($selected_survey_id) {
            $survey = $survey_manager->get_survey($selected_survey_id);
            if ($survey) {
                $questions = $question_manager->get_survey_questions_with_response_count($selected_survey_id);
            }
        }

        // Get specific question data if editing
        if ($selected_question_id) {
            $question_data = $question_manager->get_question($selected_question_id, true);
        }


        // Include the template
        include FLOWQ_PATH . 'admin/templates/questions-page.php';
    }

    /**
     * Handle question form submission
     */
    private function handle_question_form_submission() {
        // Verify nonce
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'flowq_question_action')) {
            wp_die(__('Security check failed.', 'flowq'));
        }

        $question_manager = new FlowQ_Question_Manager();
        $action = sanitize_text_field($_POST['question_action']);
        $survey_id = intval($_POST['survey_id']);

        try {
            switch ($action) {
                case 'create_question':
                    $question_id = $this->create_question_from_form($question_manager, $survey_id);
                    $this->add_admin_notice(__('Question created successfully!', 'flowq'), 'success');
                    break;

                case 'update_question':
                    $question_id = intval($_POST['question_id']);
                    $this->update_question_from_form($question_manager, $question_id);
                    $this->add_admin_notice(__('Question updated successfully!', 'flowq'), 'success');
                    break;

                case 'delete_question':
                    $question_id = intval($_POST['question_id']);
                    $question_manager->delete_question($question_id);
                    $this->add_admin_notice(__('Question deleted successfully!', 'flowq'), 'success');
                    break;
            }

            // Redirect to prevent form resubmission
            $redirect_url = admin_url('admin.php?page=flowq-questions&survey_id=' . $survey_id);
            wp_redirect($redirect_url);
            exit;

        } catch (Exception $e) {
            $this->add_admin_notice($e->getMessage(), 'error');
        }
    }

    /**
     * Handle question delete action from GET request
     */
    private function handle_question_delete_action() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'flowq'));
        }

        // Verify nonce
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'flowq_question_action')) {
            wp_die(__('Security check failed.', 'flowq'));
        }

        $question_id = absint($_GET['question_id']);
        $survey_id = absint($_GET['survey_id']);

        if (!$question_id || !$survey_id) {
            wp_die(__('Invalid question or survey ID.', 'flowq'));
        }

        $question_manager = new FlowQ_Question_Manager();
        $survey_manager = new FlowQ_Survey_Manager();

        try {
            // Get survey details to check status
            $survey = $survey_manager->get_survey($survey_id);
            if (!$survey) {
                $this->add_admin_notice(__('Survey not found.', 'flowq'), 'error');
                $redirect_url = admin_url('admin.php?page=flowq-questions&survey_id=' . $survey_id);
                wp_redirect($redirect_url);
                exit;
            }

            // Check if survey is in draft status
            if ($survey['status'] !== 'draft') {
                $this->add_admin_notice(__('Questions can only be deleted from draft surveys. Please set the survey to draft status first.', 'flowq'), 'error');
                $redirect_url = admin_url('admin.php?page=flowq-questions&survey_id=' . $survey_id);
                wp_redirect($redirect_url);
                exit;
            }

            // Check if question has any responses
            $response_count = $this->get_question_responses_count($question_id);

            if ($response_count > 0) {
                $this->add_admin_notice(
                    sprintf(
                        __('Cannot delete question: It has %d response(s). Questions with responses cannot be deleted to maintain data integrity.', 'flowq'),
                        $response_count
                    ),
                    'error'
                );
                $redirect_url = admin_url('admin.php?page=flowq-questions&survey_id=' . $survey_id);
                wp_redirect($redirect_url);
                exit;
            }

            // All checks passed, proceed with deletion
            $result = $question_manager->delete_question($question_id);

            if (is_wp_error($result)) {
                $this->add_admin_notice($result->get_error_message(), 'error');
            } else {
                $this->add_admin_notice(__('Question deleted successfully!', 'flowq'), 'success');
            }

        } catch (Exception $e) {
            $this->add_admin_notice($e->getMessage(), 'error');
        }

        // Redirect to prevent re-execution
        $redirect_url = admin_url('admin.php?page=flowq-questions&survey_id=' . $survey_id);
        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Create question from form data
     */
    private function create_question_from_form($question_manager, $survey_id) {
        $question_data = array(
            'title' => sanitize_textarea_field($_POST['question_title']),
            'description' => sanitize_textarea_field($_POST['question_description']),
            'extra_message' => sanitize_textarea_field($_POST['question_extra_message'] ?? ''),
            'is_required' => rest_sanitize_boolean($_POST['question_is_required'])
        );

        $question_id = $question_manager->create_question($survey_id, $question_data);

        // Handle answer options (always single choice)
        if (isset($_POST['answer_text']) && is_array($_POST['answer_text'])) {
            $answer_data = array(
                'answer_text' => $_POST['answer_text'],
                'answer_id' => isset($_POST['answer_id']) ? $_POST['answer_id'] : array(),
                'next_question_id' => isset($_POST['next_question_id']) ? $_POST['next_question_id'] : array(),
                'answer_redirect_url' => isset($_POST['answer_redirect_url']) ? $_POST['answer_redirect_url'] : array()
            );
            $this->save_answer_options($question_manager, $question_id, $answer_data);
        }

        return $question_id;
    }

    /**
     * Update question from form data
     */
    private function update_question_from_form($question_manager, $question_id) {
        $question_data = array(
            'title' => sanitize_textarea_field($_POST['question_title']),
            'description' => sanitize_textarea_field($_POST['question_description']),
            'extra_message' => sanitize_textarea_field($_POST['question_extra_message'] ?? ''),
            'is_required' => isset($_POST['question_is_required']) ? 1 : 0
        );

        $question_manager->update_question($question_id, $question_data);

        // Handle answer options (always single choice)
        if (isset($_POST['answer_text']) && is_array($_POST['answer_text'])) {
            $answer_data = array(
                'answer_text' => $_POST['answer_text'],
                'answer_id' => isset($_POST['answer_id']) ? $_POST['answer_id'] : array(),
                'next_question_id' => isset($_POST['next_question_id']) ? $_POST['next_question_id'] : array(),
                'answer_redirect_url' => isset($_POST['answer_redirect_url']) ? $_POST['answer_redirect_url'] : array()
            );
            $this->save_answer_options($question_manager, $question_id, $answer_data);
        }
    }

    /**
     * Save answer options
     */
    private function save_answer_options($question_manager, $question_id, $post_data) {
        $answer_texts = $post_data['answer_text'];
        $answer_ids = isset($post_data['answer_id']) ? $post_data['answer_id'] : array();
        $next_questions = isset($post_data['next_question_id']) ? $post_data['next_question_id'] : array();
        $redirect_urls = isset($post_data['answer_redirect_url']) ? $post_data['answer_redirect_url'] : array();

        foreach ($answer_texts as $index => $answer_text) {
            if (empty(trim($answer_text))) {
                continue;
            }

            $answer_data = array(
                'answer_text' => sanitize_text_field($answer_text),
                'next_question_id' => !empty($next_questions[$index]) ? intval($next_questions[$index]) : null,
                'redirect_url' => !empty($redirect_urls[$index]) ? esc_url_raw($redirect_urls[$index]) : null,
                'answer_order' => $index + 1
            );

            $answer_id = !empty($answer_ids[$index]) ? intval($answer_ids[$index]) : 0;

            if ($answer_id) {
                $question_manager->update_answer($answer_id, $answer_data);
            } else {
                $question_manager->create_answer($question_id, $answer_data);
            }
        }
    }

    /**
     * Create Yes/No answers for boolean questions
     */
    private function create_boolean_answers($question_manager, $question_id) {
        // Delete existing answers first (in case we're updating)
        global $wpdb;
        $wpdb->delete(
            $wpdb->prefix . 'dynamic_survey_answers',
            array('question_id' => $question_id),
            array('%d')
        );

        // Create Yes answer
        $question_manager->create_answer($question_id, array(
            'answer_text' => __('Yes', 'flowq'),
            'answer_order' => 1,
            'next_question_id' => null,
            'redirect_url' => null
        ));

        // Create No answer
        $question_manager->create_answer($question_id, array(
            'answer_text' => __('No', 'flowq'),
            'answer_order' => 2,
            'next_question_id' => null,
            'redirect_url' => null
        ));
    }

    /**
     * Add custom capabilities
     */
    public function add_custom_capabilities() {
        $role = get_role('administrator');
        if ($role) {
            $role->add_cap('manage_surveys');
            $role->add_cap('edit_surveys');
            $role->add_cap('view_survey_analytics');
        }
    }

    /**
     * Check if current page is a survey admin page
     */
    private function is_survey_admin_page($hook) {
        $survey_pages = array(
            'toplevel_page_' . $this->menu_slugs['main'],
            'flowq_page_' . $this->menu_slugs['add_survey'],
            'flowq_page_' . $this->menu_slugs['questions'],
            'flowq_page_' . $this->menu_slugs['participants'],
            'flowq_page_' . $this->menu_slugs['analytics'],
            'flowq_page_' . $this->menu_slugs['contact'],
            'flowq_page_' . $this->menu_slugs['user_guide'],
        );

        return in_array($hook, $survey_pages);
    }

    /**
     * Add admin notice
     */
    private function add_admin_notice($message, $type = 'info') {
        $notices = get_transient('flowq_admin_notices') ?: array();
        $notices[] = array(
            'message' => $message,
            'type' => $type
        );
        set_transient('flowq_admin_notices', $notices, 30);
    }

    /**
     * Show admin notices
     */
    public function show_admin_notices() {
        $notices = get_transient('flowq_admin_notices');
        if ($notices) {
            foreach ($notices as $notice) {
                echo '<div class="notice notice-' . esc_attr($notice['type']) . ' is-dismissible">';
                echo '<p>' . esc_html($notice['message']) . '</p>';
                echo '</div>';
            }
            delete_transient('flowq_admin_notices');
        }
    }

    /**
     * Get responses count for a specific question
     *
     * @param int $question_id Question ID
     * @return int Number of responses
     */
    private function get_question_responses_count($question_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'flowq_responses';

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE question_id = %d",
                $question_id
            )
        );

        return intval($count);
    }

    /**
     * Handle survey save action via admin_post
     */
    public function handle_save_survey_action() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'flowq'));
        }

        // Verify nonce
        if (!check_admin_referer('flowq_save_survey')) {
            wp_die(__('Security check failed.', 'flowq'));
        }

        // Call the existing handler
        $this->handle_save_survey();
    }

    /**
     * Handle question save action via admin_post
     */
    public function handle_save_question_action() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'flowq'));
        }

        // Verify nonce - the form uses flowq_question_action
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'flowq_question_action')) {
            wp_die(__('Security check failed.', 'flowq'));
        }

        // Call the existing handler
        $this->handle_question_form_submission();
    }

    /**
     * Handle question delete action via admin_post
     */
    public function handle_delete_question_action() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'flowq'));
        }

        // Verify nonce
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'flowq_question_action')) {
            wp_die(__('Security check failed.', 'flowq'));
        }

        // Call the existing handler
        $this->handle_question_delete_action();
    }

    /**
     * Handle survey actions (delete, publish, draft) via admin_post
     */
    public function handle_survey_action() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'flowq'));
        }

        // Verify nonce
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'survey_action')) {
            wp_die(__('Security check failed.', 'flowq'));
        }

        // Call the existing handler
        $this->handle_survey_actions();
    }

    /**
     * Build participants data with their responses
     *
     * @param array $participants Array of participant records
     * @param FlowQ_Question_Manager $question_manager Question manager instance
     * @param FlowQ_Session_Manager $session_manager Session manager instance
     * @return array Formatted participants data
     */
    function build_participants_data($participants, $question_manager, $session_manager) {
        $participants_data = array();

        // Get all session IDs for batch response loading
        $session_ids = array_column($participants, 'session_id');

        // Get all responses for all participants in one query
        $all_responses = $session_manager->get_multiple_session_responses($session_ids);

        // Get all unique question IDs from all responses
        $all_question_ids = array();
        foreach ($all_responses as $responses) {
            foreach ($responses as $response) {
                if (isset($response['question_id']) && !empty($response['question_id'])) {
                    $all_question_ids[] = $response['question_id'];
                }
            }
        }
        $all_question_ids = array_unique($all_question_ids);

        // Load all questions in one query
        $all_questions = $question_manager->get_multiple_questions($all_question_ids, true);

        // Process each participant with their responses
        foreach ($participants as $participant) {
            $session_id = $participant['session_id'];
            $responses = isset($all_responses[$session_id]) ? $all_responses[$session_id] : array();
            $formatted_responses = array();

            foreach ($responses as $response) {
                if (!isset($response['question_id']) || empty($response['question_id'])) {
                    continue; // Skip responses without question_id
                }

                $question = isset($all_questions[$response['question_id']]) ? $all_questions[$response['question_id']] : null;
                if ($question) {
                    $formatted_response = array(
                        'question' => $question['title'],
                        'response_time' => $response['responded_at'],
                        'question_id' => $question['id'],
                    );

                    if ($response['answer_id']) {
                        foreach ($question['answers'] as $answer) {
                            if ($answer['id'] == $response['answer_id']) {
                                $formatted_response['answer'] = $answer['answer_text'];
                                break;
                            }
                        }
                    }

                    if ($response['answer_text']) {
                        $formatted_response['custom_answer'] = $response['answer_text'];
                    }

                    $formatted_responses[] = $formatted_response;
                }
            }

            $participants_data[] = array(
                'participant' => $participant,
                'responses' => $formatted_responses,
                'response_count' => count($formatted_responses),
                'completion_status' => !empty($participant['completed_at']) ? 'completed' : 'incomplete'
            );
        }

        return $participants_data;
    }
}