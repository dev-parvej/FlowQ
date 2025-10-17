<?php
/**
 * Add/Edit Survey Admin Template
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$is_edit = !empty($survey);
$page_title = $is_edit ? __('Edit Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) : __('Add New Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
?>

<div class="wrap">
    <div class="page-header-with-back">
        <h1><?php echo esc_html($page_title); ?></h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys')); ?>" class="page-title-action back-button">
            <?php echo esc_html__('← Back to Surveys', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
        </a>
    </div>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="wp_dynamic_survey_save_survey">
        <?php wp_nonce_field('wp_dynamic_survey_save_survey'); ?>

        <?php if ($is_edit): ?>
            <input type="hidden" name="survey_id" value="<?php echo esc_attr($survey['id']); ?>">
        <?php endif; ?>

        <!-- Survey Details Card -->
        <div class="survey-card">
            <h3 class="card-title"><?php echo esc_html__('Survey Details', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>
            <div class="card-content">
                <div class="form-field">
                    <label for="survey_title" class="field-label">
                        <?php echo esc_html__('Survey Title', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Enter a descriptive title for your survey. This will be displayed to participants.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                    </label>
                    <input type="text"
                           id="survey_title"
                           name="survey_title"
                           class="full-width-input"
                           value="<?php echo esc_attr($survey['title'] ?? ''); ?>"
                           placeholder="<?php echo esc_attr__('Enter your survey title...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>"
                           required>
                </div>

                <div class="form-field">
                    <label for="survey_description" class="field-label">
                        <?php echo esc_html__('Description', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Optional description explaining what this survey is about. Participants will see this before starting.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                    </label>
                    <textarea id="survey_description"
                              name="survey_description"
                              class="full-width-textarea"
                              rows="4"
                              placeholder="<?php echo esc_attr__('Describe what this survey is about...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>"><?php echo esc_textarea($survey['description'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Display Settings Card -->
        <div class="survey-card">
            <h3 class="card-title"><?php echo esc_html__('Display Settings', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>
            <div class="card-content">
                <div>
                    <label class="checkbox-label">
                        <input type="checkbox"
                               id="show_header"
                               name="show_header"
                               value="1"
                               <?php checked(!empty($survey['show_header']), true); ?>>
                        <?php echo esc_html__('Show Custom Header and Subtitle', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </label>
                    <p class="field-description">
                        <?php echo esc_html__('Display a custom header and subtitle at the top of the participant form instead of the survey title', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </p>
                </div>

                <div id="header-fields-container" style="display: none; margin-top: 30px;">
                    <div class="form-field">
                        <label for="form_header" class="field-label">
                            <?php echo esc_html__('Survey Form Header', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            <span style="color: #dc3232;">*</span>
                            <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Main heading displayed at the top of the participant form (max 255 characters)', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                                <span class="dashicons dashicons-editor-help"></span>
                            </span>
                        </label>
                        <input type="text"
                               id="form_header"
                               name="form_header"
                               class="full-width-input"
                               value="<?php echo esc_attr($survey['form_header'] ?? ''); ?>"
                               placeholder="<?php echo esc_attr__('e.g., Help Us Improve Your Experience', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>"
                               maxlength="255">
                        <p class="field-description character-count">
                            <span id="header-char-count">0</span> / 255 <?php echo esc_html__('characters', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="form_subtitle" class="field-label">
                            <?php echo esc_html__('Survey Form Subtitle', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Subtitle displayed below the header (optional)', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                                <span class="dashicons dashicons-editor-help"></span>
                            </span>
                        </label>
                        <?php
                        wp_editor($survey['form_subtitle'] ?? '', 'form_subtitle', array(
                            'textarea_name' => 'form_subtitle',
                            'textarea_rows' => 6,
                            'media_buttons' => false,
                            'teeny' => true,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,link,unlink,bullist,numlist,undo,redo'
                            ),
                            'quicktags' => array(
                                'buttons' => 'strong,em,link,ul,ol,li'
                            )
                        ));
                        ?>
                        <p class="field-description">
                            <?php echo esc_html__('e.g., Your feedback matters! Take 2 minutes to share your thoughts.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Settings Card -->
        <div class="survey-card">
            <h3 class="card-title"><?php echo esc_html__('Page Settings', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>
            <div class="card-content">
                <div class="form-field">
                    <label for="thank_you_page_slug" class="field-label">
                        <?php echo esc_html__('Thank You Page', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Optional: Select an existing published page. After completion, participants get a secure token to access this page (expires in 1 hour).', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                    </label>
                    <div class="input-with-action">
                        <?php
                        // Get all published pages
                        $pages = get_pages(array(
                            'post_status' => 'publish',
                            'sort_column' => 'post_title',
                            'sort_order' => 'ASC'
                        ));

                        $current_slug = $survey['thank_you_page_slug'] ?? '';
                        ?>
                        <select id="thank_you_page_slug"
                                name="thank_you_page_slug"
                                class="full-width-select">
                            <option value=""><?php echo esc_html__('-- Select a page --', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                            <?php foreach ($pages as $page):
                                $page_slug = $page->post_name;
                                $page_title = $page->post_title;
                                $is_thank_you = stripos($page_title, 'thank you') !== false || stripos($page_title, 'thankyou') !== false;
                                $display_title = $is_thank_you ? '⭐ ' . $page_title : $page_title;
                                $selected = ($page_slug === $current_slug) ? 'selected' : '';
                            ?>
                                <option value="<?php echo esc_attr($page_slug); ?>"
                                        <?php echo $selected; ?>
                                        data-is-thank-you="<?php echo $is_thank_you ? '1' : '0'; ?>">
                                    <?php echo esc_html($display_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($current_slug)): ?>
                            <?php
                            $thank_you_page = get_page_by_path($current_slug);
                            $edit_url = $thank_you_page
                                ? admin_url('post.php?post=' . $thank_you_page->ID . '&action=edit')
                                : admin_url('edit.php?post_type=page');
                            ?>
                            <a href="<?php echo esc_url($edit_url); ?>"
                               id="edit-page-button"
                               class="button button-secondary edit-page-button"
                               target="_blank"
                               style="<?php echo empty($current_slug) ? 'display:none;' : ''; ?>">
                                <span class="dashicons dashicons-edit"></span>
                                <?php echo esc_html__('Edit Page', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </a>
                        <?php else: ?>
                            <a href="#"
                               id="edit-page-button"
                               class="button button-secondary edit-page-button"
                               target="_blank"
                               style="display:none;">
                                <span class="dashicons dashicons-edit"></span>
                                <?php echo esc_html__('Edit Page', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <p class="field-description">
                        <?php echo esc_html__('⭐ Pages marked with a star contain "Thank You" in their title', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </p>
                </div>

                <div class="form-field">
                    <label for="survey_status" class="field-label">
                        <?php echo esc_html__('Status', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Draft: Hidden from participants. Published: Live and accessible. Archived: No longer accepting responses.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                    </label>
                    <select id="survey_status" name="survey_status" class="status-select">
                        <option value="draft" <?php selected($survey['status'] ?? 'draft', 'draft'); ?>>
                            <?php echo esc_html__('Draft', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </option>
                        <option value="published" <?php selected($survey['status'] ?? '', 'published'); ?>>
                            <?php echo esc_html__('Published', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </option>
                        <option value="archived" <?php selected($survey['status'] ?? '', 'archived'); ?>>
                            <?php echo esc_html__('Archived', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <div class="primary-actions">
                <button type="submit" name="submit" class="button button-primary-custom">
                    <?php echo $is_edit ? __('Update Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) : __('Create Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                </button>
                <?php if ($is_edit): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-questions&survey_id=' . $survey['id'])); ?>" class="button button-secondary-custom">
                        <span class="dashicons dashicons-edit"></span>
                        <?php echo esc_html__('Manage Questions', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <?php if ($is_edit): ?>
        <!-- Survey Actions Card -->
        <div class="survey-card actions-card">
            <h3 class="card-title"><?php echo esc_html__('Survey Actions', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>
            <div class="card-content">
                <?php if ($survey['status'] === 'published'): ?>
                    <!-- Analytics Section -->
                    <div class="action-section">
                        <div class="action-item">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-analytics&survey_id=' . $survey['id'])); ?>"
                               class="button button-secondary-custom analytics-button">
                                <span class="dashicons dashicons-chart-bar"></span>
                                <?php echo esc_html__('View Analytics', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </a>
                        </div>
                    </div>

                    <!-- Shortcode Section -->
                    <div class="action-section">
                        <label class="field-label"><?php echo esc_html__('Shortcode', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                        <div class="shortcode-container form-field">
                            <input type="text"
                                   id="shortcode-input"
                                   class="shortcode-input"
                                   value="<?php echo esc_attr('[wp_dynamic_survey id="' . $survey['id'] . '"]'); ?>"
                                   readonly>
                            <button type="button" class="button copy-button" onclick="copyShortcode()">
                                <span class="dashicons dashicons-clipboard"></span>
                                <?php echo esc_html__('Copy', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </button>
                        </div>
                        <p class="field-description">
                            <?php echo esc_html__('Use this shortcode to embed the survey in any post or page.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($survey['status'] !== 'published'): ?>
                <!-- Danger Zone -->
                <div class="action-section danger-zone">
                    <label class="field-label danger-label"><?php echo esc_html__('Danger Zone', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                    <div class="danger-actions">
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wp-dynamic-surveys&action=delete&survey_id=' . $survey['id']), 'survey_action')); ?>"
                           class="button button-danger"
                           onclick="return confirm('<?php echo esc_html__('Are you sure you want to delete this survey? This action cannot be undone.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>')">
                            <span class="dashicons dashicons-trash"></span>
                            <?php echo esc_html__('Delete Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </a>
                        <span class="danger-description"><?php echo esc_html__('This action cannot be undone.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Page header with back button */
