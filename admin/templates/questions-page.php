<?php
/**
 * Questions Management Page Template
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
$is_editing = !empty($question_data);
$page_title = $is_editing ? __('Edit Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) : __('Manage Questions', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
?>
<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>

    <?php if (empty($surveys)): ?>
        <div class="notice notice-warning">
            <p><?php echo esc_html__('No surveys found. Please create a survey first.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-add')); ?>" class="button button-primary"><?php echo esc_html__('Create Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></a></p>
        </div>
    <?php else: ?>

        <!-- Survey Selection -->
        <div class="survey-selection-section">
            <h2><?php echo esc_html__('Select Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>
            <form method="get" action="">
                <input type="hidden" name="page" value="wp-dynamic-surveys-questions">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="survey_id"><?php echo esc_html__('Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                            </th>
                            <td>
                                <select name="survey_id" id="survey_id" class="regular-text">
                                    <option value=""><?php echo esc_html__('-- Select a Survey --', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                    <?php foreach ($surveys as $survey_option): ?>
                                        <option value="<?php echo esc_attr($survey_option['id']); ?>" <?php selected($selected_survey_id, $survey_option['id']); ?>>
                                            <?php echo esc_html($survey_option['title']); ?>
                                            (<?php echo esc_html(ucfirst($survey_option['status'])); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="submit" class="button button-secondary" value="<?php echo esc_attr__('Select Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>

        <?php if ($survey): ?>
            <hr>

            <!-- Survey Info -->
            <div class="survey-info-section">
                <h2><?php echo esc_html__('Survey:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?> <?php echo esc_html($survey['title']); ?></h2>
                <p class="description"><?php echo esc_html($survey['description']); ?></p>
                <p><strong><?php echo esc_html__('Status:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></strong> <?php echo esc_html(ucfirst($survey['status'])); ?></p>
            </div>

            <?php if ($is_editing): ?>
                <!-- Edit Question Form -->
                <div class="question-form-section">
                    <h2><?php echo esc_html__('Edit Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>
                    <?php include 'question-form.php'; ?>
                </div>
            <?php else: ?>
                <!-- Questions List -->
                <div class="questions-list-section">
                    <h2><?php echo esc_html__('Questions', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>

                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-questions&survey_id=' . $selected_survey_id . '&action=add')); ?>" class="button button-primary">
                            <?php echo esc_html__('Add New Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </a>
                    </p>

                    <?php if (empty($questions)): ?>
                        <div class="notice notice-info inline">
                            <p><?php echo esc_html__('No questions added yet.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                        </div>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width: 40%;"><?php echo esc_html__('Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></th>
                                    <th><?php echo esc_html__('Answers', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></th>
                                    <th style="width: 150px;"><?php echo esc_html__('Actions', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($questions as $question): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo esc_html($question['title']); ?></strong>
                                            <?php if ($question['description']): ?>
                                                <br><small class="description"><?php echo esc_html($question['description']); ?></small>
                                            <?php endif; ?>
                                            <?php if ($question['extra_message']): ?>
                                                <br><em class="description" style="color: #555;"><b>Optional Message: </b><?php echo esc_html($question['extra_message']); ?></em>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($question['answers'])): ?>
                                                <div class="answers-list">
                                                    <?php foreach ($question['answers'] as $answer): ?>
                                                        <div class="answer-item" style="margin-bottom: 8px; padding: 6px; background: #f8f9fa; border-left: 3px solid #007cba; font-size: 12px;">
                                                            <strong><?php echo esc_html($answer['answer_text']); ?></strong>
                                                            <div class="next-question-controls">
                                                                <span class="next-question-label"><?php echo esc_html__('Next Question:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
                                                                <select class="next-question-dropdown"
                                                                        data-answer-id="<?php echo esc_attr($answer['id']); ?>"
                                                                        data-original-value="<?php echo esc_attr($answer['next_question_id'] ?? ''); ?>">
                                                                    <option value=""><?php echo esc_html__('-- Select Next Question --', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                                                    <?php if (count($questions) > 1): ?>
                                                                        <?php foreach ($questions as $q): ?>
                                                                            <option value="<?php echo esc_attr($q['id']); ?>" <?php selected($answer['next_question_id'] ?? '', $q['id']); ?>>
                                                                                <?php echo esc_html($q['title']); ?>  <?php echo $question['id'] == $q['id'] ? '<span style="color: red"><- Current question</span>' : '' ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    <?php else: ?>
                                                                        <option disabled><?php echo esc_html__('No other questions available', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                                                    <?php endif; ?>
                                                                </select>
                                                            </div>
                                                            <?php if ($answer['redirect_url']): ?>
                                                                <br><span style="color: #d63384;">🔗 <?php echo esc_html($answer['redirect_url']); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="description"><?php echo esc_html__('No answers configured', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-questions&survey_id=' . $selected_survey_id . '&question_id=' . $question['id'])); ?>" class="button button-small">
                                                <?php echo esc_html__('Edit', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                            </a>
                                            <?php if ($question['response_count'] == 0): ?>
                                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=wp_dynamic_survey_delete_question&survey_id=' . $selected_survey_id . '&question_id=' . $question['id']), 'wp_dynamic_survey_question_action')); ?>"
                                                    class="button button-small button-link-delete"
                                                    onclick="return confirm('<?php echo esc_attr__('Are you sure you want to delete this question?', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>')">
                                                    <?php echo esc_html__('Delete', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="button button-small button-disabled" title="<?php echo esc_attr__('Cannot delete question with responses', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                                                    <?php echo esc_html__('Delete', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!$is_editing && isset($_GET['action']) && $_GET['action'] === 'add'): ?>
                <!-- Add Question Form -->
                <hr>
                <div class="question-form-section">
                    <h2><?php echo esc_html__('Add New Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>
                    <?php $question_data = null; // Reset for new question ?>
                    <?php include 'question-form.php'; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Auto-submit survey selection
    $('#survey_id').on('change', function() {
        if ($(this).val()) {
            $(this).closest('form').submit();
        }
    });

    // Smooth scroll to forms
    function scrollToElement(selector, offset = 20) {
        const element = $(selector);
        if (element.length) {
            $('html, body').animate({
                scrollTop: element.offset().top - offset
            }, 800);
        }
    }

    // Auto-scroll to add question form if URL has action=add
    if (window.location.search.includes('action=add')) {
        setTimeout(function() {
            scrollToElement('.question-form-section');
        }, 100);
    }

    // Auto-scroll to edit form if we're editing a question
    if (window.location.search.includes('question_id=') && !window.location.search.includes('action=add')) {
        setTimeout(function() {
            scrollToElement('.question-form-section');
        }, 100);
    }

    // Handle add question button clicks with visual feedback
    $(document).on('click', 'a[href*="action=add"]', function(e) {
        const $button = $(this);
        const originalText = $button.text();

        // Add loading state
        $button.addClass('loading').css('position', 'relative');
        $button.text('Loading...');

        // Navigate after brief delay
        setTimeout(function() {
            window.location.href = $button.attr('href');
        }, 200);
    });

    // Handle edit button clicks with visual feedback
    $(document).on('click', 'a[href*="question_id="]', function(e) {
        const href = $(this).attr('href');
        if (!href.includes('action=delete')) {
            const $button = $(this);
            const originalText = $button.text();

            // Add loading state
            $button.addClass('loading').css('position', 'relative');
            $button.text('Loading...');

            // Navigate after brief delay
            setTimeout(function() {
                window.location.href = href;
            }, 200);
        }
    });
    // Add highlighting effect to form sections
    setTimeout(function() {
        $('.question-form-section').addClass('highlight-form');
        setTimeout(function() {
            $('.question-form-section').removeClass('highlight-form');
        }, 2000);
    }, 500);

    // Handle inline next question dropdown changes
    $(document).on('change', '.next-question-dropdown', function() {
        const $dropdown = $(this);
        const answerId = $dropdown.data('answer-id');
        const nextQuestionId = $dropdown.val();
        const originalValue = $dropdown.data('original-value');

        // Show loading state
        $dropdown.prop('disabled', true).addClass('loading');

        // Prepare AJAX data
        const ajaxData = {
            action: 'wp_dynamic_survey_update_answer_next_question',
            nonce: '<?php echo wp_create_nonce('wp_dynamic_survey_admin_nonce'); ?>',
            answer_id: answerId,
            next_question_id: nextQuestionId
        };

        // Make AJAX request
        $.post(ajaxurl, ajaxData)
            .done(function(response) {
                if (response.success) {
                    // Update the original value to new selection
                    $dropdown.data('original-value', nextQuestionId);

                    // Show success feedback
                    showNotification('success', response.data.message);

                    // Add visual success indicator
                    $dropdown.addClass('success-updated');
                    setTimeout(function() {
                        $dropdown.removeClass('success-updated');
                    }, 2000);
                } else {
                    // Revert to original value on error
                    $dropdown.val(originalValue);
                    showNotification('error', response.data || 'Update failed');
                }
            })
            .fail(function() {
                // Revert to original value on failure
                $dropdown.val(originalValue);
                showNotification('error', 'Network error occurred');
            })
            .always(function() {
                // Remove loading state
                $dropdown.prop('disabled', false).removeClass('loading');
            });
    });

    // Show notification function
    function showNotification(type, message) {
        const $notification = $('<div class="notice notice-' + (type === 'success' ? 'success' : 'error') + ' is-dismissible inline-notification"><p>' + message + '</p></div>');

        // Insert notification at top of page
        $('.wrap h1').after($notification);

        // Auto-dismiss after 3 seconds
        setTimeout(function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);

        // Handle manual dismiss
        $notification.on('click', '.notice-dismiss', function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        });
    }
});
</script>

<style>
.question-form-section {
    transition: all 0.3s ease;
}

.question-form-section.highlight-form {
    border: 2px solid #0073aa;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 115, 170, 0.3);
    background: #f8f9fa;
}

.question-form-section {
    margin-top: 20px;
    padding: 20px;
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 3px;
}

/* Smooth scroll behavior */
html {
    scroll-behavior: smooth;
}

