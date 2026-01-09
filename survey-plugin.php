<?php
/**
 * Plugin Name: FlowQ
 * Plugin URI: https://github.com/dev-parvej/FlowQ
 * Description: Create intelligent, dynamic surveys that adapt in real-time to user responses. Engage your audience with interactive question flows built right inside WordPress.
 * Version: 1.0.0
 * Author: Parvej Ahammad
 * Author URI: https://www.linkedin.com/in/dev-parvej
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: flowq
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants with unique prefix
define('FLOWQ_VERSION', '1.0.0');
define('FLOWQ_FILE', __FILE__);
define('FLOWQ_PATH', plugin_dir_path(__FILE__));
define('FLOWQ_URL', plugin_dir_url(__FILE__));
define('FLOWQ_BASENAME', plugin_basename(__FILE__));

// Main plugin class with unique prefix
class FlowQ_Plugin {

    /**
     * Single instance of the plugin
     */
    private static $instance = null;

    /**
     * Plugin version
     */
    public $version = FLOWQ_VERSION;

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
        register_activation_hook(FLOWQ_FILE, array($this, 'activate'));
        register_deactivation_hook(FLOWQ_FILE, array($this, 'deactivate'));

        // Initialize plugin after WordPress loads
        add_action('plugins_loaded', array($this, 'init'), 10);
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Autoloader
        require_once FLOWQ_PATH . 'includes/class-autoloader.php';

        // Core classes
        require_once FLOWQ_PATH . 'includes/class-survey-manager.php';
        require_once FLOWQ_PATH . 'includes/class-participant-manager.php';
        require_once FLOWQ_PATH . 'includes/class-question-manager.php';
        require_once FLOWQ_PATH . 'includes/class-session-manager.php';
        require_once FLOWQ_PATH . 'includes/class-template-handler.php';
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
                echo esc_html__('FlowQ requires PHP 7.4 or higher.', 'flowq');
                echo '</p></div>';
            });
            return false;
        }

        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, '5.0', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo esc_html__('FlowQ requires WordPress 5.0 or higher.', 'flowq');
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
        require_once FLOWQ_PATH . 'admin/class-admin.php';
        new FlowQ_Admin();

        // Load question admin class for AJAX handlers
        require_once FLOWQ_PATH . 'admin/class-question-admin.php';
        new FlowQ_Question_Admin();

        // Load settings admin class
        require_once FLOWQ_PATH . 'admin/class-settings-admin.php';
        new FlowQ_Settings_Admin();

    }

    /**
     * Initialize frontend functionality
     */
    private function init_frontend() {
        // Load frontend class
        require_once FLOWQ_PATH . 'public/class-frontend.php';
        new FlowQ_Frontend();

        // Initialize template handler for proper CSS enqueuing
        $template_handler = new FlowQ_Template_Handler();
        $template_handler->init();
    }

    /**
     * Initialize enhanced shortcode system
     */
    private function init_shortcodes() {
        require_once FLOWQ_PATH . 'includes/class-shortcode.php';
        new FlowQ_Shortcode();

        if (is_admin()) {
            require_once FLOWQ_PATH . 'admin/class-shortcode-builder.php';
            new FlowQ_Shortcode_Builder();
        }
    }

    /**
     * Initialize AJAX handlers
     */
    private function init_ajax() {
        // Include and initialize centralized AJAX handler
        require_once FLOWQ_PATH . 'includes/class-ajax-handler.php';
        new FlowQ_Ajax_Handler();

        // AJAX handlers are now handled in the frontend class
        // Frontend class registers all necessary AJAX handlers
    }

    /**
     * Initialize REST API
     */
    private function init_rest_api() {
        // Include and initialize REST API handler
        require_once FLOWQ_PATH . 'includes/class-rest-api.php';
        new FlowQ_REST_API();
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
        wp_clear_scheduled_hook('flowq_cleanup_sessions');
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        // Load and run database migrator
        require_once FLOWQ_PATH . 'includes/class-db-migrator.php';
        $migrator = new FlowQ_DB_Migrator();
        $migrator->create_tables();
    }

    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $default_settings = array(
            'version' => FLOWQ_VERSION,
            'session_timeout' => 3600, // 1 hour
            'auto_save_interval' => 30, // 30 seconds
            'enable_progress_bar' => true,
            'allow_back_navigation' => false,
            'require_all_fields' => true
        );

        add_option('flowq_settings', $default_settings);
        add_option('flowq_version', FLOWQ_VERSION);
    }

    /**
     * Create necessary directories
     */
    private function create_directories() {
        $upload_dir = wp_upload_dir();
        $survey_dir = $upload_dir['basedir'] . '/flowq-surveys';

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
     * Get plugin option
     */
    public function get_option($key, $default = null) {
        $options = get_option('flowq_settings', array());
        return isset($options[$key]) ? $options[$key] : $default;
    }

    /**
     * Update plugin option
     */
    public function update_option($key, $value) {
        $options = get_option('flowq_settings', array());
        $options[$key] = $value;
        update_option('flowq_settings', $options);
    }

    /**
     * Check database version and run migrations if needed
     */
    private function check_database_version() {
        require_once FLOWQ_PATH . 'includes/class-db-migrator.php';
        $migrator = new FlowQ_DB_Migrator();
        $migrator->check_version();
    }

    /**
     * Clean up plugin data (for uninstall)
     */
    public static function uninstall() {
        // Remove all plugin options
        delete_option('flowq_settings');
        delete_option('flowq_version');
        delete_option('flowq_db_version');

        // Drop all plugin tables
        require_once FLOWQ_PATH . 'includes/class-db-migrator.php';
        $migrator = new FlowQ_DB_Migrator();
        $migrator->drop_tables();

        // Remove upload directory
        $upload_dir = wp_upload_dir();
        $survey_dir = $upload_dir['basedir'] . '/flowq-surveys';

        if (file_exists($survey_dir)) {
            // Remove all files in directory
            $files = glob($survey_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    wp_delete_file($file);
                }
            }
            // Remove directory - using rmdir as WP_Filesystem requires complex initialization
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- Safe usage during plugin deactivation
            rmdir($survey_dir);
        }

        // Clear any scheduled events
        wp_clear_scheduled_hook('flowq_cleanup_sessions');
    }
}

