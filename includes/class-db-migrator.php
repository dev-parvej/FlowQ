<?php
/**
 * Database Migration Handler for WP Dynamic Survey Plugin
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database migrator class
 */
class WP_Dynamic_Survey_DB_Migrator {

    /**
     * Database version for migrations
     */
    const DB_VERSION = '1.0.0';

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
     * Create all plugin tables
     */
    public function create_tables() {
        $this->create_surveys_table();
        $this->create_participants_table();
        $this->create_questions_table();
        $this->create_answers_table();
        $this->create_responses_table();
        $this->create_templates_table();

        // Update database version
        update_option('wp_dynamic_survey_db_version', self::DB_VERSION);
    }

    /**
     * Create wp_surveys table
     */
    private function create_surveys_table() {
        $table_name = $this->table_prefix . 'surveys';

        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text,
            thank_you_page_slug varchar(255) DEFAULT NULL COMMENT 'Slug of private thank you page',
            created_by bigint(20) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'draft' COMMENT 'Survey status: draft, published, archived',
            show_header tinyint(1) DEFAULT 0 COMMENT 'Show custom header and subtitle on participant form',
            form_header varchar(255) DEFAULT '' COMMENT 'Custom header text for participant form',
            form_subtitle text COMMENT 'Custom subtitle text for participant form',
            settings longtext COMMENT 'Survey configuration and metadata',
            PRIMARY KEY (id),
            KEY created_by_idx (created_by),
            KEY status_idx (status),
            KEY created_at_idx (created_at)
        ) {$charset_collate};";

