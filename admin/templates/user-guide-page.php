<?php
/**
 * User Guide Page Template
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Read the USER-GUIDE.md file
$guide_file = FLOWQ_PATH . 'USER-GUIDE.md';
$guide_content = '';

if (file_exists($guide_file)) {
    $guide_content = file_get_contents($guide_file);

    // Remove Installation section (not needed in admin panel)
    // Match from "## Installation" until the next "## " section
    $guide_content = preg_replace('/## Installation.*?(?=## )/s', '', $guide_content);
}

/**
 * Simple Markdown to HTML converter
 * Converts basic markdown syntax to HTML
 */
function flowq_markdown_to_html($markdown) {
    // Convert headers
    $html = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $markdown);
    $html = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $html);

    // Convert bold
    $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);

    // Convert italic
    $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);

    // Convert links
    $html = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2" target="_blank">$1</a>', $html);

    // Convert horizontal rules
    $html = preg_replace('/^---$/m', '<hr>', $html);

    // Convert unordered lists
    $html = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.*?<\/li>\n)+/s', '<ul>$0</ul>', $html);

    // Convert code blocks
    $html = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $html);

    // Convert inline code
    $html = preg_replace('/`(.*?)`/', '<code>$1</code>', $html);

    // Convert paragraphs (lines with content)
    $lines = explode("\n", $html);
    $in_list = false;
    $in_code = false;
    $processed_lines = array();

    foreach ($lines as $line) {
        $trimmed = trim($line);

        // Skip empty lines
        if (empty($trimmed)) {
            $processed_lines[] = '';
            continue;
        }

        // Check if it's already HTML
        if (preg_match('/^<(h[1-6]|ul|ol|li|hr|pre|code)/', $trimmed)) {
            $processed_lines[] = $line;
        } else {
            // Wrap in paragraph if not already HTML
            if (!preg_match('/<\/?(h[1-6]|ul|ol|li|hr|pre|code|p)>/', $trimmed)) {
                $processed_lines[] = '<p>' . $line . '</p>';
            } else {
                $processed_lines[] = $line;
            }
        }
    }

    $html = implode("\n", $processed_lines);

    // Clean up extra paragraph tags around headers and lists
    $html = preg_replace('/<p>(<h[1-6]>.*?<\/h[1-6]>)<\/p>/', '$1', $html);
    $html = preg_replace('/<p>(<ul>.*?<\/ul>)<\/p>/s', '$1', $html);
    $html = preg_replace('/<p>(<hr>)<\/p>/', '$1', $html);
    $html = preg_replace('/<p>(<pre>.*?<\/pre>)<\/p>/s', '$1', $html);

    return $html;
}

// Convert markdown to HTML
$guide_html = flowq_markdown_to_html($guide_content);

// Extract table of contents
preg_match_all('/<h2>(.*?)<\/h2>/', $guide_html, $toc_matches);
$toc_items = $toc_matches[1];

?>

