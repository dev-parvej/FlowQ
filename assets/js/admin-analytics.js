/**
 * Analytics Page JavaScript
 *
 * @package FlowQ
 */

(function($) {
    'use strict';

    // Analytics object
    window.FlowQAnalytics = {
        /**
         * Initialize analytics page
         */
        init: function() {
            this.initExportCSV();
        },

        /**
         * Initialize export CSV functionality
         */
        initExportCSV: function() {
            $('#export-csv').on('click', function() {
                var surveyId = $(this).data('survey-id');
                var button = $(this);
                var exportingText = flowqAnalytics.exportingText || 'Exporting...';
                var exportText = flowqAnalytics.exportText || 'Export CSV';
                var failText = flowqAnalytics.failText || 'Export failed. Please try again.';

                button.prop('disabled', true);
                button.find('.export-text').text(exportingText);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'flowq_admin_action',
                        admin_action: 'export_responses',
                        survey_id: surveyId,
                        nonce: flowqAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Create and download CSV file
                            var csvContent = '';
                            response.data.csv_data.forEach(function(row) {
                                csvContent += row.map(function(field) {
                                    return '"' + String(field).replace(/"/g, '""') + '"';
                                }).join(',') + '\n';
                            });

                            var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                            var link = document.createElement('a');
                            if (link.download !== undefined) {
                                var url = URL.createObjectURL(blob);
                                link.setAttribute('href', url);
                                link.setAttribute('download', 'survey-responses-' + surveyId + '.csv');
                                link.style.visibility = 'hidden';
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                            }
                        } else {
                            alert(failText);
                        }
                    },
                    error: function() {
                        alert(failText);
                    },
                    complete: function() {
                        button.prop('disabled', false);
                        button.find('.export-text').text(exportText);
                    }
                });
            });
        },

        /**
         * Initialize a chart for a question
         * @param {string} questionId - Question ID
         * @param {array} labels - Chart labels
         * @param {array} data - Chart data
         */
        initChart: function(questionId, labels, data) {
            var canvasId = 'chart-question-' + questionId;
            var canvas = document.getElementById(canvasId);

            if (!canvas || typeof Chart === 'undefined') {
                return;
            }

            var ctx = canvas.getContext('2d');
            var total = data.reduce(function(a, b) { return a + b; }, 0);

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            '#2563EB', '#059669', '#DC2626', '#7C3AED', '#EA580C',
                            '#0891B2', '#9333EA', '#0284C7', '#16A34A', '#CA8A04'
                        ],
                        borderColor: '#FFFFFF',
                        borderWidth: 2,
                        hoverBorderWidth: 3,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'rectRounded',
                                padding: 20,
                                font: {
                                    size: 14,
                                    weight: '500'
                                },
                                generateLabels: function(chart) {
                                    var chartData = chart.data;
                                    if (chartData.labels.length && chartData.datasets.length) {
                                        return chartData.labels.map(function(label, i) {
                                            var value = chartData.datasets[0].data[i];
                                            var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0';
                                            return {
                                                text: label + ' (' + percentage + '%)',
                                                fillStyle: chartData.datasets[0].backgroundColor[i],
                                                hidden: false,
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : '0';
                                    return context.label + ': ' + context.parsed + ' responses (' + percentage + '%)';
                                }
                            },
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#FFFFFF',
                            bodyColor: '#FFFFFF',
                            cornerRadius: 8,
                            displayColors: true
                        }
                    },
                    elements: {
                        arc: {
                            borderRadius: 4
                        }
                    },
                    cutout: '60%',
                    layout: {
                        padding: {
                            left: 20,
                            right: 20,
                            top: 20,
                            bottom: 20
                        }
                    }
                }
            });
        }
    };

    // Initialize when ready
    $(document).ready(function() {
        FlowQAnalytics.init();
    });

})(jQuery);