.page-header-with-back {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.page-header-with-back h1 {
    margin: 0;
    flex-grow: 1;
}

.back-button {
    background: none !important;
    border: none !important;
    color: #2271b1 !important;
    text-decoration: none !important;
    padding: 0 !important;
    font-size: 13px !important;
    margin-left: 15px;
    transition: color 0.2s ease;
}

.back-button:hover {
    color: #135e96 !important;
    text-decoration: underline !important;
}

.back-button:focus {
    color: #135e96 !important;
    text-decoration: underline !important;
    outline: 1px dotted #2271b1 !important;
}

/* Card Layout */
.survey-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    margin-bottom: 20px;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}

.survey-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.card-title {
    background: #f6f7f7;
    border-bottom: 1px solid #c3c4c7;
    margin: 0;
    padding: 15px 20px;
    font-size: 16px;
    font-weight: 600;
    color: #1d2327;
}

.card-content {
    padding: 20px;
}

/* Form Fields */
.form-field {
    margin-bottom: 24px;
}

.form-field:last-child {
    margin-bottom: 0;
}

.field-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 600;
    color: #1d2327;
    margin-bottom: 6px;
}

/* Help Tooltips */
.help-tooltip {
    position: relative;
    display: inline-block;
    cursor: help;
}

.help-tooltip .dashicons {
    font-size: 16px;
    color: #646970;
    transition: color 0.2s ease;
}

