/** Question Form JavaScript - @package FlowQ */
(function($) {
jQuery(document).ready(function($) {
    // Initialize answer options for single choice
    if ($('#answer-options-container').children().length === 0) {
        addAnswerOption();
        addAnswerOption();
    }

    // Initialize required field toggle
    initializeRequiredToggle();

    // Handle required checkbox change
    $('#question_is_required').on('change', function() {
        toggleSkipDestinationField();
        validateSkipDestination();
    });

    // Handle skip destination change
    $('#question_skip_next_question_id').on('change', function() {
        validateSkipDestination();
    });

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
            $preview.text(flowqQuestionForm.i18n.newAnswerOption);
        }
    });

    // Advanced Options Toggle
    $(document).on('click', '.advanced-options-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $toggle = $(this);
        var $content = $toggle.siblings('.advanced-options-content');
        var $icon = $toggle.find('.dashicons');
        var isCollapsed = $toggle.data('collapsed');

        if (isCollapsed) {
            $content.slideDown(200);
            $icon.removeClass('dashicons-arrow-right-alt2').addClass('dashicons-arrow-down-alt2');
            $toggle.data('collapsed', false);
        } else {
            $content.slideUp(200);
            $icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-right-alt2');
            $toggle.data('collapsed', true);
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

    function initializeRequiredToggle() {
        // Show/hide skip destination field based on initial state
        toggleSkipDestinationField();
        validateSkipDestination();
    }

    function toggleSkipDestinationField() {
        var isRequired = $('#question_is_required').is(':checked');
        var $skipField = $('.skip-destination-field');

        if (isRequired) {
            $skipField.slideUp(200);
        } else {
            $skipField.slideDown(200);
        }
    }

    function validateSkipDestination() {
        var isRequired = $('#question_is_required').is(':checked');
        var skipDestination = $('#question_skip_next_question_id').val();
        var $warning = $('.skip-warning');

        if (!isRequired && !skipDestination) {
            $warning.slideDown(200);
        } else {
            $warning.slideUp(200);
        }
    }

});
})(jQuery);