<div class="wrap flowq-user-guide-page">
    <div class="flowq-guide-header">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-book-alt" style="font-size: 30px; width: 30px; height: 30px;"></span>
            <?php esc_html_e('FlowQ User Guide', 'wp-dynamic-survey'); ?>
        </h1>
        <p class="description">
            <?php esc_html_e('Complete guide to using FlowQ - from getting started to creating dynamic surveys with conditional logic', 'wp-dynamic-survey'); ?>
        </p>
    </div>

    <div class="flowq-guide-container">

        <!-- Sidebar Navigation -->
        <div class="flowq-guide-sidebar">
            <div class="guide-toc">
                <h3><?php esc_html_e('Table of Contents', 'wp-dynamic-survey'); ?></h3>
                <ul class="toc-list">
                    <?php if (!empty($toc_items)): ?>
                        <?php foreach ($toc_items as $index => $item): ?>
                            <?php
                            // Create anchor from title
                            $anchor = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/', '', $item)));
                            ?>
                            <li>
                                <a href="#section-<?php echo esc_attr($anchor); ?>">
                                    <span class="toc-number"><?php echo ($index + 1); ?>.</span>
                                    <?php echo esc_html($item); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>

                <div class="guide-quick-links">
                    <h4><?php esc_html_e('Quick Links', 'wp-dynamic-survey'); ?></h4>
                    <ul>
                        <li>
                            <a href="<?php echo admin_url('admin.php?page=flowq-add'); ?>">
                                <span class="dashicons dashicons-plus-alt"></span>
                                <?php esc_html_e('Create New Survey', 'wp-dynamic-survey'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo admin_url('admin.php?page=flowq'); ?>">
                                <span class="dashicons dashicons-list-view"></span>
                                <?php esc_html_e('View All Surveys', 'wp-dynamic-survey'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo admin_url('admin.php?page=flowq-settings'); ?>">
                                <span class="dashicons dashicons-admin-settings"></span>
                                <?php esc_html_e('Settings', 'wp-dynamic-survey'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flowq-guide-content">
            <?php if (!empty($guide_content)): ?>
                <div class="guide-content-inner">
                    <?php
                    // Add section anchors to h2 headers
                    $guide_html_with_anchors = preg_replace_callback(
                        '/<h2>(.*?)<\/h2>/',
                        function($matches) {
                            $anchor = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/', '', $matches[1])));
                            return '<h2 id="section-' . $anchor . '">' . $matches[1] . '</h2>';
                        },
                        $guide_html
                    );

                    // Output the HTML (already sanitized during conversion)
                    echo wp_kses_post($guide_html_with_anchors);
                    ?>
                </div>
            <?php else: ?>
                <div class="notice notice-warning">
                    <p><?php esc_html_e('User guide file not found. Please ensure USER-GUIDE.md exists in the plugin directory.', 'wp-dynamic-survey'); ?></p>
                </div>
            <?php endif; ?>

            <!-- Back to Top Button -->
            <div class="guide-back-to-top">
                <a href="#top" class="button button-secondary">
                    <span class="dashicons dashicons-arrow-up-alt"></span>
                    <?php esc_html_e('Back to Top', 'wp-dynamic-survey'); ?>
                </a>
            </div>
        </div>

    </div>
</div>

<style>
/* User Guide Page Styles */
.flowq-user-guide-page {
    max-width: 100%;
    margin: 20px 0;
}

.flowq-guide-header {
    background: #fff;
    padding: 20px 30px;
    margin: 0 0 20px 0;
    border-left: 4px solid #2271b1;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
}

.flowq-guide-header h1 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 10px 0;
}

.flowq-guide-header .description {
    margin: 0;
    font-size: 14px;
    color: #50575e;
}

.flowq-guide-container {
    display: flex;
    gap: 30px;
    align-items: flex-start;
}

/* Sidebar Navigation */
.flowq-guide-sidebar {
    width: 280px;
    flex-shrink: 0;
    position: sticky;
    top: 32px;
    max-height: calc(100vh - 100px);
    overflow-y: auto;
}

@media (max-width: 1200px) {
    .flowq-guide-container {
        flex-direction: column;
    }

    .flowq-guide-sidebar {
        width: 100%;
        position: static;
        max-height: none;
    }
}

.guide-toc {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 6px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
}

.guide-toc h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #1d2327;
    border-bottom: 2px solid #2271b1;
    padding-bottom: 10px;
}

.toc-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.toc-list li {
    margin: 0 0 8px 0;
}

.toc-list a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    text-decoration: none;
    color: #2c3338;
    border-radius: 4px;
    transition: all 0.2s ease;
    font-size: 13px;
    line-height: 1.4;
}

.toc-list a:hover {
    background: #f0f6fc;
    color: #2271b1;
    padding-left: 16px;
}

.toc-number {
    color: #2271b1;
    font-weight: 600;
    flex-shrink: 0;
}

.guide-quick-links {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #dcdcde;
}

.guide-quick-links h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #1d2327;
}

.guide-quick-links ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.guide-quick-links li {
    margin: 0 0 6px 0;
}

.guide-quick-links a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    text-decoration: none;
    color: #2c3338;
    font-size: 13px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.guide-quick-links a:hover {
    background: #f0f6fc;
    color: #2271b1;
}

.guide-quick-links .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    color: #2271b1;
}

