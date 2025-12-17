<?php
/**
 * Add/Edit Survey Admin Template
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$is_edit = !empty($survey);
$page_title = $is_edit ? __('Edit Survey', 'flowq') : __('Add New Survey', 'flowq');

// Get all published pages for Thank You page selector
$pages = get_pages(array(
    'post_status' => 'publish',
    'sort_column' => 'post_title',
    'sort_order' => 'ASC'
));

// Localize script data for add-survey.js
wp_localize_script('flowq-add-survey', 'flowqAddSurvey', array(
    'copiedText' => __('Copied!', 'flowq'),
    'headerRequiredText' => esc_js(__('Survey Form Header is required when Show Custom Header is enabled', 'flowq')),
    'editPostUrl' => admin_url('post.php?post='),
    'pages' => array_map(function($page) {
        return array('ID' => $page->ID, 'post_name' => $page->post_name);
    }, $pages)
));
?>

<div class="wrap">
    <div class="page-header-with-back">
        <h1><?php echo esc_html($page_title); ?></h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=flowq')); ?>" class="page-title-action back-button">
            <?php echo esc_html__('← Back to Surveys', 'flowq'); ?>
        </a>
    </div>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="flowq_save_survey">
        <?php wp_nonce_field('flowq_save_survey'); ?>

        <?php if ($is_edit): ?>
            <input type="hidden" name="survey_id" value="<?php echo esc_attr($survey['id']); ?>">
        <?php endif; ?>

        <!-- Survey Details Card -->
        <div class="survey-card">
            <h3 class="card-title"><?php echo esc_html__('Survey Details', 'flowq'); ?></h3>
            <div class="card-content">
                <div class="form-field">
                    <label for="survey_title" class="field-label">
                        <?php echo esc_html__('Survey Title', 'flowq'); ?>
                        <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Enter a descriptive title for your survey. This will be displayed to participants.', 'flowq'); ?>">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                    </label>
                    <input type="text"
                           id="survey_title"
                           name="survey_title"
                           class="full-width-input"
                           value="<?php echo esc_attr($survey['title'] ?? ''); ?>"
                           placeholder="<?php echo esc_attr__('Enter your survey title...', 'flowq'); ?>"
                           required>
                </div>

                <div class="form-field">
                    <label for="survey_description" class="field-label">
                        <?php echo esc_html__('Description', 'flowq'); ?>
                        <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Optional description explaining what this survey is about. Participants will see this before starting.', 'flowq'); ?>">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                    </label>
                    <textarea id="survey_description"
                              name="survey_description"
                              class="full-width-textarea"
                              rows="4"
                              placeholder="<?php echo esc_attr__('Describe what this survey is about...', 'flowq'); ?>"><?php echo esc_textarea($survey['description'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Display Settings Card -->
        <div class="survey-card">
            <h3 class="card-title"><?php echo esc_html__('Display Settings', 'flowq'); ?></h3>
            <div class="card-content">
                <div>
                    <label class="checkbox-label">
                        <input type="checkbox"
                               id="show_header"
                               name="show_header"
                               value="1"
                               <?php checked(!empty($survey['show_header']), true); ?>>
                        <?php echo esc_html__('Show Custom Header and Subtitle', 'flowq'); ?>
                    </label>
                    <p class="field-description">
                        <?php echo esc_html__('Display a custom header and subtitle at the top of the participant form instead of the survey title', 'flowq'); ?>
                    </p>
                </div>

                <div id="header-fields-container" style="display: none; margin-top: 30px;">
                    <div class="form-field">
                        <label for="form_header" class="field-label">
                            <?php echo esc_html__('Survey Form Header', 'flowq'); ?>
                            <span style="color: #dc3232;">*</span>
                            <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Main heading displayed at the top of the participant form (max 255 characters)', 'flowq'); ?>">
                                <span class="dashicons dashicons-editor-help"></span>
                            </span>
                        </label>
                        <input type="text"
                               id="form_header"
                               name="form_header"
                               class="full-width-input"
                               value="<?php echo esc_attr($survey['form_header'] ?? ''); ?>"
                               placeholder="<?php echo esc_attr__('e.g., Help Us Improve Your Experience', 'flowq'); ?>"
                               maxlength="255">
                        <p class="field-description character-count">
                            <span id="header-char-count">0</span> / 255 <?php echo esc_html__('characters', 'flowq'); ?>
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="form_subtitle" class="field-label">
                            <?php echo esc_html__('Survey Form Subtitle', 'flowq'); ?>
                            <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Subtitle displayed below the header (optional)', 'flowq'); ?>">
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
                            <?php echo esc_html__('e.g., Your feedback matters! Take 2 minutes to share your thoughts.', 'flowq'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Settings Card -->
        <div class="survey-card">
            <h3 class="card-title"><?php echo esc_html__('Page Settings', 'flowq'); ?></h3>
            <div class="card-content">
                <div class="form-field">
                    <label for="thank_you_page_slug" class="field-label">
                        <?php echo esc_html__('Thank You Page', 'flowq'); ?>
                        <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Optional: Select an existing published page. After completion, participants get a secure token to access this page (expires in 1 hour).', 'flowq'); ?>">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                    </label>
                    <div class="input-with-action">
                        <?php
                        $current_slug = $survey['thank_you_page_slug'] ?? '';
                        ?>
                        <select id="thank_you_page_slug"
                                name="thank_you_page_slug"
                                class="full-width-select">
                            <option value=""><?php echo esc_html__('-- Select a page --', 'flowq'); ?></option>
                            <?php foreach ($pages as $page):
                                $page_slug = $page->post_name;
                                $page_title = $page->post_title;
                                $is_thank_you = stripos($page_title, 'thank you') !== false || stripos($page_title, 'thankyou') !== false;
                                $display_title = $is_thank_you ? '⭐ ' . $page_title : $page_title;
                                $selected = ($page_slug === $current_slug) ? 'selected' : '';
                            ?>
                                <option value="<?php echo esc_attr($page_slug); ?>"
                                        <?php echo esc_attr($selected); ?>
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
                                <?php echo esc_html__('Edit Page', 'flowq'); ?>
                            </a>
                        <?php else: ?>
                            <a href="#"
                               id="edit-page-button"
                               class="button button-secondary edit-page-button"
                               target="_blank"
                               style="display:none;">
                                <span class="dashicons dashicons-edit"></span>
                                <?php echo esc_html__('Edit Page', 'flowq'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <p class="field-description">
                        <?php echo esc_html__('⭐ Pages marked with a star contain "Thank You" in their title', 'flowq'); ?>
                    </p>
                </div>

                <div class="form-field">
                    <label for="survey_status" class="field-label">
                        <?php echo esc_html__('Status', 'flowq'); ?>
                        <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Draft: Hidden from participants. Published: Live and accessible. Archived: No longer accepting responses.', 'flowq'); ?>">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                    </label>
                    <select id="survey_status" name="survey_status" class="status-select">
                        <option value="draft" <?php selected($survey['status'] ?? 'draft', 'draft'); ?>>
                            <?php echo esc_html__('Draft', 'flowq'); ?>
                        </option>
                        <option value="published" <?php selected($survey['status'] ?? '', 'published'); ?>>
                            <?php echo esc_html__('Published', 'flowq'); ?>
                        </option>
                        <option value="archived" <?php selected($survey['status'] ?? '', 'archived'); ?>>
                            <?php echo esc_html__('Archived', 'flowq'); ?>
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <div class="primary-actions">
                <button type="submit" name="submit" class="button button-primary-custom">
                    <?php echo $is_edit ? esc_html__('Update Survey', 'flowq') : esc_html__('Create Survey', 'flowq'); ?>
                </button>
                <?php if ($is_edit): ?>
                    <a href="<?php echo esc_url($this->get_secure_admin_url('flowq-questions', array('survey_id' => $survey['id']))); ?>" class="button button-secondary-custom">
                        <span class="dashicons dashicons-edit"></span>
                        <?php echo esc_html__('Manage Questions', 'flowq'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <?php if ($is_edit): ?>
        <!-- Survey Actions Card -->
        <div class="survey-card actions-card">
            <h3 class="card-title"><?php echo esc_html__('Survey Actions', 'flowq'); ?></h3>
            <div class="card-content">
                <?php if ($survey['status'] === 'published'): ?>
                    <!-- Analytics Section -->
                    <div class="action-section">
                        <div class="action-item">
                            <a href="<?php echo esc_url($this->get_secure_admin_url('flowq-analytics', array('survey_id' => $survey['id']))); ?>"
                               class="button button-secondary-custom analytics-button">
                                <span class="dashicons dashicons-chart-bar"></span>
                                <?php echo esc_html__('View Analytics', 'flowq'); ?>
                            </a>
                        </div>
                    </div>

                    <!-- Shortcode Section -->
                    <div class="action-section">
                        <label class="field-label"><?php echo esc_html__('Shortcode', 'flowq'); ?></label>
                        <div class="shortcode-container form-field">
                            <input type="text"
                                   id="shortcode-input"
                                   class="shortcode-input"
                                   value="<?php echo esc_attr('[flowq_survey id="' . $survey['id'] . '"]'); ?>"
                                   readonly>
                            <button type="button" class="button copy-button" onclick="copyShortcode()">
                                <span class="dashicons dashicons-clipboard"></span>
                                <?php echo esc_html__('Copy', 'flowq'); ?>
                            </button>
                        </div>
                        <p class="field-description">
                            <?php echo esc_html__('Use this shortcode to embed the survey in any post or page.', 'flowq'); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($survey['status'] !== 'published'): ?>
                <!-- Danger Zone -->
                <div class="action-section danger-zone">
                    <label class="field-label danger-label"><?php echo esc_html__('Danger Zone', 'flowq'); ?></label>
                    <div class="danger-actions">
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=flowq&action=delete&survey_id=' . $survey['id']), 'survey_action')); ?>"
                           class="button button-danger"
                           onclick="return confirm('<?php echo esc_html__('Are you sure you want to delete this survey? This action cannot be undone.', 'flowq'); ?>')">
                            <span class="dashicons dashicons-trash"></span>
                            <?php echo esc_html__('Delete Survey', 'flowq'); ?>
                        </a>
                        <span class="danger-description"><?php echo esc_html__('This action cannot be undone.', 'flowq'); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>