        $this->execute_sql($sql);
    }

    /**
     * Create wp_survey_participants table
     */
    private function create_participants_table() {
        $table_name = $this->table_prefix . 'participants';

        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(100) NOT NULL,
            survey_id bigint(20) NOT NULL,
            participant_name varchar(255) NOT NULL,
            participant_phone varchar(20) NOT NULL,
            participant_email varchar(255) NOT NULL,
            participant_address text DEFAULT NULL,
            participant_zip_code varchar(20) DEFAULT NULL,
            current_question_id bigint(20) DEFAULT NULL,
            question_chain longtext,
            completion_token varchar(255) DEFAULT NULL COMMENT 'Short-lived token for thank you page access',
            token_expires_at datetime DEFAULT NULL COMMENT 'Token expiration time',
            started_at datetime DEFAULT CURRENT_TIMESTAMP,
            completed_at datetime DEFAULT NULL,
            is_synced tinyint(1) DEFAULT 0 COMMENT 'Whether participant data is synced with external CRM',
            user_ip varchar(45),
            user_agent text,
            PRIMARY KEY (id),
            UNIQUE KEY session_id_idx (session_id),
            KEY survey_participant_idx (survey_id, participant_email),
            KEY survey_id_idx (survey_id),
            KEY completed_at_idx (completed_at),
            KEY started_at_idx (started_at),
            KEY completion_token_idx (completion_token)
        ) {$charset_collate};";

        $this->execute_sql($sql);
    }

    /**
     * Create wp_survey_questions table
     */
    private function create_questions_table() {
        $table_name = $this->table_prefix . 'questions';

        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            survey_id bigint(20) NOT NULL,
            title text NOT NULL,
            description text,
            extra_message text,
            type varchar(20) DEFAULT 'single_choice' COMMENT 'Question type: always single_choice',
            is_required tinyint(1) DEFAULT 1 COMMENT 'Whether this question is required (1) or optional (0)',
            skip_next_question_id bigint(20) DEFAULT NULL COMMENT 'Question ID to navigate to when question is skipped',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY survey_id_idx (survey_id),
            KEY skip_next_question_idx (skip_next_question_id)
        ) {$charset_collate};";

        $this->execute_sql($sql);
    }

    /**
     * Create wp_survey_answers table
     */
    private function create_answers_table() {
        $table_name = $this->table_prefix . 'answers';

        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            question_id bigint(20) NOT NULL,
            answer_text text NOT NULL,
            answer_value varchar(255),
            next_question_id bigint(20) DEFAULT NULL,
            redirect_url varchar(500) DEFAULT NULL,
            answer_order int DEFAULT 0,
            PRIMARY KEY (id),
            KEY question_id_idx (question_id),
            KEY answer_order_idx (question_id, answer_order),
            KEY next_question_idx (next_question_id)
        ) {$charset_collate};";

        $this->execute_sql($sql);
    }

    /**
     * Create wp_survey_responses table
     */
    private function create_responses_table() {
        $table_name = $this->table_prefix . 'responses';

        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            participant_id bigint(20) NOT NULL,
            survey_id bigint(20) NOT NULL,
            session_id varchar(100) NOT NULL,
            question_id bigint(20) NOT NULL,
            answer_id bigint(20) DEFAULT NULL,
            answer_text text DEFAULT NULL,
            is_skipped tinyint(1) DEFAULT 0 COMMENT 'Whether this question was skipped (1) or answered (0)',
            responded_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY participant_response_idx (participant_id, question_id),
            KEY session_survey_idx (session_id, survey_id),
            KEY survey_id_idx (survey_id),
            KEY question_id_idx (question_id),
            KEY answer_id_idx (answer_id),
            KEY responded_at_idx (responded_at),
            KEY is_skipped_idx (is_skipped)
        ) {$charset_collate};";

        $this->execute_sql($sql);
    }

    /**
     * Create wp_survey_templates table
     */
    private function create_templates_table() {
        $table_name = $this->table_prefix . 'templates';

        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            is_default tinyint(1) DEFAULT 0 COMMENT 'Whether this is a system default template',
            preview_image varchar(500),
            styles longtext COMMENT 'JSON configuration for template styles',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY is_default_idx (is_default),
            KEY created_at_idx (created_at)
        ) {$charset_collate};";

        $this->execute_sql($sql);
    }

    /**
     * Seed default templates
     */
    private function seed_default_templates() {
        $table_name = $this->table_prefix . 'templates';

        // Check if templates already exist
        $count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
        if ($count > 0) {
            return; // Templates already seeded
        }

        $default_templates = array(
            array(
                'name' => 'Classic',
                'description' => 'Traditional form-style survey with clean, professional design',
                'is_default' => 1,
                'preview_image' => WP_DYNAMIC_SURVEY_URL . 'assets/images/templates/classic.svg',
                'styles' => json_encode(array(
                    'primary_color' => '#0073aa',
                    'background_color' => '#ffffff',
                    'text_color' => '#1d2327',
                    'button_style' => 'solid',
                    'border_radius' => '4px',
                    'font_family' => 'system-ui, sans-serif'
                ))
            ),
            array(
                'name' => 'Modern',
                'description' => 'Clean, minimalist design with modern aesthetics',
                'is_default' => 1,
                'preview_image' => WP_DYNAMIC_SURVEY_URL . 'assets/images/templates/modern.svg',
                'styles' => json_encode(array(
                    'primary_color' => '#6366f1',
                    'background_color' => '#f8f9fa',
                    'text_color' => '#1e293b',
                    'button_style' => 'rounded',
                    'border_radius' => '12px',
                    'font_family' => 'Inter, system-ui, sans-serif'
                ))
            ),
            array(
                'name' => 'Card-based',
                'description' => 'Each question displayed as an elegant card',
                'is_default' => 1,
                'preview_image' => WP_DYNAMIC_SURVEY_URL . 'assets/images/templates/card.svg',
                'styles' => json_encode(array(
                    'primary_color' => '#10b981',
                    'background_color' => '#f3f4f6',
                    'text_color' => '#111827',
                    'button_style' => 'elevated',
                    'border_radius' => '16px',
                    'font_family' => 'system-ui, sans-serif',
                    'card_shadow' => '0 4px 6px rgba(0,0,0,0.1)'
                ))
            ),
            array(
                'name' => 'Dark Mode',
                'description' => 'Sleek dark theme for modern surveys',
                'is_default' => 1,
                'preview_image' => WP_DYNAMIC_SURVEY_URL . 'assets/images/templates/dark.svg',
                'styles' => json_encode(array(
                    'primary_color' => '#818cf8',
                    'background_color' => '#2d3748',
                    'text_color' => '#f1f5f9',
                    'button_style' => 'solid',
                    'border_radius' => '8px',
                    'font_family' => 'system-ui, sans-serif',
                    'input_bg_color' => '#1a202c',
                    'input_border_color' => '#4a5568',
                    'input_text_color' => '#f1f5f9'
                ))
            ),
            array(
                'name' => 'Colorful',
                'description' => 'Vibrant, engaging design with bold colors',
                'is_default' => 1,
                'preview_image' => WP_DYNAMIC_SURVEY_URL . 'assets/images/templates/colorful.svg',
                'styles' => json_encode(array(
                    'primary_color' => '#ec4899',
                    'background_color' => '#fef3c7',
                    'text_color' => '#1f2937',
                    'button_style' => 'gradient',
                    'border_radius' => '20px',
                    'font_family' => 'system-ui, sans-serif',
                    'gradient_start' => '#ec4899',
                    'gradient_end' => '#f59e0b'
                ))
            )
        );

        foreach ($default_templates as $template) {
            $this->wpdb->insert($table_name, $template);
        }

        // Set default active template to Classic (ID: 1)
        update_option('wp_dynamic_survey_active_template', 1);
    }


    /**
     * Execute SQL with error handling
     */
    private function execute_sql($sql) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $result = dbDelta($sql);

        // Log any errors
        if ($this->wpdb->last_error) {
            error_log('WP Dynamic Survey DB Error: ' . $this->wpdb->last_error);
            error_log('SQL: ' . $sql);
        }

        return $result;
    }

    /**
     * Check if tables exist
     */
    public function tables_exist() {
        $tables = $this->get_table_names();

        foreach ($tables as $table) {
            if ($this->wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all plugin table names
     */
    public function get_table_names() {
        return array(
            $this->table_prefix . 'surveys',
            $this->table_prefix . 'participants',
            $this->table_prefix . 'questions',
            $this->table_prefix . 'answers',
            $this->table_prefix . 'responses',
            $this->table_prefix . 'templates',
        );
    }

    /**
     * Drop all plugin tables (for uninstall)
     */
    public function drop_tables() {
        $tables = $this->get_table_names();

        foreach ($tables as $table) {
            $this->wpdb->query("DROP TABLE IF EXISTS {$table}");
        }

        // Remove database version option
        delete_option('wp_dynamic_survey_db_version');
    }

    /**
     * Check database version and run migrations if needed
     */
    public function check_version() {
        $current_version = get_option('wp_dynamic_survey_db_version', '0.0.0');

        if (version_compare($current_version, self::DB_VERSION, '<')) {
            $this->run_migrations($current_version);
        }
    }

    /**
     * Run database migrations based on version
     */
    private function run_migrations($from_version) {
        // Future migrations will be added here
        // Example:
        // if (version_compare($from_version, '1.1.0', '<')) {
        //     $this->migrate_to_1_1_0();
        // }

        // For now, just create tables if they don't exist
        if (!$this->tables_exist()) {
            $this->create_tables();
        }

        // Update to current version
        update_option('wp_dynamic_survey_db_version', self::DB_VERSION);
    }

    /**
     * Get database statistics
     */
    public function get_stats() {
        $stats = array();
        $tables = $this->get_table_names();

        foreach ($tables as $table) {
            $count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$table}");
            $table_name = str_replace($this->table_prefix, '', $table);
            $stats[$table_name] = intval($count);
        }

        return $stats;
    }

    /**
     * Optimize database tables
     */
    public function optimize_tables() {
        $tables = $this->get_table_names();

        foreach ($tables as $table) {
            $this->wpdb->query("OPTIMIZE TABLE {$table}");
        }
    }
}