<?php
/**
 * Questions Management Page Template
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
$is_editing = !empty($question_data);
$page_title = $is_editing ? __('Edit Question', 'flowq') : __('Manage Questions', 'flowq');
?>
<div class="wrap">
    <div class="page-header-with-back">
        <h1><?php echo esc_html($page_title); ?></h1>
        <?php
        // Determine back button URL and text
        if ($selected_survey_id && !empty($survey)) {
            $back_url = $this->get_secure_admin_url('flowq-add', array('survey_id' => $selected_survey_id));
            /* translators: %s: survey title */
            $back_text = sprintf(__('← Back to Edit "%s"', 'flowq'), esc_html($survey['title']));
        } else {
            $back_url = admin_url('admin.php?page=flowq');
            $back_text = __('← Back to Surveys', 'flowq');
        }
        ?>
        <a href="<?php echo esc_url($back_url); ?>" class="page-title-action back-button">
            <?php echo esc_html($back_text); ?>
        </a>
    </div>

    <?php if (empty($surveys)): ?>
        <div class="notice notice-warning">
            <p><?php echo esc_html__('No surveys found. Please create a survey first.', 'flowq'); ?></p>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=flowq-add')); ?>" class="button button-primary"><?php echo esc_html__('Create Survey', 'flowq'); ?></a></p>
        </div>
    <?php else: ?>

        <!-- Survey Filter Bar -->
        <div class="survey-filter-bar">
            <div class="filter-bar-content">
                <div class="filter-section">
                    <label for="survey_id" class="filter-label">
                        <span class="dashicons dashicons-filter"></span>
                        <?php echo esc_html__('Filter by Survey:', 'flowq'); ?>
                    </label>
                    <form method="get" action="" class="filter-form">
                        <input type="hidden" name="page" value="flowq-questions">
                        <select name="survey_id" id="survey_id" class="filter-dropdown">
                            <option value=""><?php echo esc_html__('-- Select a Survey --', 'flowq'); ?></option>
                            <?php foreach ($surveys as $survey_option): ?>
                                <option value="<?php echo esc_attr($survey_option['id']); ?>" <?php selected($selected_survey_id, $survey_option['id']); ?>>
                                    <?php echo esc_html($survey_option['title']); ?>
                                    <span class="survey-status">(<?php echo esc_html(ucfirst($survey_option['status'])); ?>)</span>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <?php if ($selected_survey_id && $survey): ?>
                <div class="action-section">
                    <a href="<?php echo esc_url($this->get_secure_admin_url('flowq-questions', array('survey_id' => $selected_survey_id, 'action' => 'add'))); ?>"
                       class="add-question-button">
                        <span class="dashicons dashicons-plus-alt2"></span>
                        <?php echo esc_html__('Add New Question', 'flowq'); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($survey): ?>
            <?php if ($is_editing): ?>
                <!-- Edit Question Form -->
                <div class="question-form-section">
                    <h2><?php echo esc_html__('Edit Question', 'flowq'); ?></h2>
                    <?php include 'question-form.php'; ?>
                </div>
            <?php else: ?>
                <!-- Questions List -->
                <div class="questions-list-section">
                    <h2><?php echo esc_html__('Questions', 'flowq'); ?></h2>

                    <?php if (empty($questions)): ?>
                        <div class="notice notice-info inline">
                            <p><?php echo esc_html__('No questions added yet.', 'flowq'); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="questions-cards-container">
                            <?php foreach ($questions as $question): ?>
                                <div class="question-card">
                                    <div class="question-card-header">
                                        <div class="question-header-content">
                                            <div class="question-title-row">
                                                <h3 class="question-title"><?php echo esc_html($question['title']); ?></h3>
                                            </div>
                                            <?php if ($question['description']): ?>
                                                <p class="question-description"><?php echo esc_html($question['description']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($question['extra_message']): ?>
                                                <p class="question-extra-message">
                                                    <span class="extra-message-label"><?php echo esc_html__('Optional Message:', 'flowq'); ?></span>
                                                    <?php echo esc_html($question['extra_message']); ?>
                                                </p>
                                            <?php endif; ?>

                                            <div class="question-meta-badges">
                                                <?php if (isset($question['is_required']) && !$question['is_required']): ?>
                                                    <span class="question-badge optional-badge">
                                                        <span class="dashicons dashicons-yes"></span>
                                                        <?php echo esc_html__('Optional', 'flowq'); ?>
                                                    </span>

                                                    <?php
                                                    $skip_question_title = '';
                                                    if (!empty($question['skip_next_question_id'])) {
                                                        foreach ($questions as $q) {
                                                            if ($q['id'] == $question['skip_next_question_id']) {
                                                                $skip_question_title = $q['title'];
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    ?>

                                                    <div class="skip-destination-badge">
                                                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                                                        <strong><?php echo esc_html__('Skip to:', 'flowq'); ?></strong>
                                                        <?php if (!empty($skip_question_title)): ?>
                                                            <span class="skip-destination-text"><?php echo esc_html($skip_question_title); ?></span>
                                                        <?php else: ?>
                                                            <span class="skip-destination-text warning">
                                                                <span class="dashicons dashicons-warning"></span>
                                                                <?php echo esc_html__('End survey', 'flowq'); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="question-badge required-badge">
                                                        <span class="dashicons dashicons-admin-network"></span>
                                                        <?php echo esc_html__('Required', 'flowq'); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (isset($question['is_required']) && !$question['is_required']): ?>
                                                <div class="skip-destination-section">
                                                    <div class="skip-destination-header">
                                                        <span class="dashicons dashicons-admin-generic"></span>
                                                        <strong><?php echo esc_html__('Change Skip Destination:', 'flowq'); ?></strong>
                                                    </div>
                                                    <div class="skip-destination-content">
                                                        <div class="skip-destination-selector" data-question-id="<?php echo esc_attr($question['id']); ?>">
                                                            <select class="skip-destination-dropdown" data-original-value="<?php echo esc_attr($question['skip_next_question_id'] ?? ''); ?>">
                                                                <option value=""><?php echo esc_html__('— End survey —', 'flowq'); ?></option>
                                                                <?php foreach ($questions as $q): ?>
                                                                    <?php if ($q['id'] != $question['id']): ?>
                                                                        <option value="<?php echo esc_attr($q['id']); ?>" <?php selected($question['skip_next_question_id'] ?? '', $q['id']); ?>>
                                                                            <?php echo esc_html($q['title']); ?>
                                                                        </option>
                                                                    <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <button type="button" class="button button-small save-skip-destination" style="display: none;">
                                                                <?php echo esc_html__('Save', 'flowq'); ?>
                                                            </button>
                                                            <button type="button" class="button button-small cancel-skip-destination" style="display: none;">
                                                                <?php echo esc_html__('Cancel', 'flowq'); ?>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="question-actions">
                                            <a href="<?php echo esc_url($this->get_secure_admin_url('flowq-questions', array('survey_id' => $selected_survey_id, 'question_id' => $question['id']))); ?>"
                                               class="action-button edit-button"
                                               title="<?php echo esc_attr__('Edit Question', 'flowq'); ?>">
                                                <span class="dashicons dashicons-edit"></span>
                                            </a>
                                            <?php if ($question['response_count'] == 0): ?>
                                                <button type="button"
                                                        class="action-button delete-button"
                                                        title="<?php echo esc_attr__('Delete Question', 'flowq'); ?>"
                                                        data-question-id="<?php echo esc_attr($question['id']); ?>"
                                                        data-question-title="<?php echo esc_attr($question['title']); ?>"
                                                        data-delete-url="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=flowq_delete_question&survey_id=' . $selected_survey_id . '&question_id=' . $question['id']), 'flowq_question_action')); ?>">
                                                    <span class="dashicons dashicons-trash"></span>
                                                </button>
                                            <?php else: ?>
                                                <span class="action-button delete-button disabled"
                                                      title="<?php echo esc_attr__('Cannot delete question with responses', 'flowq'); ?>">
                                                    <span class="dashicons dashicons-trash"></span>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="question-card-content">
                                        <div class="answers-section">
                                            <h4 class="answers-title">
                                                <span class="dashicons dashicons-list-view"></span>
                                                <?php echo esc_html__('Answer Options', 'flowq'); ?>
                                            </h4>
                                            <?php if (!empty($question['answers'])): ?>
                                                <ol class="answers-list">
                                                    <?php foreach ($question['answers'] as $index => $answer): ?>
                                                        <li class="answer-item">
                                                            <div class="answer-content">
                                                                <div class="answer-text-container">
                                                                    <strong class="answer-text"><?php echo esc_html($answer['answer_text']); ?></strong>
                                                                    <?php if (!empty($answer['answer_value'])): ?>
                                                                        <span class="answer-value"><?php echo esc_html($answer['answer_value']); ?></span>
                                                                    <?php endif; ?>
                                                                </div>

                                                                <div class="answer-meta">
                                                                    <?php
                                                                    $next_question_title = '';
                                                                    if (!empty($answer['next_question_id'])) {
                                                                        foreach ($questions as $q) {
                                                                            if ($q['id'] == $answer['next_question_id']) {
                                                                                $next_question_title = $q['title'];
                                                                                break;
                                                                            }
                                                                        }
                                                                    }
                                                                    ?>

                                                                    <div class="next-question-badge-container">
                                                                        <span class="next-question-badge"
                                                                              data-answer-id="<?php echo esc_attr($answer['id']); ?>"
                                                                              data-original-value="<?php echo esc_attr($answer['next_question_id'] ?? ''); ?>">
                                                                            <?php if (!empty($next_question_title)): ?>
                                                                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                                                                                <?php echo esc_html($next_question_title); ?>
                                                                            <?php else: ?>
                                                                                <span class="dashicons dashicons-flag"></span>
                                                                                <?php echo esc_html__('End Survey', 'flowq'); ?>
                                                                            <?php endif; ?>
                                                                            <span class="edit-indicator dashicons dashicons-edit"></span>
                                                                        </span>

                                                                        <select class="next-question-dropdown hidden"
                                                                                data-answer-id="<?php echo esc_attr($answer['id']); ?>"
                                                                                data-original-value="<?php echo esc_attr($answer['next_question_id'] ?? ''); ?>">
                                                                            <option value=""><?php echo esc_html__('— End survey —', 'flowq'); ?></option>
                                                                            <?php if (count($questions) > 1): ?>
                                                                                <?php foreach ($questions as $q): ?>
                                                                                    <?php if ($q['id'] != $question['id']): ?>
                                                                                        <option value="<?php echo esc_attr($q['id']); ?>" <?php selected($answer['next_question_id'] ?? '', $q['id']); ?>>
                                                                                            <?php echo esc_html($q['title']); ?>
                                                                                        </option>
                                                                                    <?php endif; ?>
                                                                                <?php endforeach; ?>
                                                                            <?php else: ?>
                                                                                <option disabled><?php echo esc_html__('No other questions available', 'flowq'); ?></option>
                                                                            <?php endif; ?>
                                                                        </select>
                                                                    </div>

                                                                    <?php if ($answer['redirect_url']): ?>
                                                                        <div class="redirect-url-badge">
                                                                            <span class="dashicons dashicons-external"></span>
                                                                            <span class="redirect-text"><?php echo esc_html__('Redirects to:', 'flowq'); ?></span>
                                                                            <span class="redirect-value"><?php echo esc_html($answer['redirect_url']); ?></span>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ol>
                                            <?php else: ?>
                                                <p class="no-answers"><?php echo esc_html__('No answers configured', 'flowq'); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!$is_editing && isset($_GET['action']) && sanitize_text_field($_GET['action']) === 'add'): ?>
                <!-- Add Question Form -->
                <hr>
                <div class="question-form-section">
                    <h2><?php echo esc_html__('Add New Question', 'flowq'); ?></h2>
                    <?php $question_data = null; // Reset for new question ?>
                    <?php include 'question-form.php'; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    <?php endif; ?>
</div>

<!-- Delete Question Modal -->
<div id="delete-question-modal" class="question-modal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <span class="dashicons dashicons-warning"></span>
                <?php echo esc_html__('Delete Question', 'flowq'); ?>
            </h3>
            <button type="button" class="modal-close" aria-label="<?php echo esc_attr__('Close', 'flowq'); ?>">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>

        <div class="modal-body">
            <div class="warning-message">
                <p><?php echo esc_html__('Are you sure you want to delete this question?', 'flowq'); ?></p>
                <p class="question-title-display"></p>
                <p class="warning-note">
                    <span class="dashicons dashicons-info"></span>
                    <?php echo esc_html__('This action cannot be undone.', 'flowq'); ?>
                </p>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="button button-secondary modal-cancel">
                <?php echo esc_html__('Cancel', 'flowq'); ?>
            </button>
            <button type="button" class="button button-danger modal-confirm">
                <span class="dashicons dashicons-trash"></span>
                <?php echo esc_html__('Delete Question', 'flowq'); ?>
            </button>
        </div>
    </div>
</div>
