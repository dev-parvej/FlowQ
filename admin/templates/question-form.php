<?php
/**
 * Question Form Template
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$is_editing = !empty($question_data);
$form_action = $is_editing ? 'update_question' : 'create_question';
?>

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <?php wp_nonce_field('flowq_question_action'); ?>

    <input type="hidden" name="action" value="flowq_save_question">
    <input type="hidden" name="question_action" value="<?php echo esc_attr($form_action); ?>">
    <input type="hidden" name="survey_id" value="<?php echo esc_attr($selected_survey_id); ?>">

    <?php if ($is_editing): ?>
        <input type="hidden" name="question_id" value="<?php echo esc_attr($question_data['id']); ?>">
    <?php endif; ?>

    <!-- Question Details Card -->
    <div class="question-card">
        <h3 class="card-title"><?php echo esc_html__('Question Details', 'flowq'); ?></h3>
        <div class="card-content">
            <div class="form-field">
                <label for="question_title" class="field-label">
                    <?php echo esc_html__('Question Title', 'flowq'); ?> *
                </label>
                <input type="text"
                       id="question_title"
                       name="question_title"
                       class="full-width-input"
                       required
                       value="<?php echo esc_attr($question_data['title'] ?? ''); ?>"
                       placeholder="<?php echo esc_attr__('What is your favorite color?', 'flowq'); ?>" />
            </div>

            <div class="form-field">
                <label for="question_description" class="field-label">
                    <?php echo esc_html__('Description', 'flowq'); ?>
                </label>
                <textarea id="question_description"
                          name="question_description"
                          class="full-width-textarea"
                          rows="3"
                          placeholder="<?php echo esc_attr__('Please choose the option that best describes your preference...', 'flowq'); ?>"><?php echo esc_textarea($question_data['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-field">
                <label for="question_extra_message" class="field-label">
                    <?php echo esc_html__('Extra Message', 'flowq'); ?>
                    <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Message shown after the participant answers. Use this to provide feedback or next steps.', 'flowq'); ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </label>
                <textarea id="question_extra_message"
                          name="question_extra_message"
                          class="full-width-textarea"
                          rows="3"
                          placeholder="<?php echo esc_attr__('Thank you for your answer! Our team will contact you if needed.', 'flowq'); ?>"><?php echo esc_textarea($question_data['extra_message'] ?? ''); ?></textarea>
            </div>

            <div>
                <label class="checkbox-field">
                    <input type="checkbox"
                           id="question_is_required"
                           name="question_is_required"
                           value="1"
                           <?php checked(($question_data['is_required'] ?? 1), 1); ?>>
                    <span class="checkbox-label">
                        <?php echo esc_html__('This question is required', 'flowq'); ?>
                        <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('When unchecked, participants can skip this question using a skip button.', 'flowq'); ?>">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                    </span>
                </label>
            </div>

            <div class="form-field skip-destination-field" style="display: none; margin-top: 20px;">
                <label for="question_skip_next_question_id" class="field-label">
                    <?php echo esc_html__('Skip Destination', 'flowq'); ?>
                    <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Choose which question to show when participants skip this question.', 'flowq'); ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </label>
                <select id="question_skip_next_question_id" name="question_skip_next_question_id" class="full-width-select">
                    <option value=""><?php echo esc_html__('— End survey —', 'flowq'); ?></option>
                    <?php if (!empty($questions)): ?>
                        <?php foreach ($questions as $q): ?>
                            <?php if ($q['id'] != ($question_data['id'] ?? 0)): ?>
                                <option value="<?php echo esc_attr($q['id']); ?>" <?php selected(($question_data['skip_next_question_id'] ?? ''), $q['id']); ?>>
                                    <?php echo esc_html($q['title']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Answer Options Card -->
    <div class="question-card">
        <div class="card-header">
            <h3 class="card-title"><?php echo esc_html__('Answer Options', 'flowq'); ?></h3>
            <button type="button" class="button button-secondary-outlined" id="add-answer-option">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php echo esc_html__('Add Answer Option', 'flowq'); ?>
            </button>
        </div>
        <div class="card-content">
            <div id="answer-options-container">
                <?php if ($is_editing && !empty($question_data['answers'])): ?>
                    <?php foreach ($question_data['answers'] as $index => $answer): ?>
                        <div class="answer-option-card">
                            <div class="answer-card-header" data-toggle="collapse">
                                <div class="answer-header-left">
                                    <span class="answer-preview"><?php echo esc_html($answer['answer_text'] ?: __('New Answer Option', 'flowq')); ?></span>
                                </div>
                                <div class="answer-header-right">
                                    <button type="button" class="button-icon collapse-toggle" title="<?php echo esc_attr__('Expand/Collapse', 'flowq'); ?>">
                                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                                    </button>
                                    <button type="button" class="button-icon remove-answer-option" title="<?php echo esc_attr__('Remove', 'flowq'); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="answer-card-content collapsible-content">
                                <div class="form-field">
                                    <label class="field-label"><?php echo esc_html__('Answer Text', 'flowq'); ?></label>
                                    <input type="text"
                                           name="answer_text[]"
                                           class="full-width-input"
                                           value="<?php echo esc_attr($answer['answer_text']); ?>"
                                           placeholder="<?php echo esc_attr__('Blue', 'flowq'); ?>"
                                           required>
                                    <input type="hidden" name="answer_id[]" value="<?php echo esc_attr($answer['id']); ?>">
                                </div>

                                <div class="form-field">
                                    <label class="field-label">
                                        <?php echo esc_html__('Next Question', 'flowq'); ?>
                                        <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Choose which question to show next when this answer is selected.', 'flowq'); ?>">
                                            <span class="dashicons dashicons-editor-help"></span>
                                        </span>
                                    </label>
                                    <select name="next_question_id[]" class="full-width-select">
                                        <option value=""><?php echo esc_html__('— End survey or continue to next question —', 'flowq'); ?></option>
                                        <?php foreach ($questions as $q): ?>
                                            <?php if ($q['id'] != ($question_data['id'] ?? 0)): ?>
                                                <option value="<?php echo esc_attr($q['id']); ?>" <?php selected($answer['next_question_id'], $q['id']); ?>>
                                                    <?php echo esc_html($q['title']); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Advanced Options Toggle -->
                                <div class="advanced-options-section">
                                    <button type="button" class="advanced-options-toggle" data-collapsed="true">
                                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                                        <?php echo esc_html__('Advanced Options', 'flowq'); ?>
                                    </button>
                                    <div class="advanced-options-content" style="display: none;">
                                        <div class="form-field">
                                            <label class="field-label">
                                                <?php echo esc_html__('Redirect URL', 'flowq'); ?>
                                                <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Optional: Redirect to an external URL when this answer is selected (instead of continuing the survey).', 'flowq'); ?>">
                                                    <span class="dashicons dashicons-editor-help"></span>
                                                </span>
                                            </label>
                                            <input type="url"
                                                   name="answer_redirect_url[]"
                                                   class="full-width-input"
                                                   value="<?php echo esc_attr($answer['redirect_url']); ?>"
                                                   placeholder="<?php echo esc_attr__('https://example.com/thank-you', 'flowq'); ?>">
                                        </div>
                                    </div>
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
            <input type="submit" class="button button-primary-custom" value="<?php echo esc_attr($is_editing ? __('Update Question', 'flowq') : __('Create Question', 'flowq')); ?>">
        </div>
        <div class="secondary-actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=flowq-questions&survey_id=' . $selected_survey_id)); ?>" class="button-link-cancel">
                <?php echo esc_html__('Cancel', 'flowq'); ?>
            </a>
        </div>
    </div>
</form>

<!-- Answer Option Template -->
<div id="answer-option-template" style="display: none;">
    <div class="answer-option-card">
        <div class="answer-card-header" data-toggle="collapse">
            <div class="answer-header-left">
                <span class="answer-preview"><?php echo esc_html__('New Answer Option', 'flowq'); ?></span>
            </div>
            <div class="answer-header-right">
                <button type="button" class="button-icon collapse-toggle" title="<?php echo esc_attr__('Expand/Collapse', 'flowq'); ?>">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </button>
                <button type="button" class="button-icon remove-answer-option" title="<?php echo esc_attr__('Remove', 'flowq'); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
        </div>
        <div class="answer-card-content collapsible-content expanded">
            <div class="form-field">
                <label class="field-label"><?php echo esc_html__('Answer Text', 'flowq'); ?></label>
                <input type="text"
                       name="answer_text[]"
                       class="full-width-input"
                       placeholder="<?php echo esc_attr__('Blue', 'flowq'); ?>"
                       required>
                <input type="hidden" name="answer_id[]" value="">
            </div>

            <div class="form-field">
                <label class="field-label">
                    <?php echo esc_html__('Next Question', 'flowq'); ?>
                    <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Choose which question to show next when this answer is selected.', 'flowq'); ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </label>
                <select name="next_question_id[]" class="full-width-select">
                    <option value=""><?php echo esc_html__('— End survey or continue to next question —', 'flowq'); ?></option>
                    <?php if (!empty($questions)): ?>
                        <?php foreach ($questions as $q): ?>
                            <option value="<?php echo esc_attr($q['id']); ?>">
                                <?php echo esc_html($q['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Advanced Options Toggle -->
            <div class="advanced-options-section">
                <button type="button" class="advanced-options-toggle" data-collapsed="true">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                    <?php echo esc_html__('Advanced Options', 'flowq'); ?>
                </button>
                <div class="advanced-options-content" style="display: none;">
                    <div class="form-field">
                        <label class="field-label">
                            <?php echo esc_html__('Redirect URL', 'flowq'); ?>
                            <span class="help-tooltip" data-tooltip="<?php echo esc_attr__('Optional: Redirect to an external URL when this answer is selected (instead of continuing the survey).', 'flowq'); ?>">
                                <span class="dashicons dashicons-editor-help"></span>
                            </span>
                        </label>
                        <input type="url"
                               name="answer_redirect_url[]"
                               class="full-width-input"
                               placeholder="<?php echo esc_attr__('https://example.com/thank-you', 'flowq'); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
