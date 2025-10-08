<?php
/**
 * Plugin Name: WordPress Survey Plugin
 * Plugin URI: https://example.com/wordpress-survey-plugin
 * Description: A WordPress plugin that adds dynamic survey functionality with conditional branching, question sequencing, and external redirections.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-dynamic-survey
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants with unique prefix
define('WP_DYNAMIC_SURVEY_VERSION', '1.0.0');
define('WP_DYNAMIC_SURVEY_FILE', __FILE__);
define('WP_DYNAMIC_SURVEY_PATH', plugin_dir_path(__FILE__));
define('WP_DYNAMIC_SURVEY_URL', plugin_dir_url(__FILE__));
define('WP_DYNAMIC_SURVEY_BASENAME', plugin_basename(__FILE__));
define('WP_DYNAMIC_SURVEY_TEXT_DOMAIN', 'wp-dynamic-survey');

// Main plugin class with unique prefix
class WP_Dynamic_Survey_Plugin {

    /**
     * Single instance of the plugin
     */
    private static $instance = null;

    /**
     * Plugin version
     */
    public $version = WP_DYNAMIC_SURVEY_VERSION;

    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(WP_DYNAMIC_SURVEY_FILE, array($this, 'activate'));
        register_deactivation_hook(WP_DYNAMIC_SURVEY_FILE, array($this, 'deactivate'));

        // Initialize plugin after WordPress loads
        add_action('plugins_loaded', array($this, 'init'), 10);

        // Load text domain for translations
        add_action('init', array($this, 'load_textdomain'));
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Autoloader
        require_once WP_DYNAMIC_SURVEY_PATH . 'includes/class-autoloader.php';

        // Core classes
        require_once WP_DYNAMIC_SURVEY_PATH . 'includes/class-survey-manager.php';
        require_once WP_DYNAMIC_SURVEY_PATH . 'includes/class-participant-manager.php';
        require_once WP_DYNAMIC_SURVEY_PATH . 'includes/class-question-manager.php';
        require_once WP_DYNAMIC_SURVEY_PATH . 'includes/class-session-manager.php';
        require_once WP_DYNAMIC_SURVEY_PATH . 'includes/class-template-handler.php';
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Check if WordPress and PHP versions are compatible
        if (!$this->check_requirements()) {
            return;
        }

        // Check database version and run migrations if needed
        $this->check_database_version();

        // Initialize admin functionality
        if (is_admin()) {
            $this->init_admin();
        }

        // Initialize frontend functionality
        $this->init_frontend();

        // Initialize enhanced shortcode system
        $this->init_shortcodes();

        // Initialize AJAX handlers
        $this->init_ajax();

        // Initialize REST API
        $this->init_rest_api();

    }

    /**
     * Check plugin requirements
     */
    private function check_requirements() {
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo __('WordPress Survey Plugin requires PHP 7.4 or higher.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
                echo '</p></div>';
            });
            return false;
        }

        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, '5.0', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo __('WordPress Survey Plugin requires WordPress 5.0 or higher.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
                echo '</p></div>';
            });
            return false;
        }

        return true;
    }

    /**
     * Initialize admin functionality
     */
    private function init_admin() {
        // Load admin class
        require_once WP_DYNAMIC_SURVEY_PATH . 'admin/class-admin.php';
        new WP_Dynamic_Survey_Admin();

        // Load question admin class for AJAX handlers
        require_once WP_DYNAMIC_SURVEY_PATH . 'admin/class-question-admin.php';
        new WP_Dynamic_Survey_Question_Admin();

        // Load settings admin class
        require_once WP_DYNAMIC_SURVEY_PATH . 'admin/class-settings-admin.php';
        new WP_Dynamic_Survey_Settings_Admin();

    }

    /**
     * Initialize frontend functionality
     */
    private function init_frontend() {
        // Load frontend class
        require_once WP_DYNAMIC_SURVEY_PATH . 'public/class-frontend.php';
        new WP_Dynamic_Survey_Frontend();
    }

    /**
     * Initialize enhanced shortcode system
     */
    private function init_shortcodes() {
        require_once WP_DYNAMIC_SURVEY_PATH . 'includes/class-shortcode.php';
        new WP_Dynamic_Survey_Shortcode();

        if (is_admin()) {
            require_once WP_DYNAMIC_SURVEY_PATH . 'admin/class-shortcode-builder.php';
            new WP_Dynamic_Survey_Shortcode_Builder();
        }
    }

    /**
     * Initialize AJAX handlers
     */
    private function init_ajax() {
        // Include and initialize centralized AJAX handler
        require_once WP_DYNAMIC_SURVEY_PATH . 'includes/class-ajax-handler.php';
        new WP_Dynamic_Survey_Ajax_Handler();

        // AJAX handlers are now handled in the frontend class
        // Frontend class registers all necessary AJAX handlers
    }

    /**
     * Initialize REST API
     */
    private function init_rest_api() {
        // Include and initialize REST API handler
        require_once WP_DYNAMIC_SURVEY_PATH . 'includes/class-rest-api.php';
        new WP_Dynamic_Survey_REST_API();
    }


    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $this->create_tables();

        // Set default options
        $this->set_default_options();

        // Create upload directories
        $this->create_directories();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Clear any scheduled events
        wp_clear_scheduled_hook('wp_dynamic_survey_cleanup_sessions');
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        // Load and run database migrator
        require_once WP_DYNAMIC_SURVEY_PATH . 'includes/class-db-migrator.php';
        $migrator = new WP_Dynamic_Survey_DB_Migrator();
        $migrator->create_tables();
    }

    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $default_settings = array(
            'version' => WP_DYNAMIC_SURVEY_VERSION,
            'session_timeout' => 3600, // 1 hour
            'auto_save_interval' => 30, // 30 seconds
            'enable_progress_bar' => true,
            'allow_back_navigation' => false,
            'require_all_fields' => true
        );

        add_option('wp_dynamic_survey_settings', $default_settings);
        add_option('wp_dynamic_survey_version', WP_DYNAMIC_SURVEY_VERSION);
    }

    /**
     * Create necessary directories
     */
    private function create_directories() {
        $upload_dir = wp_upload_dir();
        $survey_dir = $upload_dir['basedir'] . '/wp-dynamic-surveys';

        if (!file_exists($survey_dir)) {
            wp_mkdir_p($survey_dir);

            // Create .htaccess for security
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "<Files *.php>\n";
            $htaccess_content .= "deny from all\n";
            $htaccess_content .= "</Files>\n";

            file_put_contents($survey_dir . '/.htaccess', $htaccess_content);
        }
    }

    /**
     * Load text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            WP_DYNAMIC_SURVEY_TEXT_DOMAIN,
            false,
            dirname(WP_DYNAMIC_SURVEY_BASENAME) . '/languages'
        );
    }

    /**
     * Get plugin option
     */
    public function get_option($key, $default = null) {
        $options = get_option('wp_dynamic_survey_settings', array());
        return isset($options[$key]) ? $options[$key] : $default;
    }

    /**
     * Update plugin option
     */
    public function update_option($key, $value) {
        $options = get_option('wp_dynamic_survey_settings', array());
        $options[$key] = $value;
        update_option('wp_dynamic_survey_settings', $options);
    }

    /**
     * Check database version and run migrations if needed
     */
    private function check_database_version() {
        require_once WP_DYNAMIC_SURVEY_PATH . 'includes/class-db-migrator.php';
        $migrator = new WP_Dynamic_Survey_DB_Migrator();
        $migrator->check_version();
    }

    /**
     * Clean up plugin data (for uninstall)
     */
    public static function uninstall() {
        // Remove all plugin options
        delete_option('wp_dynamic_survey_settings');
        delete_option('wp_dynamic_survey_version');
        delete_option('wp_dynamic_survey_db_version');

        // Drop all plugin tables
        require_once WP_DYNAMIC_SURVEY_PATH . 'includes/class-db-migrator.php';
        $migrator = new WP_Dynamic_Survey_DB_Migrator();
        $migrator->drop_tables();

        // Remove upload directory
        $upload_dir = wp_upload_dir();
        $survey_dir = $upload_dir['basedir'] . '/wp-dynamic-surveys';

        if (file_exists($survey_dir)) {
            // Remove all files in directory
            $files = glob($survey_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            // Remove directory
            rmdir($survey_dir);
        }

        // Clear any scheduled events
        wp_clear_scheduled_hook('wp_dynamic_survey_cleanup_sessions');
    }
}

