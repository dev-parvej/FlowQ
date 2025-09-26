<?php
/**
 * Question Form Template
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$is_editing = !empty($question_data);
$form_action = $is_editing ? 'update_question' : 'create_question';
?>

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <?php wp_nonce_field('wp_dynamic_survey_question_action'); ?>

    <input type="hidden" name="action" value="wp_dynamic_survey_save_question">
    <input type="hidden" name="question_action" value="<?php echo esc_attr($form_action); ?>">
    <input type="hidden" name="survey_id" value="<?php echo esc_attr($selected_survey_id); ?>">

    <?php if ($is_editing): ?>
        <input type="hidden" name="question_id" value="<?php echo esc_attr($question_data['id']); ?>">
    <?php endif; ?>

    <!-- Question Details Card -->
    <div class="question-card">
        <h3 class="card-title"><?php echo esc_html__('Question Details', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>
        <div class="card-content">
            <div class="form-field">
                <label for="question_title" class="field-label">
                    <?php echo esc_html__('Question Title', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?> *
                    <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('The main question text that participants will see and answer.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </label>
                <input type="text"
                       id="question_title"
                       name="question_title"
                       class="full-width-input"
                       required
                       value="<?php echo esc_attr($question_data['title'] ?? ''); ?>"
                       placeholder="<?php echo esc_attr__('What is your favorite color?', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>" />
            </div>

            <div class="form-field">
                <label for="question_description" class="field-label">
                    <?php echo esc_html__('Description', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Optional additional context or instructions to help participants understand the question.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </label>
                <textarea id="question_description"
                          name="question_description"
                          class="full-width-textarea"
                          rows="3"
                          placeholder="<?php echo esc_attr__('Please choose the option that best describes your preference...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>"><?php echo esc_textarea($question_data['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-field">
                <label for="question_extra_message" class="field-label">
                    <?php echo esc_html__('Extra Message', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Message shown after the participant answers. Use this to provide feedback or next steps.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </label>
                <textarea id="question_extra_message"
                          name="question_extra_message"
                          class="full-width-textarea"
                          rows="3"
                          placeholder="<?php echo esc_attr__('Thank you for your answer! Our team will contact you if needed.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>"><?php echo esc_textarea($question_data['extra_message'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>

    <!-- Answer Options Card -->
    <div class="question-card">
        <div class="card-header">
            <h3 class="card-title"><?php echo esc_html__('Answer Options', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>
            <button type="button" class="button button-secondary-outlined" id="add-answer-option">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php echo esc_html__('Add Answer Option', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
            </button>
        </div>
        <div class="card-content">
            <div id="answer-options-container">
                <?php if ($is_editing && !empty($question_data['answers'])): ?>
                    <?php foreach ($question_data['answers'] as $index => $answer): ?>
                        <div class="answer-option-card">
                            <div class="answer-card-header" data-toggle="collapse">
                                <div class="answer-header-left">
                                    <span class="answer-number"><?php echo esc_html($index + 1); ?></span>
                                    <span class="answer-preview"><?php echo esc_html($answer['answer_text'] ?: __('New Answer Option', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?></span>
                                </div>
                                <div class="answer-header-right">
                                    <button type="button" class="button-icon collapse-toggle" title="<?php echo esc_attr__('Expand/Collapse', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                                    </button>
                                    <button type="button" class="button-icon remove-answer-option" title="<?php echo esc_attr__('Remove', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="answer-card-content collapsible-content">
                                <div class="form-field">
                                    <label class="field-label"><?php echo esc_html__('Answer Text', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                                    <input type="text"
                                           name="answer_text[]"
                                           class="full-width-input"
                                           value="<?php echo esc_attr($answer['answer_text']); ?>"
                                           placeholder="<?php echo esc_attr__('Blue', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>"
                                           required>
                                    <input type="hidden" name="answer_id[]" value="<?php echo esc_attr($answer['id']); ?>">
                                </div>

                                <div class="form-field">
                                    <label class="field-label">
                                        <?php echo esc_html__('Next Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                        <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Choose which question to show next when this answer is selected.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                                            <span class="dashicons dashicons-editor-help"></span>
                                        </span>
                                    </label>
                                    <select name="next_question_id[]" class="full-width-select">
                                        <option value=""><?php echo esc_html__('— End survey or continue to next question —', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                        <?php foreach ($questions as $q): ?>
                                            <?php if ($q['id'] != ($question_data['id'] ?? 0)): ?>
                                                <option value="<?php echo esc_attr($q['id']); ?>" <?php selected($answer['next_question_id'], $q['id']); ?>>
                                                    <?php echo esc_html($q['title']); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-field">
                                    <label class="field-label">
                                        <?php echo esc_html__('Redirect URL', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                        <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Optional: Redirect to an external URL when this answer is selected (instead of continuing the survey).', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                                            <span class="dashicons dashicons-editor-help"></span>
                                        </span>
                                    </label>
                                    <input type="url"
                                           name="answer_redirect_url[]"
                                           class="full-width-input"
                                           value="<?php echo esc_attr($answer['redirect_url']); ?>"
                                           placeholder="<?php echo esc_attr__('https://example.com/thank-you', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <div class="primary-actions">
            <input type="submit" class="button button-primary-custom" value="<?php echo esc_attr($is_editing ? __('Update Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) : __('Create Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>">
        </div>
        <div class="secondary-actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-questions&survey_id=' . $selected_survey_id)); ?>" class="button-link-cancel">
                <?php echo esc_html__('Cancel', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
            </a>
        </div>
    </div>
</form>

<!-- Answer Option Template -->
<div id="answer-option-template" style="display: none;">
    <div class="answer-option-card">
        <div class="answer-card-header" data-toggle="collapse">
            <div class="answer-header-left">
                <span class="answer-number">1</span>
                <span class="answer-preview"><?php echo esc_html__('New Answer Option', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
            </div>
            <div class="answer-header-right">
                <button type="button" class="button-icon collapse-toggle" title="<?php echo esc_attr__('Expand/Collapse', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </button>
                <button type="button" class="button-icon remove-answer-option" title="<?php echo esc_attr__('Remove', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
        </div>
        <div class="answer-card-content collapsible-content expanded">
            <div class="form-field">
                <label class="field-label"><?php echo esc_html__('Answer Text', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                <input type="text"
                       name="answer_text[]"
                       class="full-width-input"
                       placeholder="<?php echo esc_attr__('Blue', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>"
                       required>
                <input type="hidden" name="answer_id[]" value="">
            </div>

            <div class="form-field">
                <label class="field-label">
                    <?php echo esc_html__('Next Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Choose which question to show next when this answer is selected.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </label>
                <select name="next_question_id[]" class="full-width-select">
                    <option value=""><?php echo esc_html__('— End survey or continue to next question —', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                    <?php if (!empty($questions)): ?>
                        <?php foreach ($questions as $q): ?>
                            <option value="<?php echo esc_attr($q['id']); ?>">
                                <?php echo esc_html($q['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-field">
                <label class="field-label">
                    <?php echo esc_html__('Redirect URL', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Optional: Redirect to an external URL when this answer is selected (instead of continuing the survey).', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </label>
                <input type="url"
                       name="answer_redirect_url[]"
                       class="full-width-input"
                       placeholder="<?php echo esc_attr__('https://example.com/thank-you', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize answer options for single choice
    if ($('#answer-options-container').children().length === 0) {
        addAnswerOption();
        addAnswerOption();
    }

    // Add answer option
    $('#add-answer-option').on('click', function() {
        addAnswerOption();
    });

    // Remove answer option
    $(document).on('click', '.remove-answer-option', function(e) {
        e.stopPropagation();
        $(this).closest('.answer-option-card').remove();
        updateAnswerNumbers();
    });

    // Toggle collapse/expand
    $(document).on('click', '.answer-card-header[data-toggle="collapse"]', function(e) {
        // Don't trigger if clicking on buttons
        if ($(e.target).closest('.button-icon').length) {
            return;
        }

        var $card = $(this).closest('.answer-option-card');
        var $content = $card.find('.collapsible-content');
        var $toggle = $card.find('.collapse-toggle .dashicons');

        if ($content.hasClass('expanded')) {
            $content.removeClass('expanded').addClass('collapsed');
            $toggle.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-right-alt2');
        } else {
            $content.removeClass('collapsed').addClass('expanded');
            $toggle.removeClass('dashicons-arrow-right-alt2').addClass('dashicons-arrow-down-alt2');
        }
    });

    // Toggle button click
    $(document).on('click', '.collapse-toggle', function(e) {
        e.stopPropagation();
        $(this).closest('.answer-card-header[data-toggle="collapse"]').trigger('click');
    });

    // Update answer preview when typing
    $(document).on('input', 'input[name="answer_text[]"]', function() {
        var $input = $(this);
        var $card = $input.closest('.answer-option-card');
        var $preview = $card.find('.answer-preview');
        var text = $input.val().trim();

        if (text) {
            $preview.text(text);
        } else {
            $preview.text('<?php echo esc_js(__('New Answer Option', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>');
        }
    });

    function addAnswerOption(defaultText = '') {
        var template = $('#answer-option-template').html();
        var answerCard = $(template);

        if (defaultText) {
            answerCard.find('input[name="answer_text[]"]').val(defaultText);
            answerCard.find('.answer-preview').text(defaultText);
        }

        $('#answer-options-container').append(answerCard);
        updateAnswerNumbers();
    }

    function updateAnswerNumbers() {
        $('#answer-options-container .answer-option-card').each(function(index) {
            $(this).find('.answer-number').text(index + 1);
        });
    }

});
</script>

<style>
/* Question Cards */
.question-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    margin-bottom: 20px;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}

