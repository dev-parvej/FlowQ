<?php
/**
 * Survey Builder Template
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="survey-builder-wrapper">
    <div class="survey-builder-header">
        <h2><?php echo esc_html($survey['title']); ?></h2>
        <div class="builder-actions">
            <button type="button" class="button button-secondary" id="preview-survey" data-survey-id="<?php echo esc_attr($survey['id']); ?>">
                <span class="dashicons dashicons-visibility"></span>
                <?php echo esc_html__('Preview Survey', 'flowq'); ?>
            </button>
            <button type="button" class="button button-primary" id="add-question">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php echo esc_html__('Add Question', 'flowq'); ?>
            </button>
        </div>
    </div>

    <div class="survey-builder-content">
        <?php if (empty($questions)): ?>
            <div class="no-questions-placeholder">
                <div class="placeholder-content">
                    <span class="dashicons dashicons-clipboard"></span>
                    <h3><?php echo esc_html__('No questions yet', 'flowq'); ?></h3>
                    <p><?php echo esc_html__('Start building your survey by adding your first question.', 'flowq'); ?></p>
                    <button type="button" class="button button-primary" id="add-first-question">
                        <?php echo esc_html__('Add First Question', 'flowq'); ?>
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="questions-container" id="questions-sortable">
                <?php foreach ($questions as $question): ?>
                    <div class="question-item" data-question-id="<?php echo esc_attr($question['id']); ?>">
                        <div class="question-header">
                            <div class="question-drag-handle">
                                <span class="dashicons dashicons-move"></span>
                            </div>
                            <div class="question-info">
                                <div class="question-type">
                                    <span class="dashicons dashicons-list-view"></span>
                                    <?php echo esc_html__('Question', 'flowq'); ?>
                                </div>
                                <div class="question-order">
                                    #<?php echo esc_html($question['question_order']); ?>
                                </div>
                            </div>
                            <div class="question-actions">
                                <button type="button" class="button button-small edit-question"
                                        data-question-id="<?php echo esc_attr($question['id']); ?>">
                                    <?php echo esc_html__('Edit', 'flowq'); ?>
                                </button>
                                <button type="button" class="button button-small button-link-delete delete-question"
                                        data-question-id="<?php echo esc_attr($question['id']); ?>">
                                    <?php echo esc_html__('Delete', 'flowq'); ?>
                                </button>
                            </div>
                        </div>

                        <div class="question-content">
                            <div class="question-title">
                                <strong><?php echo esc_html($question['title']); ?></strong>
                                <?php if ($question['is_required']): ?>
                                    <span class="required-indicator">*</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($question['description'])): ?>
                                <div class="question-description">
                                    <?php echo esc_html($question['description']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($question['answers'])): ?>
                                <div class="question-answers">
                                    <div class="answers-list" data-question-id="<?php echo esc_attr($question['id']); ?>">
                                        <?php foreach ($question['answers'] as $answer): ?>
                                            <div class="answer-item" data-answer-id="<?php echo esc_attr($answer['id']); ?>">
                                                <span class="answer-drag-handle dashicons dashicons-move"></span>
                                                <span class="answer-text"><?php echo esc_html($answer['answer_text']); ?></span>

                                                <?php if ($answer['next_question_id'] || $answer['redirect_url']): ?>
                                                    <span class="answer-routing">
                                                        <?php if ($answer['next_question_id']): ?>
                                                            → Q<?php echo esc_html($answer['next_question_id']); ?>
                                                        <?php endif; ?>

                                                        <?php if ($answer['redirect_url']): ?>
                                                            🔗
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endif; ?>

                                                <div class="answer-actions">
                                                    <button type="button" class="button button-small edit-answer"
                                                            data-answer-id="<?php echo esc_attr($answer['id']); ?>"
                                                            data-question-id="<?php echo esc_attr($question['id']); ?>">
                                                        <?php echo esc_html__('Edit', 'flowq'); ?>
                                                    </button>
                                                    <button type="button" class="button button-small button-link-delete delete-answer"
                                                            data-answer-id="<?php echo esc_attr($answer['id']); ?>">
                                                        <?php echo esc_html__('Delete', 'flowq'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <button type="button" class="button button-secondary add-answer"
                                            data-question-id="<?php echo esc_attr($question['id']); ?>">
                                        <span class="dashicons dashicons-plus-alt2"></span>
                                        <?php echo esc_html__('Add Answer', 'flowq'); ?>
                                    </button>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="add-question-button-container">
                <button type="button" class="button button-primary" id="add-question-bottom">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php echo esc_html__('Add Another Question', 'flowq'); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Question Modal Placeholder -->
<div id="question-modal" style="display: none;">
    <!-- Question editing modal content will be loaded here -->
</div>

<!-- Answer Modal Placeholder -->
<div id="answer-modal" style="display: none;">
    <!-- Answer editing modal content will be loaded here -->
</div>

<!-- Preview Modal -->
<div id="preview-modal" style="display: none;">
    <div class="preview-modal-content">
        <div class="preview-header">
            <h3><?php echo esc_html__('Survey Preview', 'flowq'); ?></h3>
            <button type="button" class="preview-close">&times;</button>
        </div>
        <div class="preview-body">
            <!-- Preview content will be loaded here -->
        </div>
    </div>
</div>

<?php
// Add inline script for survey builder initialization using wp_add_inline_script
$survey_init_script = sprintf(
    'jQuery(document).ready(function($) { if (typeof flowqSurveyBuilder !== "undefined") { flowqSurveyBuilder.init(%s); } });',
    wp_json_encode(array(
        'survey_id' => $survey['id'],
        'questions' => $questions
    ))
);
wp_add_inline_script('flowq-admin', $survey_init_script);
?>
