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
    <div class="page-header-with-back">
        <h1><?php echo esc_html($page_title); ?></h1>
        <?php
        // Determine back button URL and text
        if ($selected_survey_id && !empty($survey)) {
            $back_url = admin_url('admin.php?page=wp-dynamic-surveys-add&survey_id=' . $selected_survey_id);
            $back_text = sprintf(__('← Back to Edit "%s"', WP_DYNAMIC_SURVEY_TEXT_DOMAIN), esc_html($survey['title']));
        } else {
            $back_url = admin_url('admin.php?page=wp-dynamic-surveys');
            $back_text = __('← Back to Surveys', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
        }
        ?>
        <a href="<?php echo esc_url($back_url); ?>" class="page-title-action back-button">
            <?php echo esc_html($back_text); ?>
        </a>
    </div>

    <?php if (empty($surveys)): ?>
        <div class="notice notice-warning">
            <p><?php echo esc_html__('No surveys found. Please create a survey first.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-add')); ?>" class="button button-primary"><?php echo esc_html__('Create Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></a></p>
        </div>
    <?php else: ?>

        <!-- Survey Filter Bar -->
        <div class="survey-filter-bar">
            <div class="filter-bar-content">
                <div class="filter-section">
                    <label for="survey_id" class="filter-label">
                        <span class="dashicons dashicons-filter"></span>
                        <?php echo esc_html__('Filter by Survey:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </label>
                    <form method="get" action="" class="filter-form">
                        <input type="hidden" name="page" value="wp-dynamic-surveys-questions">
                        <select name="survey_id" id="survey_id" class="filter-dropdown">
                            <option value=""><?php echo esc_html__('-- Select a Survey --', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
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
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-questions&survey_id=' . $selected_survey_id . '&action=add')); ?>"
                       class="add-question-button">
                        <span class="dashicons dashicons-plus-alt2"></span>
                        <?php echo esc_html__('Add New Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($survey): ?>
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

                    <?php if (empty($questions)): ?>
                        <div class="notice notice-info inline">
                            <p><?php echo esc_html__('No questions added yet.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
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
                                                    <span class="extra-message-label"><?php echo esc_html__('Optional Message:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
                                                    <?php echo esc_html($question['extra_message']); ?>
                                                </p>
                                            <?php endif; ?>

                                            <div class="question-meta-badges">
                                                <?php if (isset($question['is_required']) && !$question['is_required']): ?>
                                                    <span class="question-badge optional-badge">
                                                        <span class="dashicons dashicons-yes"></span>
                                                        <?php echo esc_html__('Optional', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
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
                                                        <strong><?php echo esc_html__('Skip to:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></strong>
                                                        <?php if (!empty($skip_question_title)): ?>
                                                            <span class="skip-destination-text"><?php echo esc_html($skip_question_title); ?></span>
                                                        <?php else: ?>
                                                            <span class="skip-destination-text warning">
                                                                <span class="dashicons dashicons-warning"></span>
                                                                <?php echo esc_html__('End survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="question-badge required-badge">
                                                        <span class="dashicons dashicons-admin-network"></span>
                                                        <?php echo esc_html__('Required', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (isset($question['is_required']) && !$question['is_required']): ?>
                                                <div class="skip-destination-section">
                                                    <div class="skip-destination-header">
                                                        <span class="dashicons dashicons-admin-generic"></span>
                                                        <strong><?php echo esc_html__('Change Skip Destination:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></strong>
                                                    </div>
                                                    <div class="skip-destination-content">
                                                        <div class="skip-destination-selector" data-question-id="<?php echo esc_attr($question['id']); ?>">
                                                            <select class="skip-destination-dropdown" data-original-value="<?php echo esc_attr($question['skip_next_question_id'] ?? ''); ?>">
                                                                <option value=""><?php echo esc_html__('— End survey —', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                                                <?php foreach ($questions as $q): ?>
                                                                    <?php if ($q['id'] != $question['id']): ?>
                                                                        <option value="<?php echo esc_attr($q['id']); ?>" <?php selected($question['skip_next_question_id'] ?? '', $q['id']); ?>>
                                                                            <?php echo esc_html($q['title']); ?>
                                                                        </option>
                                                                    <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <button type="button" class="button button-small save-skip-destination" style="display: none;">
                                                                <?php echo esc_html__('Save', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                                            </button>
                                                            <button type="button" class="button button-small cancel-skip-destination" style="display: none;">
                                                                <?php echo esc_html__('Cancel', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="question-actions">
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-questions&survey_id=' . $selected_survey_id . '&question_id=' . $question['id'])); ?>"
                                               class="action-button edit-button"
                                               title="<?php echo esc_attr__('Edit Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                                                <span class="dashicons dashicons-edit"></span>
                                            </a>
                                            <?php if ($question['response_count'] == 0): ?>
                                                <button type="button"
                                                        class="action-button delete-button"
                                                        title="<?php echo esc_attr__('Delete Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>"
                                                        data-question-id="<?php echo esc_attr($question['id']); ?>"
                                                        data-question-title="<?php echo esc_attr($question['title']); ?>"
                                                        data-delete-url="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=wp_dynamic_survey_delete_question&survey_id=' . $selected_survey_id . '&question_id=' . $question['id']), 'wp_dynamic_survey_question_action')); ?>">
                                                    <span class="dashicons dashicons-trash"></span>
                                                </button>
                                            <?php else: ?>
                                                <span class="action-button delete-button disabled"
                                                      title="<?php echo esc_attr__('Cannot delete question with responses', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                                                    <span class="dashicons dashicons-trash"></span>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="question-card-content">
                                        <div class="answers-section">
                                            <h4 class="answers-title">
                                                <span class="dashicons dashicons-list-view"></span>
                                                <?php echo esc_html__('Answer Options', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
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
                                                                                <?php echo esc_html__('End Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                                                            <?php endif; ?>
                                                                            <span class="edit-indicator dashicons dashicons-edit"></span>
                                                                        </span>

                                                                        <select class="next-question-dropdown hidden"
                                                                                data-answer-id="<?php echo esc_attr($answer['id']); ?>"
                                                                                data-original-value="<?php echo esc_attr($answer['next_question_id'] ?? ''); ?>">
                                                                            <option value=""><?php echo esc_html__('— End survey —', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                                                            <?php if (count($questions) > 1): ?>
                                                                                <?php foreach ($questions as $q): ?>
                                                                                    <?php if ($q['id'] != $question['id']): ?>
                                                                                        <option value="<?php echo esc_attr($q['id']); ?>" <?php selected($answer['next_question_id'] ?? '', $q['id']); ?>>
                                                                                            <?php echo esc_html($q['title']); ?>
                                                                                        </option>
                                                                                    <?php endif; ?>
                                                                                <?php endforeach; ?>
                                                                            <?php else: ?>
                                                                                <option disabled><?php echo esc_html__('No other questions available', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                                                            <?php endif; ?>
                                                                        </select>
                                                                    </div>

                                                                    <?php if ($answer['redirect_url']): ?>
                                                                        <div class="redirect-url-badge">
                                                                            <span class="dashicons dashicons-external"></span>
                                                                            <span class="redirect-text"><?php echo esc_html__('Redirects to:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
                                                                            <span class="redirect-value"><?php echo esc_html($answer['redirect_url']); ?></span>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ol>
                                            <?php else: ?>
                                                <p class="no-answers"><?php echo esc_html__('No answers configured', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
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

<!-- Delete Question Modal -->
<div id="delete-question-modal" class="question-modal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <span class="dashicons dashicons-warning"></span>
                <?php echo esc_html__('Delete Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
            </h3>
            <button type="button" class="modal-close" aria-label="<?php echo esc_attr__('Close', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>

        <div class="modal-body">
            <div class="warning-message">
                <p><?php echo esc_html__('Are you sure you want to delete this question?', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                <p class="question-title-display"></p>
                <p class="warning-note">
                    <span class="dashicons dashicons-info"></span>
                    <?php echo esc_html__('This action cannot be undone.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                </p>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="button button-secondary modal-cancel">
                <?php echo esc_html__('Cancel', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
            </button>
            <button type="button" class="button button-danger modal-confirm">
                <span class="dashicons dashicons-trash"></span>
                <?php echo esc_html__('Delete Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
            </button>
        </div>
    </div>
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
    $(document).on('click', '.edit-button', function(e) {
        e.preventDefault();

        const $button = $(this);
        const href = $button.attr('href');
        const $icon = $button.find('.dashicons');

        // Add loading state
        $button.addClass('loading').prop('disabled', true);
        $icon.removeClass('dashicons-edit').addClass('dashicons-update');

        // Navigate after brief delay
        setTimeout(function() {
            window.location.href = href;
        }, 200);
    });
    // Add highlighting effect to form sections
    setTimeout(function() {
        $('.question-form-section').addClass('highlight-form');
        setTimeout(function() {
            $('.question-form-section').removeClass('highlight-form');
        }, 2000);
    }, 500);

    // Handle delete question button clicks (show modal)
    $(document).on('click', '.delete-button:not(.disabled)', function(e) {
        e.preventDefault();

        const $button = $(this);
        const questionId = $button.data('question-id');
        const questionTitle = $button.data('question-title');
        const deleteUrl = $button.data('delete-url');

        // Update modal content
        $('#delete-question-modal .question-title-display').html('<strong>"' + questionTitle + '"</strong>');
        $('#delete-question-modal .modal-confirm').data('delete-url', deleteUrl);

        // Show modal
        showModal('#delete-question-modal');
    });

    // Handle modal close events
    $(document).on('click', '.modal-close, .modal-cancel, .modal-overlay', function(e) {
        e.preventDefault();
        hideModal('#delete-question-modal');
    });

    // Handle modal confirm delete
    $(document).on('click', '.modal-confirm', function(e) {
        e.preventDefault();

        const deleteUrl = $(this).data('delete-url');
        if (deleteUrl) {
            // Add loading state
            $(this).prop('disabled', true).addClass('loading');
            $(this).html('<span class="dashicons dashicons-update spin"></span> <?php echo esc_html__('Deleting...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>');

            // Redirect to delete URL
            window.location.href = deleteUrl;
        }
    });

    // Modal utility functions
    function showModal(selector) {
        const $modal = $(selector);
        $modal.fadeIn(200);
        $('body').addClass('modal-open');

        // Focus management
        $modal.find('.modal-confirm').focus();

        // Trap focus within modal
        $modal.on('keydown.modal', function(e) {
            if (e.key === 'Escape') {
                hideModal(selector);
            }
        });
    }

    function hideModal(selector) {
        const $modal = $(selector);
        $modal.fadeOut(200);
        $('body').removeClass('modal-open');
        $modal.off('keydown.modal');
    }

    // Handle next question badge clicks (show dropdown)
    $(document).on('click', '.next-question-badge', function(e) {
        e.preventDefault();
        const $badge = $(this);
        const $container = $badge.closest('.next-question-badge-container');
        const $dropdown = $container.find('.next-question-dropdown');

        // Hide badge and show dropdown
        $badge.addClass('hidden');
        $dropdown.removeClass('hidden').focus();
    });

    // Handle dropdown blur (hide dropdown, show badge)
    $(document).on('blur', '.next-question-dropdown', function() {
        const $dropdown = $(this);
        const $container = $dropdown.closest('.next-question-badge-container');
        const $badge = $container.find('.next-question-badge');

        // Hide dropdown and show badge
        $dropdown.addClass('hidden');
        $badge.removeClass('hidden');
    });

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

                    // Update the badge display
                    const $container = $dropdown.closest('.next-question-badge-container');
                    const $badge = $container.find('.next-question-badge');
                    const selectedText = $dropdown.find('option:selected').text();

                    if (nextQuestionId) {
                        $badge.html('<span class="dashicons dashicons-arrow-right-alt2"></span>' + selectedText + '<span class="edit-indicator dashicons dashicons-edit"></span>');
                    } else {
                        $badge.html('<span class="dashicons dashicons-flag"></span><?php echo esc_html__('End Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?><span class="edit-indicator dashicons dashicons-edit"></span>');
                    }

                    // Hide dropdown and show updated badge
                    $dropdown.addClass('hidden');
                    $badge.removeClass('hidden');

                    // Show success feedback
                    showNotification('success', response.data.message);

                    // Add visual success indicator
                    $badge.addClass('success-updated');
                    setTimeout(function() {
                        $badge.removeClass('success-updated');
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

    // Handle skip destination changes
    $('.skip-destination-dropdown').on('change', function() {
        var $dropdown = $(this);
        var $selector = $dropdown.closest('.skip-destination-selector');
        var $saveBtn = $selector.find('.save-skip-destination');
        var $cancelBtn = $selector.find('.cancel-skip-destination');
        var originalValue = $dropdown.data('original-value');
        var currentValue = $dropdown.val();

        if (currentValue !== originalValue) {
            $saveBtn.show();
            $cancelBtn.show();
        } else {
            $saveBtn.hide();
            $cancelBtn.hide();
        }
    });

    // Save skip destination
    $('.save-skip-destination').on('click', function() {
        var $button = $(this);
        var $selector = $button.closest('.skip-destination-selector');
        var $dropdown = $selector.find('.skip-destination-dropdown');
        var questionId = $selector.data('question-id');
        var skipQuestionId = $dropdown.val();

        $button.prop('disabled', true).text('<?php echo esc_js(__('Saving...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_dynamic_survey_update_question_skip_destination',
                nonce: '<?php echo wp_create_nonce('wp_dynamic_survey_admin_nonce'); ?>',
                question_id: questionId,
                skip_next_question_id: skipQuestionId
            },
            success: function(response) {
                if (response.success) {
                    $dropdown.data('original-value', skipQuestionId);
                    $button.hide();
                    $selector.find('.cancel-skip-destination').hide();

                    // Update warning display
                    var $warningSection = $selector.closest('.skip-destination-section').find('.skip-warning-inline');
                    if (skipQuestionId) {
                        $warningSection.slideUp(200);
                    } else {
                        $warningSection.slideDown(200);
                    }

                    // Show success message briefly
                    $button.text('<?php echo esc_js(__('Saved!', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>').prop('disabled', false);
                    setTimeout(function() {
                        $button.text('<?php echo esc_js(__('Save', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>');
                    }, 2000);
                } else {
                    alert('<?php echo esc_js(__('Error saving skip destination.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>');
                    $button.text('<?php echo esc_js(__('Save', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>').prop('disabled', false);
                }
            },
            error: function() {
                alert('<?php echo esc_js(__('Error saving skip destination.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>');
                $button.text('<?php echo esc_js(__('Save', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>').prop('disabled', false);
            }
        });
    });

    // Cancel skip destination change
    $('.cancel-skip-destination').on('click', function() {
        var $button = $(this);
        var $selector = $button.closest('.skip-destination-selector');
        var $dropdown = $selector.find('.skip-destination-dropdown');
        var originalValue = $dropdown.data('original-value');

        $dropdown.val(originalValue);
        $button.hide();
        $selector.find('.save-skip-destination').hide();
    });
});
</script>

<style>
/* Survey Filter Bar */
.survey-filter-bar {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 6px;
    margin: 20px 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.filter-bar-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    gap: 20px;
}

.filter-section {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.filter-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #1d2327;
    font-size: 14px;
    white-space: nowrap;
}

.filter-label .dashicons {
    color: #646970;
    font-size: 16px;
}

.filter-form {
    flex: 1;
    max-width: 400px;
}

.filter-dropdown {
    width: 100%;
    height: 40px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    padding: 8px 12px;
    font-size: 14px;
    background: #fff;
    color: #1d2327;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.filter-dropdown:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
}

.action-section {
    flex-shrink: 0;
}

.add-question-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #2271b1;
    color: white;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s ease;
    border: 1px solid #2271b1;
}

.add-question-button:hover {
    background: #135e96;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.add-question-button .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

/* Page header with back button */
.page-header-with-back {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
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
}

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

/* Question meta badges */
.question-meta-badges {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    margin-top: 12px;
}

.question-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
}

.question-badge .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.optional-badge {
    background: #e7f5ff;
    color: #0c5460;
    border: 1px solid #b8daff;
}

.required-badge {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.skip-destination-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 12px;
}

.skip-destination-badge .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
    color: #6c757d;
}

.skip-destination-badge strong {
    color: #495057;
}

.skip-destination-text {
    color: #212529;
    font-weight: 500;
}

.skip-destination-text.warning {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #856404;
}

.skip-destination-text.warning .dashicons {
    color: #f39c12;
}

.skip-destination-section {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.skip-destination-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
    color: #495057;
    font-size: 13px;
}

.skip-destination-header .dashicons {
    color: #6c757d;
}

/* Question Cards Layout */
.questions-grid {
    display: grid;
    gap: 20px;
    margin-top: 20px;
}

.question-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: box-shadow 0.3s ease, transform 0.2s ease;
    margin-bottom: 20px;
}

.question-card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.question-card-header {
    position: relative;
    padding: 20px 70px 20px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.question-header-content {
    width: 100%;
}

.question-title {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1d2327;
    line-height: 1.3;
}

.question-description {
    margin: 0 0 5px 0;
    color: #646970;
    font-size: 14px;
    line-height: 1.4;
}

.question-extra-message {
    margin: 0;
    color: #8c8f94;
    font-size: 13px;
    font-style: italic;
    line-height: 1.4;
}

.question-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
    position: absolute;
    top: 15px;
    right: 15px;
}

.action-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid transparent;
    cursor: pointer;
}

.action-button .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.edit-button {
    background: #0073aa;
    color: white;
    border-color: #0073aa;
}

.edit-button:hover:not(.loading) {
    background: #005a87;
    color: white;
    text-decoration: none;
    transform: scale(1.05);
}

.edit-button.loading {
    opacity: 0.7;
    pointer-events: none;
    transform: none !important;
}

.edit-button.loading .dashicons-update {
    animation: spin 1s linear infinite;
}

.delete-button {
    background: #d63638;
    color: white;
    border-color: #d63638;
}

.delete-button:hover {
    background: #b32d2e;
    color: white;
    text-decoration: none;
    transform: scale(1.05);
}

.delete-button.disabled {
    background: #f0f0f1;
    color: #a7aaad;
    border-color: #dcdcde;
    cursor: not-allowed;
    opacity: 0.6;
}

.delete-button.disabled:hover {
    transform: none;
    background: #f0f0f1;
    color: #a7aaad;
}

.question-card-content {
    padding: 20px;
}

.answers-section h4 {
    margin: 0 0 15px 0;
    font-size: 14px;
    font-weight: 600;
    color: #1d2327;
    display: flex;
    align-items: center;
    gap: 8px;
}

.answers-section h4 .dashicons {
    color: #8c8f94;
    font-size: 16px;
}

.answers-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.answer-item {
    position: relative;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    margin-bottom: 12px;
    padding: 15px 15px 15px 25px;
    transition: all 0.2s ease;
}

.answer-item:hover {
    background: #e9ecef;
    border-color: #dee2e6;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.answer-content {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.answer-text-container {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.answer-text {
    font-size: 14px;
    font-weight: 500;
    color: #1d2327;
    margin: 0;
}

.answer-value {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 3px;
    padding: 2px 6px;
    font-size: 11px;
    font-family: monospace;
    color: #646970;
}

.answer-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.next-question-badge-container {
    position: relative;
}

.next-question-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #0073aa;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.next-question-badge:hover {
    background: #005a87;
    color: white;
    text-decoration: none;
}

.next-question-badge .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.next-question-badge .edit-indicator {
    opacity: 0.7;
    font-size: 12px;
    width: 12px;
    height: 12px;
}

.next-question-badge.success-updated {
    background: #46b450;
    animation: pulse 1s ease-in-out;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.redirect-url-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f0f6fc;
    border: 1px solid #c3dcf2;
    color: #0073aa;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
}

.redirect-url-badge .dashicons {
    font-size: 12px;
    width: 12px;
    height: 12px;
}

.redirect-text {
    font-weight: 500;
}

.redirect-value {
    font-family: monospace;
    background: rgba(255, 255, 255, 0.8);
    padding: 1px 4px;
    border-radius: 2px;
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hidden {
    display: none !important;
}

/* Delete Question Modal */
.question-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(2px);
}

.modal-content {
    position: relative;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
    animation: modalSlideIn 0.2s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    background: #f8f9fa;
}

.modal-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #d63638;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-title .dashicons {
    color: #d63638;
    font-size: 20px;
}

.modal-close {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    border-radius: 4px;
    color: #646970;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: #f0f0f0;
    color: #d63638;
}

.modal-close .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
}

.modal-body {
    padding: 20px;
}

.warning-message p {
    margin: 0 0 15px 0;
    font-size: 14px;
    line-height: 1.5;
}

.question-title-display {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 10px;
    font-family: monospace;
    font-size: 13px;
    color: #1d2327;
}

.warning-note {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    padding: 10px;
    color: #856404;
    font-size: 13px;
    margin-top: 15px !important;
}

.warning-note .dashicons {
    color: #f39c12;
    font-size: 16px;
    flex-shrink: 0;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid #e0e0e0;
    background: #f8f9fa;
}

.modal-footer .button {
    min-width: 100px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.button-danger {
    background: #d63638;
    border-color: #d63638;
    color: white;
}

.button-danger:hover:not(:disabled) {
    background: #b32d2e;
    border-color: #b32d2e;
    color: white;
}

.button-danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.button-danger.loading .dashicons {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Prevent body scroll when modal is open */
body.modal-open {
    overflow: hidden;
}

/* Responsive modal */
@media screen and (max-width: 782px) {
    .modal-content {
        width: 95%;
        margin: 20px;
    }

    .modal-header,
    .modal-body,
    .modal-footer {
        padding: 15px;
    }

    .modal-footer {
        flex-direction: column;
    }

    .modal-footer .button {
        width: 100%;
    }
}

.answer-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 15px;
    transition: background-color 0.2s ease, border-color 0.2s ease;
}

.answer-card:hover {
    background: #e9ecef;
    border-color: #dee2e6;
}

.answer-main {
    margin-bottom: 10px;
}

.answer-text {
    font-weight: 500;
    color: #1d2327;
    margin: 0 0 4px 0;
    font-size: 14px;
}

.answer-value {
    color: #646970;
    font-size: 12px;
    font-family: monospace;
    background: #fff;
    padding: 2px 6px;
    border-radius: 3px;
    border: 1px solid #ddd;
    display: inline-block;
}

.answer-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
    margin-top: 8px;
}

.next-question-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.next-question-label {
    font-size: 11px;
    color: #646970;
    font-weight: 500;
    white-space: nowrap;
}

.next-question-dropdown {
    min-width: 180px;
    font-size: 12px;
    height: 28px;
    border-radius: 4px;
    border: 1px solid #ddd;
    background: #fff;
}

.redirect-url {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #646970;
    font-size: 12px;
}

.redirect-url .dashicons {
    color: #8c8f94;
    font-size: 14px;
}

.redirect-url span:last-child {
    font-family: monospace;
    background: #fff;
    padding: 2px 6px;
    border-radius: 3px;
    border: 1px solid #ddd;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.no-answers {
    color: #8c8f94;
    font-style: italic;
    margin: 0;
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px dashed #dee2e6;
}

/* Responsive adjustments */
@media screen and (max-width: 782px) {
    .filter-bar-content {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
        padding: 15px;
    }

    .filter-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .filter-form {
        max-width: none;
        width: 100%;
    }

    .action-section {
        align-self: center;
    }

    .add-question-button {
        width: 100%;
        justify-content: center;
    }

    .question-card-header {
        padding: 15px 60px 15px 15px;
    }

    .question-actions {
        top: 10px;
        right: 10px;
        gap: 6px;
    }

    .action-button {
        width: 28px;
        height: 28px;
    }

    .action-button .dashicons {
        font-size: 14px;
        width: 14px;
        height: 14px;
    }

    .answer-controls {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .next-question-dropdown {
        min-width: 100%;
    }

    .question-meta-badges {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .skip-destination-badge {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
}
</style>