/**
 * Initialize the plugin
 */
function wp_dynamic_survey_plugin() {
    add_action('pre_get_posts', function($query) {
        if (is_admin()) {
            return;
        }

        // Static variable for memoization - persists across function calls
        static $exclude_ids = null;

        if ($exclude_ids === null) {
            // Get thank you page IDs from database (only once per request)
            global $wpdb;
            $thank_you_slugs = $wpdb->get_col("SELECT DISTINCT thank_you_page_slug FROM {$wpdb->prefix}wp_dynamic_survey_surveys WHERE thank_you_page_slug IS NOT NULL AND thank_you_page_slug != ''");

            if (empty($thank_you_slugs)) {
                $exclude_ids = array(); // Cache empty result
            } else {
                // Get page IDs for these slugs
                $placeholders = implode(',', array_fill(0, count($thank_you_slugs), '%s'));
                $exclude_ids = $wpdb->get_col($wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE post_name IN ($placeholders) AND post_type = 'page'",
                    ...$thank_you_slugs
                ));

                if (empty($exclude_ids)) {
                    $exclude_ids = array(); // Cache empty result
                }
            }
        }

        if (empty($exclude_ids)) {
            return;
        }

        // Only affect queries for pages
        if (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'page') {
            if (isset($query->query_vars['post__not_in'])) {
                $query->query_vars['post__not_in'] = array_merge($query->query_vars['post__not_in'], $exclude_ids);
            } else {
                $query->query_vars['post__not_in'] = $exclude_ids;
            }
        }

    });

    return WP_Dynamic_Survey_Plugin::get_instance();
}

// Register uninstall hook
register_uninstall_hook(WP_DYNAMIC_SURVEY_FILE, array('WP_Dynamic_Survey_Plugin', 'uninstall'));

// Start the plugin
wp_dynamic_survey_plugin();