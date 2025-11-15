/**
 * Templates List JavaScript
 * @package FlowQ
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        $('.select-template-btn').on('click', function() {
            const button = $(this);
            const buttonExist = $('button[disabled]');
            const templateId = button.data('template-id');
            const templateName = button.data('template-name');
            const card = button.closest('.template-card');

            // Confirm before selecting
            if (!confirm(flowqTemplates.confirmMessage)) {
                return;
            }

            // Disable button and show loading state
            button.prop('disabled', true).text(flowqTemplates.activatingText);

            // AJAX request to select template
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'flowq_select_template',
                    template_id: templateId,
                    nonce: flowqTemplates.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Remove active class from all cards
                        $('.template-card').removeClass('active');
                        $('.template-active-badge').remove();

                        // Update all buttons
                        $('.select-template-btn').each(function() {
                            $(this).removeClass('button-primary').addClass('button-secondary')
                                .prop('disabled', false)
                                .html(flowqTemplates.selectText);
                        });

                        // Add active class to selected card
                        card.addClass('active');

                        // Add active badge to preview
                        card.find('.template-preview').append(
                            '<div class="template-active-badge">' +
                            '<span class="dashicons dashicons-yes-alt"></span>' +
                            flowqTemplates.activeText +
                            '</div>'
                        );

                        buttonExist.prop('disabled', false).addClass('button-primary select-template-btn').text(flowqTemplates.selectText);

                        // Update button to selected state
                        button.removeClass('button-secondary').addClass('button-primary')
                            .prop('disabled', true)
                            .html('<span class="dashicons dashicons-yes"></span>' + flowqTemplates.selectedText);

                        // Show success notice
                        const notice = $('<div class="notice notice-success is-dismissible"><p>' +
                            response.data.message +
                            '</p></div>');
                        $('.templates-list-wrapper').before(notice);

                        // Auto-dismiss notice after 3 seconds
                        setTimeout(function() {
                            notice.fadeOut(function() {
                                $(this).remove();
                                window.location.reload();
                            });
                        }, 500);
                    } else {
                        alert(flowqTemplates.errorText + ' ' + response.data);
                        button.prop('disabled', false).text(flowqTemplates.selectText);
                    }
                },
                error: function(xhr, status, error) {
                    alert(flowqTemplates.errorGenericText);
                    button.prop('disabled', false).text(flowqTemplates.selectText);
                }
            });
        });
    });

})(jQuery);
