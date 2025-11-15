/** Questions Page JavaScript - @package FlowQ */
(function($) {
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
            $(this).html('<span class="dashicons dashicons-update spin"></span> Deleting...');

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
            action: 'flowq_update_answer_next_question',
            nonce: flowqQuestionsPage.nonce,
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
                        $badge.html('<span class="dashicons dashicons-flag"></span>' + flowqQuestionsPage.i18n.endSurvey + '<span class="edit-indicator dashicons dashicons-edit"></span>');
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

        $button.prop('disabled', true).text(flowqQuestionsPage.i18n.saving);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'flowq_update_question_skip_destination',
                nonce: flowqQuestionsPage.nonce,
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
                    $button.text(flowqQuestionsPage.i18n.saved).prop('disabled', false);
                    setTimeout(function() {
                        $button.text(flowqQuestionsPage.i18n.save);
                    }, 2000);
                } else {
                    alert(flowqQuestionsPage.i18n.errorSaving);
                    $button.text(flowqQuestionsPage.i18n.save).prop('disabled', false);
                }
            },
            error: function() {
                alert(flowqQuestionsPage.i18n.errorSaving);
                $button.text(flowqQuestionsPage.i18n.save).prop('disabled', false);
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
})(jQuery);
