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
        // Get current tab from URL parameter, default to 'templates'
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'templates';

        // Define available tabs
        $tabs = array(
            'templates' => __('Templates', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
            // Additional tabs will be added in future updates
        );

        // Include the settings page template
        include WP_DYNAMIC_SURVEY_PATH . 'admin/templates/settings-page.php';
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
     * Save templates settings
     */
    private function save_templates_settings() {
        // Template settings will be saved here
        // This will be implemented when template functionality is added
    }
}
