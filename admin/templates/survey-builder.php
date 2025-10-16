<?php
/**
 * Survey Builder Template
 *
 * @package WP_Dynamic_Survey
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
                <?php echo esc_html__('Preview Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
            </button>
            <button type="button" class="button button-primary" id="add-question">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php echo esc_html__('Add Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
            </button>
        </div>
    </div>

    <div class="survey-builder-content">
        <?php if (empty($questions)): ?>
            <div class="no-questions-placeholder">
                <div class="placeholder-content">
                    <span class="dashicons dashicons-clipboard"></span>
                    <h3><?php echo esc_html__('No questions yet', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>
                    <p><?php echo esc_html__('Start building your survey by adding your first question.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                    <button type="button" class="button button-primary" id="add-first-question">
                        <?php echo esc_html__('Add First Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
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
                                    <?php echo esc_html__('Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                </div>
                                <div class="question-order">
                                    #<?php echo esc_html($question['question_order']); ?>
                                </div>
                            </div>
                            <div class="question-actions">
                                <button type="button" class="button button-small edit-question"
                                        data-question-id="<?php echo esc_attr($question['id']); ?>">
                                    <?php echo esc_html__('Edit', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                </button>
                                <button type="button" class="button button-small button-link-delete delete-question"
                                        data-question-id="<?php echo esc_attr($question['id']); ?>">
                                    <?php echo esc_html__('Delete', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
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
                                                        <?php echo esc_html__('Edit', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                                    </button>
                                                    <button type="button" class="button button-small button-link-delete delete-answer"
                                                            data-answer-id="<?php echo esc_attr($answer['id']); ?>">
                                                        <?php echo esc_html__('Delete', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <button type="button" class="button button-secondary add-answer"
                                            data-question-id="<?php echo esc_attr($question['id']); ?>">
                                        <span class="dashicons dashicons-plus-alt2"></span>
                                        <?php echo esc_html__('Add Answer', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
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
                    <?php echo esc_html__('Add Another Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
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
            <h3><?php echo esc_html__('Survey Preview', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>
            <button type="button" class="preview-close">&times;</button>
        </div>
        <div class="preview-body">
            <!-- Preview content will be loaded here -->
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize survey builder
    if (typeof WPSurveyBuilder !== 'undefined') {
        WPSurveyBuilder.init(<?php echo json_encode(array(
            'survey_id' => $survey['id'],
            'questions' => $questions
        )); ?>);
    }
});
</script>

<style>
.survey-builder-wrapper {
    max-width: 100%;
    margin: 20px 0;
}

.survey-builder-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid #ddd;
}

.builder-actions {
    display: flex;
    gap: 10px;
}

.no-questions-placeholder {
    text-align: center;
    padding: 60px 20px;
    border: 2px dashed #ddd;
    border-radius: 8px;
    background: #fafafa;
}

.placeholder-content .dashicons {
    font-size: 48px;
    color: #ccd0d4;
    margin-bottom: 15px;
}

.questions-container {
    margin-bottom: 30px;
}

.question-item {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: box-shadow 0.2s;
}

.question-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.question-header {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
    border-radius: 6px 6px 0 0;
}

.question-drag-handle {
    cursor: move;
    margin-right: 15px;
    color: #666;
}

.question-info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 15px;
}

.question-type {
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: 600;
    color: #2271b1;
}

.question-order {
    color: #666;
    font-size: 14px;
}

.question-actions {
    display: flex;
    gap: 5px;
}

.question-title {
    font-size: 16px;
    margin-bottom: 10px;
}

.required-indicator {
    color: #d63638;
    font-weight: bold;
}

.question-description {
    color: #666;
    margin-bottom: 15px;
    font-style: italic;
}

.answers-list {
    margin-bottom: 15px;
}

.answer-item {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    background: #f9f9f9;
    border: 1px solid #eee;
    border-radius: 4px;
    margin-bottom: 8px;
    gap: 10px;
}

.answer-drag-handle {
    cursor: move;
    color: #666;
}

.answer-text {
    flex: 1;
}

.answer-routing {
    font-size: 12px;
    color: #666;
    background: #e8f4fd;
    padding: 2px 6px;
    border-radius: 3px;
}

.answer-actions {
    display: flex;
    gap: 5px;
}

.text-question-info {
    margin-top: 15px;
    padding: 10px;
    background: #f0f6fc;
    border-radius: 4px;
    border-left: 4px solid #2271b1;
}

.add-question-button-container {
    text-align: center;
    padding: 30px 0;
}

/* Modal Styles */
#preview-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 100000;
}

.preview-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    width: 90%;
    max-width: 800px;
    max-height: 90%;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #ddd;
}

.preview-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.preview-body {
    padding: 20px;
    max-height: 60vh;
    overflow-y: auto;
}

/* Sortable helpers */
.ui-sortable-helper {
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    transform: rotate(2deg);
}

.ui-sortable-placeholder {
    border: 2px dashed #2271b1;
    background: rgba(34, 113, 177, 0.1);
    visibility: visible !important;
    height: 100px;
    margin-bottom: 20px;
    border-radius: 6px;
}
</style>