/* Main Content Area */
.flowq-guide-content {
    flex: 1;
    min-width: 0;
}

.guide-content-inner {
    background: #fff;
    padding: 40px;
    border: 1px solid #c3c4c7;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
}

/* Content Typography */
.guide-content-inner h1 {
    font-size: 32px;
    margin: 0 0 20px 0;
    color: #1d2327;
    border-bottom: 3px solid #2271b1;
    padding-bottom: 15px;
}

.guide-content-inner h2 {
    font-size: 26px;
    margin: 40px 0 20px 0;
    color: #1d2327;
    border-bottom: 2px solid #dcdcde;
    padding-bottom: 10px;
    scroll-margin-top: 50px;
}

.guide-content-inner h3 {
    font-size: 20px;
    margin: 30px 0 15px 0;
    color: #1d2327;
}

.guide-content-inner h4 {
    font-size: 16px;
    margin: 20px 0 10px 0;
    color: #2c3338;
    font-weight: 600;
}

.guide-content-inner p {
    font-size: 14px;
    line-height: 1.7;
    color: #2c3338;
    margin: 0 0 15px 0;
}

.guide-content-inner ul,
.guide-content-inner ol {
    margin: 0 0 20px 20px;
    padding: 0;
}

.guide-content-inner li {
    font-size: 14px;
    line-height: 1.7;
    color: #2c3338;
    margin: 0 0 8px 0;
}

.guide-content-inner a {
    color: #2271b1;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: all 0.2s ease;
}

.guide-content-inner a:hover {
    border-bottom-color: #2271b1;
}

.guide-content-inner code {
    background: #f6f7f7;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #d63638;
}

.guide-content-inner pre {
    background: #1e1e1e;
    color: #d4d4d4;
    padding: 20px;
    border-radius: 6px;
    overflow-x: auto;
    margin: 0 0 20px 0;
}

.guide-content-inner pre code {
    background: transparent;
    color: inherit;
    padding: 0;
}

.guide-content-inner hr {
    border: none;
    border-top: 1px solid #dcdcde;
    margin: 30px 0;
}

.guide-content-inner strong {
    font-weight: 600;
    color: #1d2327;
}

.guide-content-inner em {
    font-style: italic;
}

/* Back to Top Button */
.guide-back-to-top {
    margin-top: 30px;
    text-align: center;
}

.guide-back-to-top .button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

/* Scrollbar styling for sidebar */
.flowq-guide-sidebar::-webkit-scrollbar {
    width: 6px;
}

.flowq-guide-sidebar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.flowq-guide-sidebar::-webkit-scrollbar-thumb {
    background: #c3c4c7;
    border-radius: 3px;
}

.flowq-guide-sidebar::-webkit-scrollbar-thumb:hover {
    background: #a0a5aa;
}

/* Responsive adjustments */
@media (max-width: 782px) {
    .guide-content-inner {
        padding: 20px;
    }

    .guide-content-inner h1 {
        font-size: 24px;
    }

    .guide-content-inner h2 {
        font-size: 20px;
    }

    .guide-content-inner h3 {
        font-size: 18px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Smooth scrolling for TOC links
    $('.toc-list a, .guide-back-to-top a').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');

        if (target === '#top') {
            $('html, body').animate({
                scrollTop: 0
            }, 500);
        } else {
            var targetElement = $(target);
            if (targetElement.length) {
                $('html, body').animate({
                    scrollTop: targetElement.offset().top - 50
                }, 500);
            }
        }
    });

    // Highlight active section in TOC
    $(window).on('scroll', function() {
        var scrollPos = $(window).scrollTop() + 100;

        $('.guide-content-inner h2').each(function() {
            var section = $(this);
            var sectionTop = section.offset().top;
            var sectionBottom = sectionTop + section.outerHeight();
            var sectionId = section.attr('id');

            if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
                $('.toc-list a').removeClass('active');
                $('.toc-list a[href="#' + sectionId + '"]').addClass('active');
            }
        });
    });
});
</script>

<style>
.toc-list a.active {
    background: #f0f6fc;
    color: #2271b1;
    font-weight: 600;
    padding-left: 16px;
}
</style>
