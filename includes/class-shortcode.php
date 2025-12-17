<?php
/**
 * Enhanced Shortcode Handler for WP Dynamic Survey Plugin
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode Handler class
 */
class FlowQ_Shortcode {

    /**
     * Available themes
     */
    private static $available_themes = array(
        'default' => 'Default Theme',
        'minimal' => 'Minimal Theme',
        'modern' => 'Modern Theme',
        'card' => 'Card Theme',
        'full-width' => 'Full Width Theme'
    );

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'maybe_enqueue_assets'));
        add_filter('widget_text', 'do_shortcode');
        add_filter('the_excerpt', 'do_shortcode');
    }

    /**
     * Register all shortcodes
     */
    public function register_shortcodes() {
        // Primary shortcodes with proper prefix
        add_shortcode('flowq_survey', array($this, 'render_survey'));
        add_shortcode('flowq_survey_list', array($this, 'render_survey_list'));
        add_shortcode('flowq_survey_stats', array($this, 'render_survey_stats'));
        add_shortcode('flowq_survey_button', array($this, 'render_survey_button'));
        add_shortcode('flowq_survey_embed', array($this, 'render_survey_embed'));
    }

    /**
     * Main survey shortcode
     */
    public function render_survey($atts, $content = null) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'theme' => 'default',
            'width' => '100%',
            'height' => 'auto',
            'show_title' => 'true',
            'show_description' => 'true',
            'show_progress' => 'true',
            'auto_start' => 'false',
            'redirect_url' => '',
            'css_class' => '',
            'container_id' => '',
            'disable_scroll' => 'false',
            'enable_print' => 'false',
            'custom_css' => '',
            'lang' => '',
            'track_analytics' => 'true'
        ), $atts, 'flowq_survey');

        // Validate and sanitize attributes
        $survey_id = intval($atts['id']);
        $theme = sanitize_text_field($atts['theme']);
        $width = sanitize_text_field($atts['width']);
        $height = sanitize_text_field($atts['height']);
        $show_title = filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN);
        $show_description = filter_var($atts['show_description'], FILTER_VALIDATE_BOOLEAN);
        $show_progress = filter_var($atts['show_progress'], FILTER_VALIDATE_BOOLEAN);
        $auto_start = filter_var($atts['auto_start'], FILTER_VALIDATE_BOOLEAN);
        $redirect_url = esc_url($atts['redirect_url']);
        $css_class = sanitize_html_class($atts['css_class']);
        $container_id = sanitize_html_class($atts['container_id']);
        $disable_scroll = filter_var($atts['disable_scroll'], FILTER_VALIDATE_BOOLEAN);
        $enable_print = filter_var($atts['enable_print'], FILTER_VALIDATE_BOOLEAN);
        $custom_css = wp_strip_all_tags($atts['custom_css']);
        $lang = sanitize_text_field($atts['lang']);
        $track_analytics = filter_var($atts['track_analytics'], FILTER_VALIDATE_BOOLEAN);

        if (!$survey_id) {
            return $this->render_error(__('Survey ID is required.', 'flowq'));
        }

        // Validate survey exists and is published
        $survey_manager = new FlowQ_Survey_Manager();
        $survey = $survey_manager->get_survey($survey_id, true);

        if (!$survey) {
            return $this->render_error(__('Survey not found.', 'flowq'));
        }

        if ($survey['status'] !== 'published') {
            // Show message for admins, nothing for regular users
            if (current_user_can('manage_flowq_surveys')) {
                return $this->render_error(__('Survey is not published. Only administrators can see this message.', 'flowq'));
            }
            return '';
        }

        // Validate theme
        if (!array_key_exists($theme, self::$available_themes)) {
            $theme = 'default';
        }

        // Generate unique container ID if not provided
        if (empty($container_id)) {
            $container_id = 'flowq-' . $survey_id . '-' . uniqid();
        }

        // Build container classes
        $container_classes = array(
            'flowq-container',
            'survey-theme-' . $theme,
            'survey-id-' . $survey_id
        );

        if (!empty($css_class)) {
            $container_classes[] = $css_class;
        }

        if ($disable_scroll) {
            $container_classes[] = 'disable-scroll';
        }

        if ($enable_print) {
            $container_classes[] = 'print-enabled';
        }

        // Build container styles
        $container_styles = array();
        if ($width !== '100%') {
            $container_styles[] = 'width: ' . esc_attr($width);
        }
        if ($height !== 'auto') {
            $container_styles[] = 'height: ' . esc_attr($height);
        }

        // Build configuration for JavaScript
        $config = array(
            'surveyId' => $survey_id,
            'theme' => $theme,
            'containerId' => $container_id,
            'showTitle' => $show_title,
            'showDescription' => $show_description,
            'showProgress' => $show_progress,
            'autoStart' => $auto_start,
            'redirectUrl' => $redirect_url,
            'disableScroll' => $disable_scroll,
            'enablePrint' => $enable_print,
            'trackAnalytics' => $track_analytics,
            'lang' => $lang
        );

        // Enqueue assets
        $this->enqueue_survey_assets($theme);

        // Start output buffering
        ob_start();
        ?>

        <div id="<?php echo esc_attr($container_id); ?>"
             class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
             <?php if (!empty($container_styles)): ?>
             style="<?php echo esc_attr(implode('; ', $container_styles)); ?>"
             <?php endif; ?>
             data-survey-config="<?php echo esc_attr(wp_json_encode($config)); ?>">
            <div class="survey-content">
                <?php
                // Include the actual survey interface
                $frontend = new FlowQ_Frontend();
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_survey() handles escaping internally
                echo $frontend->render_survey($survey_id, $theme);
                ?>
            </div>

            <?php if ($enable_print): ?>
            <div class="survey-print-options">
                <button type="button" class="survey-print-btn" onclick="window.print()">
                    <?php echo esc_html__('Print Survey', 'flowq'); ?>
                </button>
            </div>
            <?php endif; ?>

        </div>

        <?php
        // Add inline custom CSS using WordPress function
        if (!empty($custom_css)) {
            // Sanitize container ID for use in CSS selector (alphanumeric and hyphens only)
            $safe_container_id = preg_replace('/[^a-zA-Z0-9\-_]/', '', $container_id);
            $inline_css = '#' . esc_attr($safe_container_id) . ' {' . wp_strip_all_tags($custom_css) . '}';
            wp_add_inline_style('flowq-shortcode', $inline_css);
        }

        // Add inline initialization script using WordPress function
        $inline_script = "
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('" . esc_js($container_id) . "');
            const config = JSON.parse(container && container.dataset.surveyConfig || '{}');

            if (typeof FlowQFrontend !== 'undefined') {
                FlowQFrontend.init(config);
            }
        });
        ";
        wp_add_inline_script('flowq-frontend-enhanced', $inline_script);
        ?>

        <?php
        return ob_get_clean();
    }

    /**
     * Survey list shortcode
     */
    public function render_survey_list($atts) {
        $atts = shortcode_atts(array(
            'status' => 'published',
            'limit' => 10,
            'show_description' => 'true',
            'show_stats' => 'false',
            'order' => 'date',
            'order_dir' => 'DESC',
            'template' => 'list'
        ), $atts, 'survey_list');

        $status = sanitize_text_field($atts['status']);
        $limit = intval($atts['limit']);
        $show_description = filter_var($atts['show_description'], FILTER_VALIDATE_BOOLEAN);
        $show_stats = filter_var($atts['show_stats'], FILTER_VALIDATE_BOOLEAN);
        $order = sanitize_text_field($atts['order']);
        $order_dir = sanitize_text_field($atts['order_dir']);
        $template = sanitize_text_field($atts['template']);

        $survey_manager = new FlowQ_Survey_Manager();
        $surveys = $survey_manager->get_surveys(array(
            'status' => $status,
            'limit' => $limit,
            'order' => $order,
            'order_dir' => $order_dir
        ));

        if (empty($surveys)) {
            return '<div class="survey-list-empty">' .
                   esc_html__('No surveys found.', 'flowq') .
                   '</div>';
        }

        ob_start();
        ?>
        <div class="survey-list template-<?php echo esc_attr($template); ?>">
            <?php foreach ($surveys as $survey): ?>
            <div class="survey-list-item survey-<?php echo esc_attr($survey['id']); ?>">
                <h3 class="survey-list-title">
                    <a href="<?php echo esc_url(add_query_arg('survey_id', $survey['id'])); ?>">
                        <?php echo esc_html($survey['title']); ?>
                    </a>
                </h3>

                <?php if ($show_description && !empty($survey['description'])): ?>
                <div class="survey-list-description">
                    <?php echo wp_kses_post(wp_trim_words($survey['description'], 30)); ?>
                </div>
                <?php endif; ?>

                <?php if ($show_stats): ?>
                <div class="survey-list-stats">
                    <?php
                    $stats = $survey_manager->get_survey_statistics($survey['id']);
                    if (!is_wp_error($stats)):
                    ?>
                    <span class="stat-participants"><?php echo intval($stats['total_participants']); ?> participants</span>
                    <span class="stat-completion"><?php echo number_format($stats['completion_rate'], 1); ?>% completion</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="survey-list-actions">
                    <?php echo do_shortcode('[survey_button id="' . $survey['id'] . '" text="Take Survey"]'); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Survey statistics shortcode
     */
    public function render_survey_stats($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'show' => 'all', // all, participants, completion, average_time
            'format' => 'inline' // inline, table, cards
        ), $atts, 'survey_stats');

        $survey_id = intval($atts['id']);
        $show = sanitize_text_field($atts['show']);
        $format = sanitize_text_field($atts['format']);

        if (!$survey_id) {
            return $this->render_error(__('Survey ID is required for statistics.', 'flowq'));
        }

        $survey_manager = new FlowQ_Survey_Manager();
        $stats = $survey_manager->get_survey_statistics($survey_id);

        if (is_wp_error($stats)) {
            return $this->render_error($stats->get_error_message());
        }

        $display_stats = array();

        if ($show === 'all' || $show === 'participants') {
            $display_stats['participants'] = array(
                'label' => __('Total Participants', 'flowq'),
                'value' => number_format($stats['total_participants'])
            );
        }

        if ($show === 'all' || $show === 'completion') {
            $display_stats['completion'] = array(
                'label' => __('Completion Rate', 'flowq'),
                'value' => number_format($stats['completion_rate'], 1) . '%'
            );
        }

        if ($show === 'all' || $show === 'average_time') {
            $display_stats['average_time'] = array(
                'label' => __('Average Completion Time', 'flowq'),
                'value' => $this->format_duration($stats['average_completion_time'])
            );
        }

        ob_start();
        ?>
        <div class="survey-stats format-<?php echo esc_attr($format); ?>">
            <?php if ($format === 'table'): ?>
            <table class="survey-stats-table">
                <?php foreach ($display_stats as $key => $stat): ?>
                <tr class="stat-<?php echo esc_attr($key); ?>">
                    <td class="stat-label"><?php echo esc_html($stat['label']); ?></td>
                    <td class="stat-value"><?php echo esc_html($stat['value']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php elseif ($format === 'cards'): ?>
            <div class="survey-stats-cards">
                <?php foreach ($display_stats as $key => $stat): ?>
                <div class="stat-card stat-<?php echo esc_attr($key); ?>">
                    <div class="stat-label"><?php echo esc_html($stat['label']); ?></div>
                    <div class="stat-value"><?php echo esc_html($stat['value']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: // inline ?>
            <div class="survey-stats-inline">
                <?php foreach ($display_stats as $key => $stat): ?>
                <span class="stat-item stat-<?php echo esc_attr($key); ?>">
                    <strong><?php echo esc_html($stat['label']); ?>:</strong> <?php echo esc_html($stat['value']); ?>
                </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Survey button shortcode
     */
    public function render_survey_button($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'text' => 'Take Survey',
            'style' => 'button', // button, link
            'size' => 'medium', // small, medium, large
            'color' => 'primary', // primary, secondary, success, warning, danger
            'target' => '_self',
            'css_class' => '',
            'new_window' => 'false'
        ), $atts, 'survey_button');

        $survey_id = intval($atts['id']);
        $text = sanitize_text_field($atts['text']);
        $style = sanitize_text_field($atts['style']);
        $size = sanitize_text_field($atts['size']);
        $color = sanitize_text_field($atts['color']);
        $target = sanitize_text_field($atts['target']);
        $css_class = sanitize_html_class($atts['css_class']);
        $new_window = filter_var($atts['new_window'], FILTER_VALIDATE_BOOLEAN);

        if (!$survey_id) {
            return $this->render_error(__('Survey ID is required for button.', 'flowq'));
        }

        // Build URL
        $url = add_query_arg('survey_id', $survey_id, get_permalink());

        // Build classes
        $classes = array(
            'survey-button',
            'survey-button-' . $style,
            'size-' . $size,
            'color-' . $color
        );

        if (!empty($css_class)) {
            $classes[] = $css_class;
        }

        if ($new_window) {
            $target = '_blank';
        }

        if ($style === 'link') {
            return sprintf(
                '<a href="%s" target="%s" class="%s">%s</a>',
                esc_url($url),
                esc_attr($target),
                esc_attr(implode(' ', $classes)),
                esc_html($text)
            );
        } else {
            return sprintf(
                '<a href="%s" target="%s" class="%s" role="button">%s</a>',
                esc_url($url),
                esc_attr($target),
                esc_attr(implode(' ', $classes)),
                esc_html($text)
            );
        }
    }

    /**
     * Survey embed shortcode (for iframes)
     */
    public function render_survey_embed($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'width' => '100%',
            'height' => '600px',
            'frameborder' => '0',
            'scrolling' => 'auto',
            'sandbox' => ''
        ), $atts, 'survey_embed');

        $survey_id = intval($atts['id']);
        $width = sanitize_text_field($atts['width']);
        $height = sanitize_text_field($atts['height']);
        $frameborder = sanitize_text_field($atts['frameborder']);
        $scrolling = sanitize_text_field($atts['scrolling']);
        $sandbox = sanitize_text_field($atts['sandbox']);

        if (!$survey_id) {
            return $this->render_error(__('Survey ID is required for embed.', 'flowq'));
        }

        // Build iframe URL
        $iframe_url = add_query_arg(array(
            'survey_id' => $survey_id,
            'embed' => '1'
        ), home_url('/'));

        $iframe_attrs = array(
            'src' => esc_url($iframe_url),
            'width' => esc_attr($width),
            'height' => esc_attr($height),
            'frameborder' => esc_attr($frameborder),
            'scrolling' => esc_attr($scrolling),
            'title' => esc_attr__('Survey', 'flowq')
        );

        if (!empty($sandbox)) {
            $iframe_attrs['sandbox'] = esc_attr($sandbox);
        }

        $iframe_html = '<iframe';
        foreach ($iframe_attrs as $attr => $value) {
            $iframe_html .= ' ' . $attr . '="' . $value . '"';
        }
        $iframe_html .= '></iframe>';

        return '<div class="survey-embed-container">' . $iframe_html . '</div>';
    }

    /**
     * Enqueue survey assets for specific theme
     */
    private function enqueue_survey_assets($theme) {
        // Enqueue base assets
        wp_enqueue_style('flowq-frontend');
        wp_enqueue_script('flowq-frontend-enhanced');

        // Enqueue theme-specific assets
        if ($theme !== 'default') {
            wp_enqueue_style(
                'flowq-theme-' . $theme,
                FLOWQ_URL . 'assets/css/themes/' . $theme . '.css',
                array('flowq-frontend'),
                FLOWQ_VERSION
            );
        }

        // Enqueue shortcode-specific styles
        wp_enqueue_style(
            'flowq-shortcode',
            FLOWQ_URL . 'assets/css/shortcode.css',
            array('flowq-frontend'),
            FLOWQ_VERSION
        );
    }

    /**
     * Maybe enqueue assets based on content
     */
    public function maybe_enqueue_assets() {
        global $post;

        if (is_admin() || !is_object($post)) {
            return;
        }

        // Check for any survey shortcodes
        $shortcodes = array(
            'flowq_survey',
            'flowq_survey_list',
            'flowq_survey_stats',
            'flowq_survey_button',
            'flowq_survey_embed'
        );

        foreach ($shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, $shortcode)) {
                $this->enqueue_survey_assets('default');
                break;
            }
        }
    }

    /**
     * Render error message
     */
    private function render_error($message) {
        return '<div class="flowq-error" role="alert">' .
               '<strong>' . esc_html__('Survey Error:', 'flowq') . '</strong> ' .
               esc_html($message) .
               '</div>';
    }

    /**
     * Format duration in seconds to human readable
     */
    private function format_duration($seconds) {
        if ($seconds < 60) {
            /* translators: %d: number of seconds */
            return sprintf(__('%d seconds', 'flowq'), $seconds);
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            /* translators: %d: number of minutes */
            return sprintf(__('%d minutes', 'flowq'), $minutes);
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            /* translators: 1: number of hours, 2: number of minutes */
            return sprintf(__('%1$d hours %2$d minutes', 'flowq'), $hours, $minutes);
        }
    }

    /**
     * Get available themes
     */
    public static function get_available_themes() {
        return self::$available_themes;
    }

    /**
     * Get shortcode documentation
     */
    public static function get_shortcode_docs() {
        return array(
            'flowq_survey' => array(
                'description' => __('Display a survey form', 'flowq'),
                'attributes' => array(
                    'id' => __('Survey ID (required)', 'flowq'),
                    'theme' => __('Theme name (default, minimal, modern, card, full-width)', 'flowq'),
                    'width' => __('Container width (100%, 500px, etc.)', 'flowq'),
                    'height' => __('Container height (auto, 600px, etc.)', 'flowq'),
                    'show_title' => __('Show survey title (true/false)', 'flowq'),
                    'show_description' => __('Show survey description (true/false)', 'flowq'),
                    'show_progress' => __('Show progress bar (true/false)', 'flowq'),
                    'auto_start' => __('Auto-scroll to survey (true/false)', 'flowq'),
                    'css_class' => __('Additional CSS class', 'flowq')
                ),
                'example' => '[flowq_survey id="1" theme="modern" show_progress="true"]'
            ),
            'survey_list' => array(
                'description' => __('Display a list of surveys', 'flowq'),
                'attributes' => array(
                    'status' => __('Survey status filter (published, draft)', 'flowq'),
                    'limit' => __('Number of surveys to show', 'flowq'),
                    'show_description' => __('Show descriptions (true/false)', 'flowq'),
                    'show_stats' => __('Show statistics (true/false)', 'flowq')
                ),
                'example' => '[survey_list limit="5" show_stats="true"]'
            ),
            'survey_stats' => array(
                'description' => __('Display survey statistics', 'flowq'),
                'attributes' => array(
                    'id' => __('Survey ID (required)', 'flowq'),
                    'show' => __('What to show (all, participants, completion, average_time)', 'flowq'),
                    'format' => __('Display format (inline, table, cards)', 'flowq')
                ),
                'example' => '[survey_stats id="1" format="cards"]'
            ),
            'survey_button' => array(
                'description' => __('Display a survey button/link', 'flowq'),
                'attributes' => array(
                    'id' => __('Survey ID (required)', 'flowq'),
                    'text' => __('Button text', 'flowq'),
                    'style' => __('Style (button, link)', 'flowq'),
                    'size' => __('Size (small, medium, large)', 'flowq'),
                    'color' => __('Color (primary, secondary, success, warning, danger)', 'flowq')
                ),
                'example' => '[survey_button id="1" text="Start Survey" style="button" size="large"]'
            ),
            'survey_embed' => array(
                'description' => __('Embed survey in iframe', 'flowq'),
                'attributes' => array(
                    'id' => __('Survey ID (required)', 'flowq'),
                    'width' => __('Iframe width', 'flowq'),
                    'height' => __('Iframe height', 'flowq')
                ),
                'example' => '[survey_embed id="1" width="100%" height="600px"]'
            )
        );
    }
}