/**
 * Admin AJAX handlers for WP Dynamic Survey Plugin
 */
(function($) {
    'use strict';

    // Admin AJAX handler object
    const WPDynamicSurveyAdminAjax = {

        /**
         * Configuration
         */
        config: {
            ajaxUrl: wpDynamicSurveyAdmin.ajaxurl || ajaxurl,
            nonce: wpDynamicSurveyAdmin.nonce,
            strings: wpDynamicSurveyAdmin.strings || {}
        },

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initHeartbeat();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Statistics refresh
            $(document).on('click', '.refresh-statistics', this.refreshStatistics.bind(this));

            // Bulk export
            $(document).on('click', '.bulk-export-btn', this.initBulkExport.bind(this));

            // Session cleanup
            $(document).on('click', '.cleanup-sessions-btn', this.cleanupSessions.bind(this));

            // Permission checks
            $(document).on('click', '.check-permissions', this.checkPermissions.bind(this));
        },

        /**
         * Initialize heartbeat for real-time updates
         */
        initHeartbeat: function() {
            setInterval(this.heartbeat.bind(this), 30000); // Every 30 seconds
        },

        /**
         * Heartbeat function
         */
        heartbeat: function() {
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_dynamic_survey_heartbeat',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update any real-time elements
                        this.updateServerTime(response.data.server_time);
                    }
                }.bind(this),
                error: function(xhr, status, error) {
                    console.log('Heartbeat error:', error);
                }
            });
        },

        /**
         * Refresh survey statistics
         */
        refreshStatistics: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const surveyId = $button.data('survey-id');

            if (!surveyId) {
                this.showError('Survey ID not found.');
                return;
            }

            $button.prop('disabled', true).text(this.config.strings.loading || 'Loading...');

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_dynamic_survey_get_survey_statistics',
                    survey_id: surveyId,
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        this.updateStatisticsDisplay(response.data);
                        this.showSuccess('Statistics updated successfully.');
                    } else {
                        this.showError(response.data || 'Failed to refresh statistics.');
                    }
                }.bind(this),
                error: function(xhr, status, error) {
                    this.showError('Failed to refresh statistics: ' + error);
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).text(this.config.strings.refresh || 'Refresh');
                }.bind(this)
            });
        },

        /**
         * Initialize bulk export
         */
        initBulkExport: function(e) {
            e.preventDefault();

            const selectedSurveys = $('.survey-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedSurveys.length === 0) {
                this.showError('Please select at least one survey to export.');
                return;
            }

            const format = $('#export-format').val() || 'csv';

            this.performBulkExport(selectedSurveys, format);
        },

        /**
         * Perform bulk export
         */
        performBulkExport: function(surveyIds, format) {
            const $button = $('.bulk-export-btn');
            $button.prop('disabled', true).text(this.config.strings.exporting || 'Exporting...');

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_dynamic_survey_bulk_export_responses',
                    survey_ids: surveyIds,
                    format: format,
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        this.downloadExportFile(response.data);
                        this.showSuccess(`Successfully exported ${response.data.total_surveys} surveys.`);
                    } else {
                        this.showError(response.data || 'Export failed.');
                    }
                }.bind(this),
                error: function(xhr, status, error) {
                    this.showError('Export failed: ' + error);
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).text(this.config.strings.export || 'Export Selected');
                }.bind(this)
            });
        },

        /**
         * Download export file
         */
        downloadExportFile: function(data) {
            let content = '';

            if (data.format === 'csv') {
                // Convert to CSV format
                for (const [surveyId, surveyData] of Object.entries(data.data)) {
                    content += surveyData + '\n\n';
                }
            } else {
                content = JSON.stringify(data.data, null, 2);
            }

            const blob = new Blob([content], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = data.filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        },

        /**
         * Cleanup expired sessions
         */
        cleanupSessions: function(e) {
            e.preventDefault();

            const daysOld = $('#cleanup-days').val() || 30;

            if (!confirm(`Are you sure you want to delete sessions older than ${daysOld} days? This action cannot be undone.`)) {
                return;
            }

            const $button = $(e.currentTarget);
            $button.prop('disabled', true).text(this.config.strings.cleaning || 'Cleaning...');

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_dynamic_survey_cleanup_sessions',
                    days_old: daysOld,
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        this.showSuccess(`Successfully cleaned ${response.data.cleaned_sessions} expired sessions.`);
                        // Refresh the page to update counts
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        this.showError(response.data || 'Cleanup failed.');
                    }
                }.bind(this),
                error: function(xhr, status, error) {
                    this.showError('Cleanup failed: ' + error);
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).text(this.config.strings.cleanup || 'Cleanup Sessions');
                }.bind(this)
            });
        },

        /**
         * Check user permissions
         */
        checkPermissions: function(e) {
            e.preventDefault();

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_dynamic_survey_check_permissions'
                },
                success: function(response) {
                    if (response.success) {
                        this.displayPermissions(response.data);
                    }
                }.bind(this),
                error: function(xhr, status, error) {
                    this.showError('Permission check failed: ' + error);
                }.bind(this)
            });
        },

        /**
         * Update statistics display
         */
        updateStatisticsDisplay: function(stats) {
            $('.total-participants').text(stats.total_participants || 0);
            $('.completed-surveys').text(stats.completed_surveys || 0);
            $('.completion-rate').text((stats.completion_rate || 0) + '%');
            $('.average-time').text(this.formatTime(stats.average_completion_time || 0));
        },

        /**
         * Display permissions
         */
        displayPermissions: function(permissions) {
            const $modal = $('#permissions-modal');
            let html = '<ul>';
            for (const [perm, granted] of Object.entries(permissions)) {
                const status = granted ? '✓' : '✗';
                const className = granted ? 'granted' : 'denied';
                html += `<li class="${className}">${status} ${perm.replace('_', ' ')}</li>`;
            }
            html += '</ul>';
            $modal.find('.permissions-list').html(html);
            $modal.show();
        },

        /**
         * Update server time display
         */
        updateServerTime: function(serverTime) {
            $('.server-time').text(serverTime);
        },

        /**
         * Format time in seconds to readable format
         */
        formatTime: function(seconds) {
            if (seconds < 60) {
                return Math.round(seconds) + 's';
            } else if (seconds < 3600) {
                return Math.round(seconds / 60) + 'm';
            } else {
                return Math.round(seconds / 3600) + 'h';
            }
        },

        /**
         * Show success message
         */
        showSuccess: function(message) {
            this.showNotice(message, 'success');
        },

        /**
         * Show error message
         */
        showError: function(message) {
            this.showNotice(message, 'error');
        },

        /**
         * Show notice
         */
        showNotice: function(message, type) {
            const $notice = $(`<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`);
            $('.wp-header-end').after($notice);

            setTimeout(() => {
                $notice.fadeOut(() => $notice.remove());
            }, 5000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        WPDynamicSurveyAdminAjax.init();
    });

    // Expose globally for debugging
    window.WPDynamicSurveyAdminAjax = WPDynamicSurveyAdminAjax;

})(jQuery);