/**
 * Initialize the plugin
 */
function flowq_plugin() {
    add_action('pre_get_posts', function($query) {
        if (is_admin()) {
            return;
        }

        // Static variable for per-request memoization
        static $exclude_ids = null;

        if ($exclude_ids === null) {
            // Try to get from object cache first
            $cache_key = 'flowq_thank_you_page_ids';
            $cache_group = 'flowq';
            $exclude_ids = wp_cache_get($cache_key, $cache_group);

            if ($exclude_ids === false) {
                // Cache miss - query database
                global $wpdb;

                // Get thank you page slugs
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table requires direct query
                $thank_you_slugs = $wpdb->get_col(
                    "SELECT DISTINCT thank_you_page_slug FROM {$wpdb->prefix}flowq_surveys WHERE thank_you_page_slug IS NOT NULL AND thank_you_page_slug != ''"
                );

                if (empty($thank_you_slugs)) {
                    $exclude_ids = array();
                } else {
                    // Get page IDs for these slugs
                    $placeholders = implode(',', array_fill(0, count($thank_you_slugs), '%s'));
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Need to query wp_posts for page IDs
                    $exclude_ids = $wpdb->get_col($wpdb->prepare(
                        "SELECT ID FROM {$wpdb->posts} WHERE post_name IN ($placeholders) AND post_type = 'page'",
                        ...$thank_you_slugs
                    ));

                    if (empty($exclude_ids)) {
                        $exclude_ids = array();
                    }
                }

                // Store in cache for 12 hours (cache is invalidated when surveys are updated)
                wp_cache_set($cache_key, $exclude_ids, $cache_group, 12 * HOUR_IN_SECONDS);
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

    // Invalidate cache when surveys are modified
    add_action('flowq_survey_created', 'flowq_invalidate_thank_you_cache');
    add_action('flowq_survey_updated', 'flowq_invalidate_thank_you_cache');
    add_action('flowq_survey_deleted', 'flowq_invalidate_thank_you_cache');

    return FlowQ_Plugin::get_instance();
}

/**
 * Invalidate thank you page cache when surveys are modified
 *
 * @return void
 */
function flowq_invalidate_thank_you_cache() {
    wp_cache_delete('flowq_thank_you_page_ids', 'flowq');
}

// Register uninstall hook
register_uninstall_hook(FLOWQ_FILE, array('FlowQ_Plugin', 'uninstall'));

// Start the plugin
flowq_plugin();