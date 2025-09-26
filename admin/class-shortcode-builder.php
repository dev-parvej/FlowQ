<?php
/**
 * Shortcode Builder for WP Dynamic Survey Plugin
 * Admin interface for generating shortcodes
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode Builder class
 */
class WP_Dynamic_Survey_Shortcode_Builder {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 15);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_wp_dynamic_survey_preview_shortcode', array($this, 'ajax_preview_shortcode'));
        add_action('media_buttons', array($this, 'add_media_button'));
        add_action('admin_footer', array($this, 'shortcode_modal'));
    }

    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            'wp-dynamic-survey',
            __('Shortcode Builder', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
            __('Shortcode Builder', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
            'manage_wp_dynamic_surveys',
            'wp-dynamic-survey-shortcodes',
            array($this, 'render_builder_page')
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook) {
        if (strpos($hook, 'wp-dynamic-survey') === false && !in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }

        wp_enqueue_style(
            'wp-dynamic-survey-shortcode-builder',
            WP_DYNAMIC_SURVEY_URL . 'assets/css/shortcode-builder.css',
            array(),
            WP_DYNAMIC_SURVEY_VERSION
        );

        wp_enqueue_script(
            'wp-dynamic-survey-shortcode-builder',
            WP_DYNAMIC_SURVEY_URL . 'assets/js/shortcode-builder.js',
            array('jquery', 'wp-util'),
            WP_DYNAMIC_SURVEY_VERSION,
            true
        );

        wp_localize_script('wp-dynamic-survey-shortcode-builder', 'wpDynamicSurveyShortcode', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_dynamic_survey_shortcode_nonce'),
            'strings' => array(
                'preview' => __('Preview', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'loading' => __('Loading preview...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'error' => __('Preview failed to load.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'copied' => __('Shortcode copied to clipboard!', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                'insert' => __('Insert Shortcode', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)
            )
        ));
    }

    /**
     * Render shortcode builder page
     */
    public function render_builder_page() {
        $survey_manager = new WP_Dynamic_Survey_Manager();
        $surveys = $survey_manager->get_surveys(array('status' => 'published'));
        $themes = WP_Dynamic_Survey_Shortcode::get_available_themes();
        $shortcode_docs = WP_Dynamic_Survey_Shortcode::get_shortcode_docs();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Shortcode Builder', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h1>

            <div class="shortcode-builder-container">
                <div class="shortcode-builder-form">
                    <div class="shortcode-type-tabs">
                        <button type="button" class="tab-button active" data-tab="survey">
                            <?php echo esc_html__('Survey Display', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </button>
                        <button type="button" class="tab-button" data-tab="list">
                            <?php echo esc_html__('Survey List', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </button>
                        <button type="button" class="tab-button" data-tab="stats">
                            <?php echo esc_html__('Statistics', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </button>
                        <button type="button" class="tab-button" data-tab="button">
                            <?php echo esc_html__('Button/Link', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </button>
                        <button type="button" class="tab-button" data-tab="embed">
                            <?php echo esc_html__('Embed', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </button>
                    </div>

                    <!-- Survey Display Tab -->
                    <div class="tab-content active" id="tab-survey">
                        <h3><?php echo esc_html__('Survey Display Options', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>

                        <table class="form-table">
                            <tr>
                                <th><label for="survey-id"><?php echo esc_html__('Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <select id="survey-id" name="survey_id" required>
                                        <option value=""><?php echo esc_html__('Select a survey...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <?php foreach ($surveys as $survey): ?>
                                        <option value="<?php echo esc_attr($survey['id']); ?>">
                                            <?php echo esc_html($survey['title']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="survey-theme"><?php echo esc_html__('Theme', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <select id="survey-theme" name="theme">
                                        <?php foreach ($themes as $key => $name): ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="survey-width"><?php echo esc_html__('Width', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <input type="text" id="survey-width" name="width" value="100%" placeholder="100%, 500px, etc.">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="survey-height"><?php echo esc_html__('Height', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <input type="text" id="survey-height" name="height" value="auto" placeholder="auto, 600px, etc.">
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Display Options', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="show-title" name="show_title" checked>
                                        <?php echo esc_html__('Show Title', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" id="show-description" name="show_description" checked>
                                        <?php echo esc_html__('Show Description', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" id="show-progress" name="show_progress" checked>
                                        <?php echo esc_html__('Show Progress Bar', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" id="auto-start" name="auto_start">
                                        <?php echo esc_html__('Auto-scroll to Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" id="enable-print" name="enable_print">
                                        <?php echo esc_html__('Enable Print Option', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="survey-css-class"><?php echo esc_html__('CSS Class', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <input type="text" id="survey-css-class" name="css_class" placeholder="my-custom-class">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Survey List Tab -->
                    <div class="tab-content" id="tab-list">
                        <h3><?php echo esc_html__('Survey List Options', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>

                        <table class="form-table">
                            <tr>
                                <th><label for="list-status"><?php echo esc_html__('Status Filter', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <select id="list-status" name="status">
                                        <option value="published"><?php echo esc_html__('Published', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <option value="draft"><?php echo esc_html__('Draft', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <option value="all"><?php echo esc_html__('All', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="list-limit"><?php echo esc_html__('Number to Show', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <input type="number" id="list-limit" name="limit" value="10" min="1" max="50">
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Display Options', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="list-show-description" name="show_description" checked>
                                        <?php echo esc_html__('Show Descriptions', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" id="list-show-stats" name="show_stats">
                                        <?php echo esc_html__('Show Statistics', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Statistics Tab -->
                    <div class="tab-content" id="tab-stats">
                        <h3><?php echo esc_html__('Statistics Display Options', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>

                        <table class="form-table">
                            <tr>
                                <th><label for="stats-survey-id"><?php echo esc_html__('Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <select id="stats-survey-id" name="survey_id" required>
                                        <option value=""><?php echo esc_html__('Select a survey...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <?php foreach ($surveys as $survey): ?>
                                        <option value="<?php echo esc_attr($survey['id']); ?>">
                                            <?php echo esc_html($survey['title']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="stats-show"><?php echo esc_html__('Show Statistics', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <select id="stats-show" name="show">
                                        <option value="all"><?php echo esc_html__('All Statistics', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <option value="participants"><?php echo esc_html__('Participants Only', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <option value="completion"><?php echo esc_html__('Completion Rate Only', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <option value="average_time"><?php echo esc_html__('Average Time Only', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="stats-format"><?php echo esc_html__('Display Format', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <select id="stats-format" name="format">
                                        <option value="inline"><?php echo esc_html__('Inline', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <option value="table"><?php echo esc_html__('Table', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <option value="cards"><?php echo esc_html__('Cards', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Button Tab -->
                    <div class="tab-content" id="tab-button">
                        <h3><?php echo esc_html__('Button/Link Options', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>

                        <table class="form-table">
                            <tr>
                                <th><label for="button-survey-id"><?php echo esc_html__('Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <select id="button-survey-id" name="survey_id" required>
                                        <option value=""><?php echo esc_html__('Select a survey...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <?php foreach ($surveys as $survey): ?>
                                        <option value="<?php echo esc_attr($survey['id']); ?>">
                                            <?php echo esc_html($survey['title']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="button-text"><?php echo esc_html__('Button Text', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <input type="text" id="button-text" name="text" value="Take Survey">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="button-style"><?php echo esc_html__('Style', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <select id="button-style" name="style">
                                        <option value="button"><?php echo esc_html__('Button', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <option value="link"><?php echo esc_html__('Link', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="button-size"><?php echo esc_html__('Size', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <select id="button-size" name="size">
                                        <option value="small"><?php echo esc_html__('Small', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <option value="medium" selected><?php echo esc_html__('Medium', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <option value="large"><?php echo esc_html__('Large', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="button-color"><?php echo esc_html__('Color', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <select id="button-color" name="color">
                                        <option value="primary" selected><?php echo esc_html__('Primary', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <option value="secondary"><?php echo esc_html__('Secondary', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <option value="success"><?php echo esc_html__('Success', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <option value="warning"><?php echo esc_html__('Warning', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <option value="danger"><?php echo esc_html__('Danger', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Options', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="button-new-window" name="new_window">
                                        <?php echo esc_html__('Open in New Window', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Embed Tab -->
                    <div class="tab-content" id="tab-embed">
                        <h3><?php echo esc_html__('Embed Options', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>

                        <table class="form-table">
                            <tr>
                                <th><label for="embed-survey-id"><?php echo esc_html__('Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <select id="embed-survey-id" name="survey_id" required>
                                        <option value=""><?php echo esc_html__('Select a survey...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <?php foreach ($surveys as $survey): ?>
                                        <option value="<?php echo esc_attr($survey['id']); ?>">
                                            <?php echo esc_html($survey['title']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="embed-width"><?php echo esc_html__('Width', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <input type="text" id="embed-width" name="width" value="100%">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="embed-height"><?php echo esc_html__('Height', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label></th>
                                <td>
                                    <input type="text" id="embed-height" name="height" value="600px">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="shortcode-actions">
                        <button type="button" class="button button-primary" id="generate-shortcode">
                            <?php echo esc_html__('Generate Shortcode', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </button>
                        <button type="button" class="button" id="preview-shortcode">
                            <?php echo esc_html__('Preview', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </button>
                    </div>
                </div>

                <div class="shortcode-output">
                    <h3><?php echo esc_html__('Generated Shortcode', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>
                    <div class="shortcode-result">
                        <textarea id="shortcode-output" readonly></textarea>
                        <button type="button" class="button" id="copy-shortcode" disabled>
                            <?php echo esc_html__('Copy to Clipboard', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </button>
                    </div>

                    <div class="shortcode-preview">
                        <h3><?php echo esc_html__('Preview', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>
                        <div id="preview-container">
                            <p class="description"><?php echo esc_html__('Click "Preview" to see how your shortcode will look.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documentation Section -->
            <div class="shortcode-documentation">
                <h2><?php echo esc_html__('Shortcode Documentation', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>

                <?php foreach ($shortcode_docs as $shortcode => $doc): ?>
                <div class="shortcode-doc-item">
                    <h3><code>[<?php echo esc_html($shortcode); ?>]</code></h3>
                    <p><?php echo esc_html($doc['description']); ?></p>

                    <h4><?php echo esc_html__('Attributes:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h4>
                    <ul>
                        <?php foreach ($doc['attributes'] as $attr => $desc): ?>
                        <li><strong><?php echo esc_html($attr); ?></strong>: <?php echo esc_html($desc); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <h4><?php echo esc_html__('Example:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h4>
                    <code><?php echo esc_html($doc['example']); ?></code>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <style>
        .shortcode-builder-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .shortcode-type-tabs {
            display: flex;
            border-bottom: 1px solid #ccd0d4;
            margin-bottom: 20px;
        }

        .tab-button {
            background: none;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .tab-button.active {
            border-bottom-color: #2271b1;
            color: #2271b1;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .shortcode-actions {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ccd0d4;
        }

        .shortcode-result {
            position: relative;
        }

        #shortcode-output {
            width: 100%;
            height: 100px;
            font-family: monospace;
            resize: vertical;
            margin-bottom: 10px;
        }

        .shortcode-preview {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ccd0d4;
        }

        #preview-container {
            border: 1px solid #ccd0d4;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 4px;
            min-height: 200px;
        }

        .shortcode-documentation {
            margin-top: 40px;
            padding-top: 40px;
            border-top: 1px solid #ccd0d4;
        }

        .shortcode-doc-item {
            background: #f9f9f9;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .shortcode-doc-item h3 {
            margin-top: 0;
            color: #2271b1;
        }

        .shortcode-doc-item code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 3px;
            border: 1px solid #ddd;
        }

        @media (max-width: 1200px) {
            .shortcode-builder-container {
                grid-template-columns: 1fr;
            }
        }
        </style>
        <?php
    }

    /**
     * Add media button
     */
    public function add_media_button() {
        echo '<button type="button" class="button" id="wp-dynamic-survey-shortcode-button">
                <span class="dashicons dashicons-feedback" style="margin-top: 3px;"></span>
                ' . esc_html__('Survey Shortcode', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) . '
              </button>';
    }

    /**
     * Shortcode modal
     */
    public function shortcode_modal() {
        global $pagenow;
        if (!in_array($pagenow, array('post.php', 'post-new.php'))) {
            return;
        }

        $survey_manager = new WP_Dynamic_Survey_Manager();
        $surveys = $survey_manager->get_surveys(array('status' => 'published'));
        ?>
        <div id="wp-dynamic-survey-shortcode-modal" class="wp-dynamic-survey-modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><?php echo esc_html__('Insert Survey Shortcode', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>
                    <button type="button" class="modal-close">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="modal-survey-select"><?php echo esc_html__('Select Survey:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                        <select id="modal-survey-select" class="widefat">
                            <option value=""><?php echo esc_html__('Choose a survey...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                            <?php foreach ($surveys as $survey): ?>
                            <option value="<?php echo esc_attr($survey['id']); ?>">
                                <?php echo esc_html($survey['title']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal-theme-select"><?php echo esc_html__('Theme:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                        <select id="modal-theme-select" class="widefat">
                            <option value="default"><?php echo esc_html__('Default', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                            <option value="minimal"><?php echo esc_html__('Minimal', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                            <option value="modern"><?php echo esc_html__('Modern', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                            <option value="card"><?php echo esc_html__('Card', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="modal-show-title" checked>
                            <?php echo esc_html__('Show Title', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="modal-show-progress" checked>
                            <?php echo esc_html__('Show Progress Bar', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="button button-primary" id="insert-shortcode-btn">
                        <?php echo esc_html__('Insert Shortcode', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </button>
                    <button type="button" class="button modal-cancel">
                        <?php echo esc_html__('Cancel', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </button>
                </div>
            </div>
        </div>

        <style>
        .wp-dynamic-survey-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 4px;
            width: 90%;
            max-width: 500px;
            max-height: 90%;
            overflow: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ccd0d4;
        }

        .modal-header h2 {
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #ccd0d4;
            text-align: right;
        }

        .modal-footer .button {
            margin-left: 10px;
        }
        </style>
        <?php
    }

    /**
     * AJAX preview shortcode
     */
    public function ajax_preview_shortcode() {
        check_ajax_referer('wp_dynamic_survey_shortcode_nonce', 'nonce');

        $shortcode = sanitize_text_field($_POST['shortcode'] ?? '');

        if (empty($shortcode)) {
            wp_send_json_error(__('No shortcode provided.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN));
        }

        // Process the shortcode
        $output = do_shortcode($shortcode);

        wp_send_json_success(array(
            'html' => $output
        ));
    }
}