.help-tooltip:hover .dashicons {
    color: #2271b1;
}

.help-tooltip:before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 125%;
    left: 50%;
    transform: translateX(0px);
    background: #1d2327;
    color: #fff;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 400;
    line-height: 1.4;
    white-space: nowrap;
    max-width: 250px;
    white-space: normal;
    width: max-content;
    max-width: 300px;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
    pointer-events: none;
}

.help-tooltip:after {
    content: '';
    position: absolute;
    bottom: 115%;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: #1d2327;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.help-tooltip:hover:before,
.help-tooltip:hover:after {
    opacity: 1;
    visibility: visible;
}

/* Input with Action Button */
.input-with-action {
    display: flex;
    gap: 8px;
    align-items: center;
}

.input-with-action .full-width-input {
    flex: 1;
}

.edit-page-button {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    padding: 8px 12px !important;
    font-size: 13px !important;
    white-space: nowrap;
    text-decoration: none !important;
}

.field-description {
    margin: 6px 0 0 0 !important;
    font-size: 13px;
    color: #646970;
    line-height: 1.5;
}

.full-width-input,
.full-width-textarea {
    width: 100%;
    max-width: 100%;
    padding: 8px 12px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    font-size: 14px;
    line-height: 1.5;
    transition: border-color 0.2s ease;
}

.full-width-input:focus,
.full-width-textarea:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
}

