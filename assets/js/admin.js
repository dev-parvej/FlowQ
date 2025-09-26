/**
 * Admin JavaScript for WP Dynamic Survey Plugin
 *
 * @package WP_Dynamic_Survey
 */

(function($) {
    'use strict';

    const WPDynamicSurveyAdmin = {
        init: function() {
            this.bindEvents();
            this.initTooltips();
        },

        bindEvents: function() {
            // Survey actions
            $(document).on('click', '.survey-action', this.handleSurveyAction.bind(this));

            // Bulk actions
            $(document).on('click', '#doaction, #doaction2', this.handleBulkAction.bind(this));

            // Copy shortcode functionality
            $(document).on('click', '.copy-shortcode', this.copyShortcode.bind(this));

            // Survey status changes
            $(document).on('change', '#survey_status', this.handleStatusChange.bind(this));


            // Survey deletion confirmation
            $(document).on('click', '.delete-survey', this.confirmDelete.bind(this));

            // Analytics refresh
            $(document).on('click', '.refresh-analytics', this.refreshAnalytics.bind(this));

            // Export responses
            $(document).on('click', '.export-responses', this.exportResponses.bind(this));

            // Survey validation
            $(document).on('submit', '#survey-form', this.validateSurveyForm.bind(this));
        },

        initTooltips: function() {
            // Initialize WordPress-style tooltips if available
            if (typeof $.fn.tooltip !== 'undefined') {
                $('[title]').tooltip();
            }
        },

        handleSurveyAction: function(e) {
            e.preventDefault();
            const $button = $(e.target);
            const action = $button.data('action');
            const surveyId = $button.data('survey-id');

            if (!action || !surveyId) {
                return;
            }

            // Show loading state
            $button.prop('disabled', true);
            const originalText = $button.text();
            $button.text(wpDynamicSurveyAdmin.strings.loading || 'Loading...');

            const data = {
                action: 'wp_dynamic_survey_admin_action',
                admin_action: action,
                survey_id: surveyId,
                nonce: wpDynamicSurveyAdmin.nonce
            };

            $.post(wpDynamicSurveyAdmin.ajaxurl, data)
                .done(function(response) {
                    if (response.success) {
                        this.showNotice(response.data.message || 'Action completed successfully.', 'success');
                        // Refresh page if needed
                        if (response.data.refresh) {
                            location.reload();
                        }
                    } else {
                        this.showNotice(response.data || 'Action failed.', 'error');
                    }
                }.bind(this))
                .fail(function() {
                    this.showNotice(wpDynamicSurveyAdmin.strings.error || 'An error occurred.', 'error');
                }.bind(this))
                .always(function() {
                    $button.prop('disabled', false).text(originalText);
                });
        },

        handleBulkAction: function(e) {
            const $form = $(e.target).closest('form');
            const action = $form.find('select[name="action"]').val() || $form.find('select[name="action2"]').val();

            if (action === '-1') {
                e.preventDefault();
                this.showNotice('Please select an action.', 'warning');
                return false;
            }

            const checkedItems = $form.find('input[name="survey[]"]:checked').length;
            if (checkedItems === 0) {
                e.preventDefault();
                this.showNotice('Please select at least one survey.', 'warning');
                return false;
            }

            // Confirm destructive actions
            if (action.includes('delete')) {
                return confirm('Are you sure you want to delete the selected surveys? This action cannot be undone.');
            }

            return true;
        },

        copyShortcode: function(e) {
            e.preventDefault();
            const shortcode = $(e.target).data('shortcode');

            if (navigator.clipboard) {
                navigator.clipboard.writeText(shortcode).then(function() {
                    this.showNotice('Shortcode copied to clipboard!', 'success');
                }.bind(this));
            } else {
                // Fallback for older browsers
                const $temp = $('<input>');
                $('body').append($temp);
                $temp.val(shortcode).select();
                document.execCommand('copy');
                $temp.remove();
                this.showNotice('Shortcode copied to clipboard!', 'success');
            }
        },

        confirmDelete: function(e) {
            const message = wpDynamicSurveyAdmin.strings.confirm_delete || 'Are you sure you want to delete this survey?';
            return confirm(message);
        },

        refreshAnalytics: function(e) {
            e.preventDefault();
            const $button = $(e.target);
            const surveyId = $button.data('survey-id');

            $button.prop('disabled', true).text(wpDynamicSurveyAdmin.strings.loading || 'Loading...');

            const data = {
                action: 'wp_dynamic_survey_admin_action',
                admin_action: 'get_survey_stats',
                survey_id: surveyId,
                nonce: wpDynamicSurveyAdmin.nonce
            };

            $.post(wpDynamicSurveyAdmin.ajaxurl, data)
                .done(function(response) {
                    if (response.success) {
                        this.updateAnalyticsDisplay(response.data);
                        this.showNotice('Analytics refreshed successfully.', 'success');
                    } else {
                        this.showNotice(response.data || 'Failed to refresh analytics.', 'error');
                    }
                }.bind(this))
                .fail(function() {
                    this.showNotice(wpDynamicSurveyAdmin.strings.error || 'An error occurred.', 'error');
                }.bind(this))
                .always(function() {
                    $button.prop('disabled', false).text(wpDynamicSurveyAdmin.strings.refresh || 'Refresh');
                });
        },

        exportResponses: function(e) {
            e.preventDefault();
            const $button = $(e.target);
            const surveyId = $button.data('survey-id');

            $button.prop('disabled', true).text(wpDynamicSurveyAdmin.strings.exporting || 'Exporting...');

            const data = {
                action: 'wp_dynamic_survey_admin_action',
                admin_action: 'export_responses',
                survey_id: surveyId,
                nonce: wpDynamicSurveyAdmin.nonce
            };

            $.post(wpDynamicSurveyAdmin.ajaxurl, data)
                .done(function(response) {
                    if (response.success) {
                        this.downloadCSV(response.data.csv_data, `survey-${surveyId}-responses.csv`);
                        this.showNotice('Export completed successfully.', 'success');
                    } else {
                        this.showNotice(response.data || 'Export failed.', 'error');
                    }
                }.bind(this))
                .fail(function() {
                    this.showNotice(wpDynamicSurveyAdmin.strings.error || 'An error occurred.', 'error');
                }.bind(this))
                .always(function() {
                    $button.prop('disabled', false).text(wpDynamicSurveyAdmin.strings.export || 'Export');
                });
        },

        validateSurveyForm: function(e) {
            const $form = $(e.target);
            const title = $form.find('#survey_title').val().trim();

            if (!title) {
                e.preventDefault();
                this.showNotice('Survey title is required.', 'error');
                $form.find('#survey_title').focus();
                return false;
            }

            return true;
        },

        updateAnalyticsDisplay: function(data) {
            // Update analytics counters
            $('.stat-total-participants').text(data.total_participants || '0');
            $('.stat-completed-surveys').text(data.completed_surveys || '0');
            $('.stat-completion-rate').text((data.completion_rate || '0') + '%');
            $('.stat-average-time').text(data.average_completion_time || 'N/A');

            // Update charts if they exist
            if (typeof window.surveyChart !== 'undefined') {
                window.surveyChart.data = data.chart_data;
                window.surveyChart.update();
            }
        },

        downloadCSV: function(csvData, filename) {
            const blob = new Blob([csvData], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');

            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        },

        showNotice: function(message, type) {
            type = type || 'info';

            // Remove existing notices
            $('.wp-dynamic-survey-notice').remove();

            const $notice = $(`
                <div class="notice notice-${type} is-dismissible wp-dynamic-survey-notice">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `);

            // Insert after the first h1 or at the top of .wrap
            const $target = $('.wrap h1').first();
            if ($target.length) {
                $target.after($notice);
            } else {
                $('.wrap').prepend($notice);
            }

            // Auto-dismiss success notices
            if (type === 'success') {
                setTimeout(function() {
                    $notice.fadeOut();
                }, 3000);
            }

            // Handle dismiss button
            $notice.on('click', '.notice-dismiss', function() {
                $notice.fadeOut();
            });
        },
    };

    // Initialize when document is ready
    $(document).ready(function() {
        WPDynamicSurveyAdmin.init();
    });

    // Expose to global scope for debugging
    window.WPDynamicSurveyAdmin = WPDynamicSurveyAdmin;

})(jQuery);