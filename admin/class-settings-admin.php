<?php
/**
 * Settings Admin Interface for WP Dynamic Survey Plugin
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings Admin class
 */
class WP_Dynamic_Survey_Settings_Admin {

    /**
     * Plugin version
     */
    private $version;

    /**
     * Menu slug
     */
    private $menu_slug = 'wp-dynamic-survey-settings';

    /**
     * Constructor
     */
    public function __construct() {
        $this->version = WP_DYNAMIC_SURVEY_VERSION;
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_settings_menu'));
        add_action('admin_post_wp_dynamic_survey_save_settings', array($this, 'handle_save_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Add Settings submenu to admin menu
     */
    public function add_settings_menu() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            return;
        }

        // Add Settings submenu
        add_submenu_page(
            'wp-dynamic-surveys',                                   // Parent slug
            __('Settings', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),         // Page title
            __('Settings', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),         // Menu title
            'manage_options',                                       // Capability
            $this->menu_slug,                                      // Menu slug
            array($this, 'display_settings_page')                  // Callback
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on settings page
        if ($hook !== 'wp-dynamic-surveys_page_wp-dynamic-survey-settings') {
            return;
        }

        wp_enqueue_script('jquery');
    }

    /**
     * Display Settings page with tab navigation
     */
    public function display_settings_page() {
        // Get current tab from URL parameter, default to 'general'
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

        // Define available tabs
        $tabs = array(
            'general' => __('General', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
            'templates' => __('Templates', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
            // Additional tabs will be added in future updates
        );

        // Include the settings page template
        include WP_DYNAMIC_SURVEY_PATH . 'admin/templates/settings-page.php';
    }

    /**
     * Render General tab content
     */
    public function render_general_tab() {
        // Default privacy policy texts
        $default_privacy_single = '<p>We respect your privacy and are committed to protecting your personal information. By submitting this form, you agree that your data will be used solely for the purpose of this survey and will be handled in accordance with our <a href="/privacy-policy" target="_blank">Privacy Policy</a>.</p>';
        $default_privacy_stage1 = '<p>We respect your privacy. Your contact information will be used only for this survey and handled securely according to our <a href="/privacy-policy" target="_blank">Privacy Policy</a>.</p>';
        $default_privacy_stage2 = '<p>Your phone number will be kept confidential and used only for survey-related communication. See our <a href="/privacy-policy" target="_blank">Privacy Policy</a> for details.</p>';

        // Get current settings values
        $two_stage_form = get_option('wp_dynamic_survey_two_stage_form', 1);
        $two_page_mode = get_option('wp_dynamic_survey_two_page_mode', 0);
        $allow_duplicate_emails = get_option('wp_dynamic_survey_allow_duplicate_emails', 0);
        $field_address = get_option('wp_dynamic_survey_field_address', 1);
        $field_zipcode = get_option('wp_dynamic_survey_field_zipcode', 1);
        $field_phone = get_option('wp_dynamic_survey_field_phone', 1);
        $privacy_policy = get_option('wp_dynamic_survey_privacy_policy', $default_privacy_single);
        $privacy_policy_stage1 = get_option('wp_dynamic_survey_privacy_policy_stage1', $default_privacy_stage1);
        $privacy_policy_stage2 = get_option('wp_dynamic_survey_privacy_policy_stage2', $default_privacy_stage2);
        $phone_optional = get_option('wp_dynamic_survey_phone_optional', 0);

        // Include the general settings template
        include WP_DYNAMIC_SURVEY_PATH . 'admin/templates/general-settings.php';
    }

    /**
     * Render Templates tab content
     */
    public function render_templates_tab() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_dynamic_survey_templates';

        // Get all templates
        $templates = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY id ASC", ARRAY_A);

        // Get active template ID
        $active_template_id = get_option('wp_dynamic_survey_active_template', 1);

        // Include the template list view
        include WP_DYNAMIC_SURVEY_PATH . 'admin/templates/templates-list.php';
    }

    /**
     * Handle save settings action
     */
    public function handle_save_settings() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Verify nonce
        if (!check_admin_referer('wp_dynamic_survey_save_settings')) {
            wp_die(__('Security check failed.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Get current tab
        $tab = isset($_POST['current_tab']) ? sanitize_text_field($_POST['current_tab']) : 'templates';

        // Handle different tab settings
        switch ($tab) {
            case 'general':
                $this->save_general_settings();
                break;
            case 'templates':
                $this->save_templates_settings();
                break;
            // Additional tabs will be handled here in future updates
        }

        // Redirect back to settings page with success message
        $redirect_url = add_query_arg(
            array(
                'page' => $this->menu_slug,
                'tab' => $tab,
                'settings-updated' => 'true'
            ),
            admin_url('admin.php')
        );
        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Save general settings
     */
    private function save_general_settings() {
        // Setting 1: Two-Stage Form
        $two_stage_form = isset($_POST['two_stage_form']) ? 1 : 0;
        update_option('wp_dynamic_survey_two_stage_form', $two_stage_form);

        // Setting 2: Two-Page Survey Mode
        $two_page_mode = isset($_POST['two_page_mode']) ? 1 : 0;
        update_option('wp_dynamic_survey_two_page_mode', $two_page_mode);

        // Setting 3: Allow Duplicate Emails
        $allow_duplicate_emails = isset($_POST['allow_duplicate_emails']) ? 1 : 0;
        update_option('wp_dynamic_survey_allow_duplicate_emails', $allow_duplicate_emails);

        // Setting 4: Participant Information Fields
        $field_address = isset($_POST['field_address']) ? 1 : 0;
        update_option('wp_dynamic_survey_field_address', $field_address);

        $field_zipcode = isset($_POST['field_zipcode']) ? 1 : 0;
        update_option('wp_dynamic_survey_field_zipcode', $field_zipcode);

        $field_phone = isset($_POST['field_phone']) ? 1 : 0;
        update_option('wp_dynamic_survey_field_phone', $field_phone);

        // Setting 5: Privacy Policy Text
        // Sanitize HTML using wp_kses_post to allow safe HTML tags
        $privacy_policy = isset($_POST['privacy_policy']) ? wp_kses_post($_POST['privacy_policy']) : '';
        update_option('wp_dynamic_survey_privacy_policy', $privacy_policy);

        $privacy_policy_stage1 = isset($_POST['privacy_policy_stage1']) ? wp_kses_post($_POST['privacy_policy_stage1']) : '';
        update_option('wp_dynamic_survey_privacy_policy_stage1', $privacy_policy_stage1);

        $privacy_policy_stage2 = isset($_POST['privacy_policy_stage2']) ? wp_kses_post($_POST['privacy_policy_stage2']) : '';
        update_option('wp_dynamic_survey_privacy_policy_stage2', $privacy_policy_stage2);

        // Setting 6: Optional Phone Number Stage
        $phone_optional = isset($_POST['phone_optional']) ? 1 : 0;
        update_option('wp_dynamic_survey_phone_optional', $phone_optional);
    }

    /**
     * Save templates settings
     */
    private function save_templates_settings() {
        // Template settings will be saved here
        // This will be implemented when template functionality is added
    }
}