.status-select,
.full-width-select {
    padding: 6px 8px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    font-size: 14px;
    min-width: 200px;
}

.full-width-select {
    width: 100%;
    max-width: 100%;
    padding: 8px 12px;
    line-height: 1.5;
    transition: border-color 0.2s ease;
}

.full-width-select:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
}

/* Action Buttons */
.action-buttons {
    margin: 20px 0;
}

.primary-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.button-primary-custom {
    background: #2271b1 !important;
    border-color: #2271b1 !important;
    color: #fff !important;
    padding: 8px 16px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    border-radius: 6px !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    transition: all 0.2s ease !important;
}

.button-primary-custom:hover {
    background: #135e96 !important;
    border-color: #135e96 !important;
    transform: translateY(-1px);
}

.button-secondary-custom {
    background: #fff !important;
    border: 1px solid #2271b1 !important;
    color: #2271b1 !important;
    padding: 8px 16px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    border-radius: 6px !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    transition: all 0.2s ease !important;
}

.button-secondary-custom:hover {
    background: #f0f6fc !important;
    border-color: #135e96 !important;
    color: #135e96 !important;
}

/* Actions Card */
.actions-card .card-content {
    padding-top: 16px;
}

.action-section {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f0f0f1;
}

.action-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.action-item {
    margin-bottom: 8px;
}

/* Shortcode Container */
.shortcode-container {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: 8px;
}

.shortcode-input {
    flex: 1;
    background: #f6f7f7;
    border: 1px solid #c3c4c7;
    padding: 8px 12px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 13px;
    color: #1d2327;
}

.copy-button {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    padding: 8px 12px !important;
    font-size: 13px !important;
    white-space: nowrap;
}

/* Danger Zone */
.danger-zone {
    background: #fef7f7;
    border: 1px solid #f87171;
    border-radius: 6px;
    padding: 16px;
    margin-top: 8px;
    margin-bottom: 10px;
}

.danger-label {
    color: #dc2626 !important;
    font-weight: 600 !important;
}

.danger-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 8px;
}

.button-danger {
    background: #dc2626 !important;
    border-color: #dc2626 !important;
    color: #fff !important;
    padding: 6px 12px !important;
    font-size: 13px !important;
    border-radius: 4px !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    transition: all 0.2s ease !important;
}

.button-danger:hover {
    background: #b91c1c !important;
    border-color: #b91c1c !important;
    transform: translateY(-1px);
}

.danger-description {
    font-size: 12px;
    color: #dc2626;
    font-style: italic;
}

/* Responsive Design */
@media screen and (max-width: 782px) {
    .page-header-with-back {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .back-button {
        margin-left: 0;
        align-self: flex-start;
    }

    .primary-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .shortcode-container {
        flex-direction: column;
        align-items: stretch;
    }

    .danger-actions {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}

/* Animation for success feedback */
.copy-success {
    background: #00a32a !important;
    border-color: #00a32a !important;
    color: #fff !important;
}

.copy-success .dashicons-clipboard:before {
    content: "\f147"; /* checkmark */
}

/* Checkbox Label */
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #1d2327;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    margin: 0;
    cursor: pointer;
}

/* Character Count */
.character-count {
    font-size: 12px !important;
    color: #646970 !important;
    margin-top: 4px !important;
}

#header-char-count {
    font-weight: 600;
    color: #1d2327;
}
</style>

