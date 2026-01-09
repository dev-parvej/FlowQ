<?php
/**
 * Shortcode Builder for WP Dynamic Survey Plugin
 * Admin interface for generating shortcodes
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode Builder class
 */
class FlowQ_Shortcode_Builder {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 15);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_flowq_preview_shortcode', array($this, 'ajax_preview_shortcode'));
        add_action('media_buttons', array($this, 'add_media_button'));
        add_action('admin_footer', array($this, 'shortcode_modal'));
    }

    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            'flowq',
            __('Shortcode Builder', 'flowq'),
            __('Shortcode Builder', 'flowq'),
            'manage_flowq_surveys',
            'flowq-shortcodes',
            array($this, 'render_builder_page')
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook) {
        if (strpos($hook, 'flowq') === false && !in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }

        wp_enqueue_style(
            'flowq-shortcode-builder',
            FLOWQ_URL . 'assets/css/shortcode-builder.css',
            array(),
            FLOWQ_VERSION
        );

        wp_enqueue_script(
            'flowq-shortcode-builder',
            FLOWQ_URL . 'assets/js/shortcode-builder.js',
            array('jquery', 'wp-util'),
            FLOWQ_VERSION,
            true
        );

        wp_localize_script('flowq-shortcode-builder', 'flowqShortcode', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('flowq_shortcode_nonce'),
            'strings' => array(
                'preview' => __('Preview', 'flowq'),
                'loading' => __('Loading preview...', 'flowq'),
                'error' => __('Preview failed to load.', 'flowq'),
                'copied' => __('Shortcode copied to clipboard!', 'flowq'),
                'insert' => __('Insert Shortcode', 'flowq')
            )
        ));
    }

    /**
     * Render shortcode builder page
     */
    public function render_builder_page() {
        $survey_manager = new FlowQ_Survey_Manager();
        $surveys = $survey_manager->get_surveys(array('status' => 'published'));
        $themes = FlowQ_Shortcode::get_available_themes();
        $shortcode_docs = FlowQ_Shortcode::get_shortcode_docs();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Shortcode Builder', 'flowq'); ?></h1>

            <div class="shortcode-builder-container">
                <div class="shortcode-builder-form">
                    <div class="shortcode-type-tabs">
                        <button type="button" class="tab-button active" data-tab="survey">
                            <?php echo esc_html__('Survey Display', 'flowq'); ?>
                        </button>
                        <button type="button" class="tab-button" data-tab="list">
                            <?php echo esc_html__('Survey List', 'flowq'); ?>
                        </button>
                        <button type="button" class="tab-button" data-tab="stats">
                            <?php echo esc_html__('Statistics', 'flowq'); ?>
                        </button>
                        <button type="button" class="tab-button" data-tab="button">
                            <?php echo esc_html__('Button/Link', 'flowq'); ?>
                        </button>
                        <button type="button" class="tab-button" data-tab="embed">
                            <?php echo esc_html__('Embed', 'flowq'); ?>
                        </button>
                    </div>

                    <!-- Survey Display Tab -->
                    <div class="tab-content active" id="tab-survey">
                        <h3><?php echo esc_html__('Survey Display Options', 'flowq'); ?></h3>

                        <table class="form-table">
                            <tr>
                                <th><label for="survey-id"><?php echo esc_html__('Survey', 'flowq'); ?></label></th>
                                <td>
                                    <select id="survey-id" name="survey_id" required>
                                        <option value=""><?php echo esc_html__('Select a survey...', 'flowq'); ?></option>
                                        <?php foreach ($surveys as $survey): ?>
                                        <option value="<?php echo esc_attr($survey['id']); ?>">
                                            <?php echo esc_html($survey['title']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="survey-theme"><?php echo esc_html__('Theme', 'flowq'); ?></label></th>
                                <td>
                                    <select id="survey-theme" name="theme">
                                        <?php foreach ($themes as $key => $name): ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="survey-width"><?php echo esc_html__('Width', 'flowq'); ?></label></th>
                                <td>
                                    <input type="text" id="survey-width" name="width" value="100%" placeholder="100%, 500px, etc.">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="survey-height"><?php echo esc_html__('Height', 'flowq'); ?></label></th>
                                <td>
                                    <input type="text" id="survey-height" name="height" value="auto" placeholder="auto, 600px, etc.">
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Display Options', 'flowq'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="show-title" name="show_title" checked>
                                        <?php echo esc_html__('Show Title', 'flowq'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" id="show-description" name="show_description" checked>
                                        <?php echo esc_html__('Show Description', 'flowq'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" id="show-progress" name="show_progress" checked>
                                        <?php echo esc_html__('Show Progress Bar', 'flowq'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" id="auto-start" name="auto_start">
                                        <?php echo esc_html__('Auto-scroll to Survey', 'flowq'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" id="enable-print" name="enable_print">
                                        <?php echo esc_html__('Enable Print Option', 'flowq'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="survey-css-class"><?php echo esc_html__('CSS Class', 'flowq'); ?></label></th>
                                <td>
                                    <input type="text" id="survey-css-class" name="css_class" placeholder="my-custom-class">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Survey List Tab -->
                    <div class="tab-content" id="tab-list">
                        <h3><?php echo esc_html__('Survey List Options', 'flowq'); ?></h3>

                        <table class="form-table">
                            <tr>
                                <th><label for="list-status"><?php echo esc_html__('Status Filter', 'flowq'); ?></label></th>
                                <td>
                                    <select id="list-status" name="status">
                                        <option value="published"><?php echo esc_html__('Published', 'flowq'); ?></option>
                                        <option value="draft"><?php echo esc_html__('Draft', 'flowq'); ?></option>
                                        <option value="all"><?php echo esc_html__('All', 'flowq'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="list-limit"><?php echo esc_html__('Number to Show', 'flowq'); ?></label></th>
                                <td>
                                    <input type="number" id="list-limit" name="limit" value="10" min="1" max="50">
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Display Options', 'flowq'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="list-show-description" name="show_description" checked>
                                        <?php echo esc_html__('Show Descriptions', 'flowq'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" id="list-show-stats" name="show_stats">
                                        <?php echo esc_html__('Show Statistics', 'flowq'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Statistics Tab -->
                    <div class="tab-content" id="tab-stats">
                        <h3><?php echo esc_html__('Statistics Display Options', 'flowq'); ?></h3>

                        <table class="form-table">
                            <tr>
                                <th><label for="stats-survey-id"><?php echo esc_html__('Survey', 'flowq'); ?></label></th>
                                <td>
                                    <select id="stats-survey-id" name="survey_id" required>
                                        <option value=""><?php echo esc_html__('Select a survey...', 'flowq'); ?></option>
                                        <?php foreach ($surveys as $survey): ?>
                                        <option value="<?php echo esc_attr($survey['id']); ?>">
                                            <?php echo esc_html($survey['title']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="stats-show"><?php echo esc_html__('Show Statistics', 'flowq'); ?></label></th>
                                <td>
                                    <select id="stats-show" name="show">
                                        <option value="all"><?php echo esc_html__('All Statistics', 'flowq'); ?></option>
                                        <option value="participants"><?php echo esc_html__('Participants Only', 'flowq'); ?></option>
                                        <option value="completion"><?php echo esc_html__('Completion Rate Only', 'flowq'); ?></option>
                                        <option value="average_time"><?php echo esc_html__('Average Time Only', 'flowq'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="stats-format"><?php echo esc_html__('Display Format', 'flowq'); ?></label></th>
                                <td>
                                    <select id="stats-format" name="format">
                                        <option value="inline"><?php echo esc_html__('Inline', 'flowq'); ?></option>
                                        <option value="table"><?php echo esc_html__('Table', 'flowq'); ?></option>
                                        <option value="cards"><?php echo esc_html__('Cards', 'flowq'); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Button Tab -->
                    <div class="tab-content" id="tab-button">
                        <h3><?php echo esc_html__('Button/Link Options', 'flowq'); ?></h3>

                        <table class="form-table">
                            <tr>
                                <th><label for="button-survey-id"><?php echo esc_html__('Survey', 'flowq'); ?></label></th>
                                <td>
                                    <select id="button-survey-id" name="survey_id" required>
                                        <option value=""><?php echo esc_html__('Select a survey...', 'flowq'); ?></option>
                                        <?php foreach ($surveys as $survey): ?>
                                        <option value="<?php echo esc_attr($survey['id']); ?>">
                                            <?php echo esc_html($survey['title']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="button-text"><?php echo esc_html__('Button Text', 'flowq'); ?></label></th>
                                <td>
                                    <input type="text" id="button-text" name="text" value="Take Survey">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="button-style"><?php echo esc_html__('Style', 'flowq'); ?></label></th>
                                <td>
                                    <select id="button-style" name="style">
                                        <option value="button"><?php echo esc_html__('Button', 'flowq'); ?></option>
                                        <option value="link"><?php echo esc_html__('Link', 'flowq'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="button-size"><?php echo esc_html__('Size', 'flowq'); ?></label></th>
                                <td>
                                    <select id="button-size" name="size">
                                        <option value="small"><?php echo esc_html__('Small', 'flowq'); ?></option>
                                        <option value="medium" selected><?php echo esc_html__('Medium', 'flowq'); ?></option>
                                        <option value="large"><?php echo esc_html__('Large', 'flowq'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="button-color"><?php echo esc_html__('Color', 'flowq'); ?></label></th>
                                <td>
                                    <select id="button-color" name="color">
                                        <option value="primary" selected><?php echo esc_html__('Primary', 'flowq'); ?></option>
                                        <option value="secondary"><?php echo esc_html__('Secondary', 'flowq'); ?></option>
                                        <option value="success"><?php echo esc_html__('Success', 'flowq'); ?></option>
                                        <option value="warning"><?php echo esc_html__('Warning', 'flowq'); ?></option>
                                        <option value="danger"><?php echo esc_html__('Danger', 'flowq'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Options', 'flowq'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="button-new-window" name="new_window">
                                        <?php echo esc_html__('Open in New Window', 'flowq'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Embed Tab -->
                    <div class="tab-content" id="tab-embed">
                        <h3><?php echo esc_html__('Embed Options', 'flowq'); ?></h3>

                        <table class="form-table">
                            <tr>
                                <th><label for="embed-survey-id"><?php echo esc_html__('Survey', 'flowq'); ?></label></th>
                                <td>
                                    <select id="embed-survey-id" name="survey_id" required>
                                        <option value=""><?php echo esc_html__('Select a survey...', 'flowq'); ?></option>
                                        <?php foreach ($surveys as $survey): ?>
                                        <option value="<?php echo esc_attr($survey['id']); ?>">
                                            <?php echo esc_html($survey['title']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="embed-width"><?php echo esc_html__('Width', 'flowq'); ?></label></th>
                                <td>
                                    <input type="text" id="embed-width" name="width" value="100%">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="embed-height"><?php echo esc_html__('Height', 'flowq'); ?></label></th>
                                <td>
                                    <input type="text" id="embed-height" name="height" value="600px">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="shortcode-actions">
                        <button type="button" class="button button-primary" id="generate-shortcode">
                            <?php echo esc_html__('Generate Shortcode', 'flowq'); ?>
                        </button>
                        <button type="button" class="button" id="preview-shortcode">
                            <?php echo esc_html__('Preview', 'flowq'); ?>
                        </button>
                    </div>
                </div>

                <div class="shortcode-output">
                    <h3><?php echo esc_html__('Generated Shortcode', 'flowq'); ?></h3>
                    <div class="shortcode-result">
                        <textarea id="shortcode-output" readonly></textarea>
                        <button type="button" class="button" id="copy-shortcode" disabled>
                            <?php echo esc_html__('Copy to Clipboard', 'flowq'); ?>
                        </button>
                    </div>

                    <div class="shortcode-preview">
                        <h3><?php echo esc_html__('Preview', 'flowq'); ?></h3>
                        <div id="preview-container">
                            <p class="description"><?php echo esc_html__('Click "Preview" to see how your shortcode will look.', 'flowq'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documentation Section -->
            <div class="shortcode-documentation">
                <h2><?php echo esc_html__('Shortcode Documentation', 'flowq'); ?></h2>

                <?php foreach ($shortcode_docs as $shortcode => $doc): ?>
                <div class="shortcode-doc-item">
                    <h3><code>[<?php echo esc_html($shortcode); ?>]</code></h3>
                    <p><?php echo esc_html($doc['description']); ?></p>

                    <h4><?php echo esc_html__('Attributes:', 'flowq'); ?></h4>
                    <ul>
                        <?php foreach ($doc['attributes'] as $attr => $desc): ?>
                        <li><strong><?php echo esc_html($attr); ?></strong>: <?php echo esc_html($desc); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <h4><?php echo esc_html__('Example:', 'flowq'); ?></h4>
                    <code><?php echo esc_html($doc['example']); ?></code>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
    }

    /**
     * Add media button
     */
    public function add_media_button() {
        echo '<button type="button" class="button" id="flowq-shortcode-button">
                <span class="dashicons dashicons-feedback" style="margin-top: 3px;"></span>
                ' . esc_html__('Survey Shortcode', 'flowq') . '
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

        $survey_manager = new FlowQ_Survey_Manager();
        $surveys = $survey_manager->get_surveys(array('status' => 'published'));
        ?>
        <div id="flowq-shortcode-modal" class="flowq-modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><?php echo esc_html__('Insert Survey Shortcode', 'flowq'); ?></h2>
                    <button type="button" class="modal-close">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="modal-survey-select"><?php echo esc_html__('Select Survey:', 'flowq'); ?></label>
                        <select id="modal-survey-select" class="widefat">
                            <option value=""><?php echo esc_html__('Choose a survey...', 'flowq'); ?></option>
                            <?php foreach ($surveys as $survey): ?>
                            <option value="<?php echo esc_attr($survey['id']); ?>">
                                <?php echo esc_html($survey['title']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal-theme-select"><?php echo esc_html__('Theme:', 'flowq'); ?></label>
                        <select id="modal-theme-select" class="widefat">
                            <option value="default"><?php echo esc_html__('Default', 'flowq'); ?></option>
                            <option value="minimal"><?php echo esc_html__('Minimal', 'flowq'); ?></option>
                            <option value="modern"><?php echo esc_html__('Modern', 'flowq'); ?></option>
                            <option value="card"><?php echo esc_html__('Card', 'flowq'); ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="modal-show-title" checked>
                            <?php echo esc_html__('Show Title', 'flowq'); ?>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="modal-show-progress" checked>
                            <?php echo esc_html__('Show Progress Bar', 'flowq'); ?>
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="button button-primary" id="insert-shortcode-btn">
                        <?php echo esc_html__('Insert Shortcode', 'flowq'); ?>
                    </button>
                    <button type="button" class="button modal-cancel">
                        <?php echo esc_html__('Cancel', 'flowq'); ?>
                    </button>
                </div>
            </div>
        </div>

        <?php
    }

    /**
     * AJAX preview shortcode
     */
    public function ajax_preview_shortcode() {
        check_ajax_referer('flowq_shortcode_nonce', 'nonce');

        $shortcode = isset($_POST['shortcode']) ? sanitize_text_field(wp_unslash($_POST['shortcode'])) : '';

        if (empty($shortcode)) {
            wp_send_json_error(__('No shortcode provided.', 'flowq'));
        }

        // Process the shortcode
        $output = do_shortcode($shortcode);

        wp_send_json_success(array(
            'html' => $output
        ));
    }
}