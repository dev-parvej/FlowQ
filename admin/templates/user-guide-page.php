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
            <?php esc_html_e('FlowQ User Guide', 'flowq'); ?>
        </h1>
        <p class="description">
            <?php esc_html_e('Complete guide to using FlowQ - from getting started to creating dynamic surveys with conditional logic', 'flowq'); ?>
        </p>
    </div>

    <div class="flowq-guide-container">

        <!-- Sidebar Navigation -->
        <div class="flowq-guide-sidebar">
            <div class="guide-toc">
                <h3><?php esc_html_e('Table of Contents', 'flowq'); ?></h3>
                <ul class="toc-list">
                    <?php if (!empty($toc_items)): ?>
                        <?php foreach ($toc_items as $index => $item): ?>
                            <?php
                            // Create anchor from title
                            $anchor = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/', '', $item)));
                            ?>
                            <li>
                                <a href="#section-<?php echo esc_attr($anchor); ?>">
                                    <span class="toc-number"><?php echo absint($index + 1); ?>.</span>
                                    <?php echo esc_html($item); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>

                <div class="guide-quick-links">
                    <h4><?php esc_html_e('Quick Links', 'flowq'); ?></h4>
                    <ul>
                        <li>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=flowq-add')); ?>">
                                <span class="dashicons dashicons-plus-alt"></span>
                                <?php esc_html_e('Create New Survey', 'flowq'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=flowq')); ?>">
                                <span class="dashicons dashicons-list-view"></span>
                                <?php esc_html_e('View All Surveys', 'flowq'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=flowq-settings')); ?>">
                                <span class="dashicons dashicons-admin-settings"></span>
                                <?php esc_html_e('Settings', 'flowq'); ?>
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
                    <p><?php esc_html_e('User guide file not found. Please ensure USER-GUIDE.md exists in the plugin directory.', 'flowq'); ?></p>
                </div>
            <?php endif; ?>

            <!-- Back to Top Button -->
            <div class="guide-back-to-top">
                <a href="#top" class="button button-secondary">
                    <span class="dashicons dashicons-arrow-up-alt"></span>
                    <?php esc_html_e('Back to Top', 'flowq'); ?>
                </a>
            </div>
        </div>

    </div>
</div>