<script>
function copyShortcode() {
    const input = document.getElementById('shortcode-input');
    const button = document.querySelector('.copy-button');

    // Select and copy the text
    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices

    navigator.clipboard.writeText(input.value).then(function() {
        // Show success feedback
        button.classList.add('copy-success');
        const originalText = button.innerHTML;
        button.innerHTML = '<span class="dashicons dashicons-yes"></span><?php echo esc_html__('Copied!', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>';

        // Reset after 2 seconds
        setTimeout(function() {
            button.classList.remove('copy-success');
            button.innerHTML = originalText;
        }, 2000);
    }).catch(function() {
        // Fallback for older browsers
        input.select();
        document.execCommand('copy');
        alert('<?php echo esc_html__('Shortcode copied to clipboard!', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>');
    });
}

// Legacy function for compatibility
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('<?php echo esc_html__('Shortcode copied to clipboard!', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>');
    });
}

// Thank You Page Selection Handler
jQuery(document).ready(function($) {
    var $thankYouPageSelect = $('#thank_you_page_slug');
    var $editPageButton = $('#edit-page-button');

    // Get pages data for edit URL generation
    var pages = <?php echo json_encode(array_map(function($page) {
        return array('ID' => $page->ID, 'post_name' => $page->post_name);
    }, $pages)); ?>;

    // Handle thank you page selection change
    $thankYouPageSelect.on('change', function() {
        var selectedSlug = $(this).val();

        if (selectedSlug) {
            // Find the selected page
            var selectedPage = pages.find(function(page) {
                return page.post_name === selectedSlug;
            });

            if (selectedPage) {
                var editUrl = '<?php echo admin_url("post.php?post="); ?>' + selectedPage.ID + '&action=edit';
                $editPageButton.attr('href', editUrl).show();
            }
        } else {
            $editPageButton.hide();
        }
    });

    // Initialize button visibility on page load
    if ($thankYouPageSelect.val()) {
        $editPageButton.show();
    } else {
        $editPageButton.hide();
    }
});

// Header Fields Toggle and Validation
jQuery(document).ready(function($) {
    var $showHeaderCheckbox = $('#show_header');
    var $headerFieldsContainer = $('#header-fields-container');
    var $formHeaderInput = $('#form_header');
    var $charCount = $('#header-char-count');
    var $surveyForm = $('form[action*="admin-post.php"]');

    // Update character count
    function updateCharCount() {
        var length = $formHeaderInput.val().length;
        $charCount.text(length);

        // Change color if approaching limit
        if (length > 240) {
            $charCount.css('color', '#dc3232');
        } else if (length > 200) {
            $charCount.css('color', '#dba617');
        } else {
            $charCount.css('color', '#1d2327');
        }
    }

    // Toggle header fields visibility
    function toggleHeaderFields() {
        if ($showHeaderCheckbox.is(':checked')) {
            $headerFieldsContainer.slideDown(300);
            $formHeaderInput.attr('required', true);
        } else {
            $headerFieldsContainer.slideUp(300);
            $formHeaderInput.attr('required', false);
        }
    }

    // Initialize on page load
    toggleHeaderFields();
    updateCharCount();

    // Listen for checkbox changes
    $showHeaderCheckbox.on('change', toggleHeaderFields);

    // Listen for input changes on header field
    $formHeaderInput.on('input', updateCharCount);

    // Form validation
    $surveyForm.on('submit', function(e) {
        if ($showHeaderCheckbox.is(':checked')) {
            var headerValue = $formHeaderInput.val().trim();

            if (headerValue === '') {
                e.preventDefault();
                alert('<?php echo esc_js(__('Survey Form Header is required when Show Custom Header is enabled', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>');
                $formHeaderInput.focus();

                // Scroll to the field
                $('html, body').animate({
                    scrollTop: $formHeaderInput.offset().top - 100
                }, 500);

                return false;
            }
        }
    });
});
</script>