/* Loading animation for buttons */
.button.loading {
    opacity: 0.7;
    pointer-events: none;
}

.button.loading:after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 16px;
    height: 16px;
    margin: -8px 0 0 -8px;
    border: 2px solid transparent;
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Next question dropdown styles */
.next-question-dropdown {
    min-width: 200px;
    transition: all 0.3s ease;
}

.next-question-dropdown.loading {
    opacity: 0.6;
    cursor: wait;
}

.next-question-dropdown.success-updated {
    border-color: #46b450;
    box-shadow: 0 0 5px rgba(70, 180, 80, 0.3);
}

/* Inline notification styles */
.inline-notification {
    margin: 10px 0;
    animation: slideInFromTop 0.3s ease-out;
}

@keyframes slideInFromTop {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Enhanced answer item styling */
.answer-item {
    position: relative;
    transition: all 0.3s ease;
}

.answer-item:hover {
    background: #f0f6fc !important;
}

/* Dropdown container styling */
.next-question-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 5px;
}

.next-question-label {
    font-size: 11px;
    color: #666;
    font-weight: 500;
}

/* Success/error state colors */
.text-success {
    color: #46b450;
}

.text-error {
    color: #dc3545;
}

/* Disabled button styling */
.button-disabled {
    background: #f0f0f1 !important;
    border-color: #dcdcde !important;
    color: #a7aaad !important;
    cursor: not-allowed !important;
    opacity: 0.6;
}

.button-disabled:hover {
    background: #f0f0f1 !important;
    border-color: #dcdcde !important;
    color: #a7aaad !important;
}
</style>