.question-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.card-title {
    background: #f6f7f7;
    margin: 0;
    padding: 15px 20px;
    font-size: 16px;
    font-weight: 600;
    color: #1d2327;
}

.card-header {
    background: #f6f7f7;
    border-bottom: 1px solid #c3c4c7;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-content {
    padding: 20px;
}

/* Form Fields */
.form-field {
    margin-bottom: 20px;
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

.full-width-input,
.full-width-textarea,
.full-width-select {
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
.full-width-textarea:focus,
.full-width-select:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
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
    white-space: normal;
    width: max-content;
    max-width: 300px;
    z-index: 999999;
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
    transform: translateX(-1px);
    border: 5px solid transparent;
    border-top-color: #1d2327;
    z-index: 999999;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.help-tooltip:hover:before,
.help-tooltip:hover:after {
    opacity: 1;
    visibility: visible;
}

/* Answer Option Cards */
.answer-option-card {
    border: 1px solid #ddd;
    border-radius: 6px;
    background: #fafafa;
    margin-bottom: 16px;
    transition: border-color 0.2s ease;
}

.answer-option-card:hover {
    border-color: #2271b1;
}

.answer-card-header {
    background: #f0f0f1;
    border-bottom: 1px solid #ddd;
    padding: 10px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.answer-card-header:hover {
    background: #e8e8e9;
}

.answer-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-grow: 1;
}

.answer-header-right {
    display: flex;
    align-items: center;
    gap: 4px;
}

.answer-preview {
    font-weight: 500;
    color: #1d2327;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 300px;
}

.answer-number {
    background: #2271b1;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
}

.answer-actions {
    display: flex;
    gap: 4px;
}

.button-icon {
    background: none;
    border: none;
    padding: 4px;
    border-radius: 3px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.button-icon:hover {
    background: #ddd;
}

.button-icon.remove-answer-option:hover {
    background: #dc2626;
    color: white;
}

.answer-card-content {
    padding: 15px;
}

/* Collapsible Content */
.collapsible-content {
    transition: all 0.3s ease;
    overflow: hidden;
}

.collapsible-content.expanded {
    max-height: 1000px;
    opacity: 1;
}

.collapsible-content.collapsed {
    max-height: 0;
    opacity: 0;
    padding: 0 15px;
}

.collapse-toggle .dashicons {
    transition: transform 0.2s ease;
}

/* Buttons */
.button-secondary-outlined {
    background: #fff !important;
    border: 1px solid #2271b1 !important;
    color: #2271b1 !important;
    padding: 6px 12px !important;
    font-size: 13px !important;
    border-radius: 4px !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    transition: all 0.2s ease !important;
}

.button-secondary-outlined:hover {
    background: #f0f6fc !important;
    border-color: #135e96 !important;
    color: #135e96 !important;
}

.button-primary-custom {
    background: #2271b1 !important;
    border-color: #2271b1 !important;
    color: #fff !important;
    padding: 10px 20px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    border-radius: 6px !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
}

.button-primary-custom:hover {
    background: #135e96 !important;
    border-color: #135e96 !important;
    transform: translateY(-1px);
}

/* Action Buttons */
.action-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 6px;
}

.button-link-cancel {
    color: #646970;
    text-decoration: none;
    font-size: 13px;
    transition: color 0.2s ease;
}

.button-link-cancel:hover {
    color: #2271b1;
    text-decoration: underline;
}

/* Legacy styles for compatibility */
.survey-selection-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 6px;
}

.survey-info-section {
    background: #f0f6fc;
    border: 1px solid #c3dbf0;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 6px;
}

.questions-list-section {
    margin-top: 20px;
}

.question-form-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    margin-top: 20px;
    border-radius: 6px;
}

/* Responsive Design */
@media screen and (max-width: 782px) {
    .action-buttons {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }

    .card-header {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }

    .answer-card-header {
        flex-direction: column;
        gap: 8px;
        align-items: flex-start;
    }
}
</style>