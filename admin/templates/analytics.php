<?php
/**
 * Analytics Dashboard Template
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html__('Survey Analytics', FLOWQ_TEXT_DOMAIN); ?></h1>

    <div class="analytics-wrapper">
        <!-- Survey Selection -->
        <div class="analytics-header">
            <form method="get" action="" class="survey-selector">
                <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">

                <label for="survey_id"><?php echo esc_html__('Select Survey:', FLOWQ_TEXT_DOMAIN); ?></label>
                <select name="survey_id" id="survey_id" onchange="this.form.submit()">
                    <option value=""><?php echo esc_html__('-- Choose a Survey --', FLOWQ_TEXT_DOMAIN); ?></option>
                    <?php foreach ($surveys as $survey): ?>
                        <option value="<?php echo esc_attr($survey['id']); ?>"
                                <?php selected($selected_survey_id, $survey['id']); ?>>
                            <?php echo esc_html($survey['title']); ?>
                            (<?php echo esc_html($survey['status']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($selected_survey_id && !empty($analytics_data)): ?>
                <div class="export-actions">
                    <button type="button" class="export-button" id="export-csv"
                            data-survey-id="<?php echo esc_attr($selected_survey_id); ?>">
                        <svg class="export-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                            <path d="M12,19L8,15H10.5V12H13.5V15H16L12,19Z"/>
                        </svg>
                        <span class="export-text"><?php echo esc_html__('Export CSV', FLOWQ_TEXT_DOMAIN); ?></span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($selected_survey_id && !empty($analytics_data)): ?>
            <!-- Survey Overview Stats -->
            <div class="analytics-overview">
                <div class="stats-grid">
                    <div class="stat-card stat-card-participants clickable-card" onclick="window.location.href='<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-survey-participants&survey_id=' . $selected_survey_id)); ?>'">
                        <div class="stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zM4 18v-4h3v-3c0-1.1.9-2 2-2h2c1.1 0 2 .9 2 2v3h3v4H4z"/>
                            </svg>
                        </div>
                        <div class="stat-number">
                            <?php echo esc_html(number_format($analytics_data['survey_stats']['total_participants'])); ?>
                        </div>
                        <div class="stat-label"><?php echo esc_html__('Total Participants', FLOWQ_TEXT_DOMAIN); ?></div>
                        <div class="click-hint"><?php echo esc_html__('Click to view details', FLOWQ_TEXT_DOMAIN); ?></div>
                    </div>

                    <div class="stat-card stat-card-completed">
                        <div class="stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
                            </svg>
                        </div>
                        <div class="stat-number">
                            <?php echo esc_html(number_format($analytics_data['survey_stats']['completed_participants'])); ?>
                        </div>
                        <div class="stat-label"><?php echo esc_html__('Completed Responses', FLOWQ_TEXT_DOMAIN); ?></div>
                    </div>

                    <div class="stat-card stat-card-rate">
                        <div class="stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                        </div>
                        <div class="stat-number">
                            <?php echo esc_html($analytics_data['survey_stats']['completion_rate'] . '%'); ?>
                        </div>
                        <div class="stat-label"><?php echo esc_html__('Completion Rate', FLOWQ_TEXT_DOMAIN); ?></div>
                    </div>

                    <div class="stat-card stat-card-time">
                        <div class="stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12.5,7V12.25L17,14.92L16.25,16.15L11,13V7H12.5Z"/>
                            </svg>
                        </div>
                        <div class="stat-number">
                            <?php
                            $avg_time = isset($analytics_data['survey_stats']['average_completion_time']) ?
                                $analytics_data['survey_stats']['average_completion_time'] : 0;
                            echo esc_html(round($avg_time, 1) . ' min');
                            ?>
                        </div>
                        <div class="stat-label"><?php echo esc_html__('Avg. Completion Time', FLOWQ_TEXT_DOMAIN); ?></div>
                    </div>
                </div>
            </div>

            <!-- Question Analytics -->
            <div class="question-analytics">
                <h2><?php echo esc_html__('Question Analytics', FLOWQ_TEXT_DOMAIN); ?></h2>

                <?php foreach ($analytics_data['question_stats'] as $index => $question_stat): ?>
                    <?php $question = $analytics_data['questions'][$index]; ?>

                    <div class="question-analytics-card">
                        <div class="question-header">
                            <div class="question-title-section">
                                <div class="question-number-badge">Q<?php echo esc_html($index + 1); ?></div>
                                <h3 class="question-title">
                                    <?php echo esc_html($question['title']); ?>
                                </h3>
                            </div>
                            <div class="question-meta">
                                <div class="response-count-badge">
                                    <?php echo esc_html(number_format($question_stat['total_responses'])); ?>
                                    <?php echo esc_html__('responses', FLOWQ_TEXT_DOMAIN); ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($question_stat['answer_distribution'])): ?>
                            <!-- Multiple Choice Chart -->
                            <div class="chart-container">
                                <canvas id="chart-question-<?php echo esc_attr($question['id']); ?>"
                                        width="400" height="200"></canvas>
                            </div>

                            <!-- Answer Distribution Table -->
                            <div class="answer-distribution">
                                <h4><?php echo esc_html__('Answer Distribution', FLOWQ_TEXT_DOMAIN); ?></h4>
                                <div class="analytics-table-container">
                                    <table class="analytics-table">
                                        <thead>
                                            <tr>
                                                <th class="answer-column"><?php echo esc_html__('Answer', FLOWQ_TEXT_DOMAIN); ?></th>
                                                <th class="count-column"><?php echo esc_html__('Count', FLOWQ_TEXT_DOMAIN); ?></th>
                                                <th class="percentage-column"><?php echo esc_html__('Percentage', FLOWQ_TEXT_DOMAIN); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($question_stat['answer_distribution'] as $answer): ?>
                                                <?php
                                                $percentage = $question_stat['total_responses'] > 0 ?
                                                    round(($answer['count'] / $question_stat['total_responses']) * 100, 1) : 0;
                                                ?>
                                                <tr class="analytics-table-row">
                                                    <td class="answer-text"><?php echo esc_html($answer['answer_text']); ?></td>
                                                    <td class="count-value"><?php echo esc_html(number_format($answer['count'])); ?></td>
                                                    <td class="percentage-cell">
                                                        <div class="percentage-container">
                                                            <div class="percentage-bar" style="width: <?php echo esc_attr($percentage); ?>%;"></div>
                                                            <span class="percentage-text"><?php echo esc_html($percentage . '%'); ?></span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($question_stat['text_responses'])): ?>
                            <!-- Text Responses -->
                            <div class="text-responses">
                                <h4><?php echo esc_html__('Recent Text Responses', FLOWQ_TEXT_DOMAIN); ?></h4>
                                <div class="text-responses-list">
                                    <?php foreach (array_slice($question_stat['text_responses'], 0, 10) as $response): ?>
                                        <div class="text-response-item">
                                            <div class="response-text">
                                                "<?php echo esc_html($response['answer_text']); ?>"
                                            </div>
                                            <div class="response-date">
                                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($response['responded_at']))); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php if (count($question_stat['text_responses']) > 10): ?>
                                    <p class="more-responses">
                                        <?php
                                        echo esc_html(sprintf(
                                            __('+ %d more responses (export CSV to see all)', FLOWQ_TEXT_DOMAIN),
                                            count($question_stat['text_responses']) - 10
                                        ));
                                        ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($selected_survey_id): ?>
            <!-- No Data Message -->
            <div class="no-data-message">
                <p><?php echo esc_html__('No analytics data available for this survey yet.', FLOWQ_TEXT_DOMAIN); ?></p>
                <p><?php echo esc_html__('Data will appear here once participants start completing the survey.', FLOWQ_TEXT_DOMAIN); ?></p>
            </div>

        <?php else: ?>
            <!-- No Survey Selected -->
            <div class="no-survey-selected">
                <div class="placeholder-content">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <h3><?php echo esc_html__('Select a Survey to View Analytics', FLOWQ_TEXT_DOMAIN); ?></h3>
                    <p><?php echo esc_html__('Choose a published survey from the dropdown above to see detailed analytics and response data.', FLOWQ_TEXT_DOMAIN); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Export CSV functionality
    $('#export-csv').on('click', function() {
        var surveyId = $(this).data('survey-id');
        var button = $(this);

        button.prop('disabled', true);
        button.find('.export-text').text('<?php echo esc_js(__('Exporting...', FLOWQ_TEXT_DOMAIN)); ?>');

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
                    alert('<?php echo esc_js(__('Export failed. Please try again.', FLOWQ_TEXT_DOMAIN)); ?>');
                }
            },
            error: function() {
                alert('<?php echo esc_js(__('Export failed. Please try again.', FLOWQ_TEXT_DOMAIN)); ?>');
            },
            complete: function() {
                button.prop('disabled', false);
                button.find('.export-text').text('<?php echo esc_js(__('Export CSV', FLOWQ_TEXT_DOMAIN)); ?>');
            }
        });
    });

    <?php if ($selected_survey_id && !empty($analytics_data)): ?>
    // Initialize charts for multiple choice questions
    <?php foreach ($analytics_data['question_stats'] as $index => $question_stat): ?>
        <?php $question = $analytics_data['questions'][$index]; ?>
        <?php if (!empty($question_stat['answer_distribution'])): ?>
        {
            var ctx = document.getElementById('chart-question-<?php echo esc_js($question['id']); ?>').getContext('2d');
            var chartData = [
                <?php foreach ($question_stat['answer_distribution'] as $answer): ?>
                <?php echo intval($answer['count']); ?>,
                <?php endforeach; ?>
            ];
            var total = chartData.reduce((a, b) => a + b, 0);

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [
                        <?php foreach ($question_stat['answer_distribution'] as $answer): ?>
                        '<?php echo esc_js($answer['answer_text']); ?>',
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        data: chartData,
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
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        return data.labels.map((label, i) => {
                                            const value = data.datasets[0].data[i];
                                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0';
                                            return {
                                                text: `${label} (${percentage}%)`,
                                                fillStyle: data.datasets[0].backgroundColor[i],
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
                                    const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : '0';
                                    return `${context.label}: ${context.parsed} responses (${percentage}%)`;
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
        <?php endif; ?>
    <?php endforeach; ?>
    <?php endif; ?>
});
</script>

<style>
/* CSS Variables for Design System */
:root {
    --color-primary: #2563EB;
    --color-primary-hover: #1D4ED8;
    --color-success: #059669;
    --color-warning: #EA580C;
    --color-error: #DC2626;
    --color-purple: #7C3AED;
    --color-orange: #F59E0B;
    --color-cyan: #0891B2;

    --color-text-primary: #1F2937;
    --color-text-secondary: #6B7280;
    --color-text-muted: #9CA3AF;

    --color-bg-primary: #FFFFFF;
    --color-bg-secondary: #F9FAFB;
    --color-bg-tertiary: #F3F4F6;
    --color-bg-header: #F8FAFC;

    --color-border: #E5E7EB;
    --color-border-light: #F3F4F6;

    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);

    --radius-sm: 6px;
    --radius-md: 8px;
    --radius-lg: 12px;
    --radius-xl: 16px;

    --spacing-xs: 8px;
    --spacing-sm: 12px;
    --spacing-md: 16px;
    --spacing-lg: 24px;
    --spacing-xl: 32px;
    --spacing-2xl: 48px;

    --font-size-xs: 12px;
    --font-size-sm: 14px;
    --font-size-base: 16px;
    --font-size-lg: 18px;
    --font-size-xl: 20px;
    --font-size-2xl: 24px;
    --font-size-3xl: 28px;
    --font-size-4xl: 36px;

    --font-weight-normal: 400;
    --font-weight-medium: 500;
    --font-weight-semibold: 600;
    --font-weight-bold: 700;
}

/* Typography System */
h1, .text-3xl {
    font-size: var(--font-size-3xl);
    font-weight: var(--font-weight-bold);
    color: var(--color-text-primary);
    line-height: 1.2;
}

h2, .text-xl {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-primary);
    line-height: 1.3;
}

h3, .text-lg {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-medium);
    color: var(--color-text-primary);
    line-height: 1.4;
}

h4, .text-base {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-medium);
    color: var(--color-text-primary);
    line-height: 1.5;
}

.text-sm {
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
    line-height: 1.4;
}

.text-xs {
    font-size: var(--font-size-xs);
    color: var(--color-text-muted);
    line-height: 1.3;
}

/* Layout Container */
.analytics-wrapper {
    margin: 0 auto;
    margin-top: var(--spacing-lg);
    min-height: 100vh;
}

/* Page Header */
.analytics-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-xl);
    padding: var(--spacing-lg);
    background: var(--color-bg-primary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
}

.survey-selector {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.survey-selector label {
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-primary);
    font-size: var(--font-size-sm);
}

.survey-selector select {
    min-width: 280px;
    padding: var(--spacing-sm) var(--spacing-md);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    background: var(--color-bg-primary);
    font-size: var(--font-size-sm);
    color: var(--color-text-primary);
    transition: all 200ms ease;
}

.survey-selector select:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.export-actions {
    display: flex;
    gap: var(--spacing-sm);
}

.export-button {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    background: var(--color-primary);
    color: white;
    border: 1px solid var(--color-primary);
    border-radius: var(--radius-lg);
    padding: var(--spacing-sm) var(--spacing-md);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    cursor: pointer;
    transition: all 200ms ease;
    outline: none;
}

.export-button:hover {
    background: var(--color-primary-hover);
    border-color: var(--color-primary-hover);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.export-button:focus-visible {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}

.export-button:disabled {
    background: var(--color-text-muted);
    border-color: var(--color-text-muted);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.export-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.export-text {
    white-space: nowrap;
}

/* Stats Grid */
.analytics-overview {
    margin-bottom: var(--spacing-2xl);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-2xl);
}

/* Enhanced Stat Cards */
.stat-card {
    background: var(--color-bg-primary);
    padding: var(--spacing-lg);
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-border);
    text-align: center;
    box-shadow: var(--shadow-md);
    transition: all 200ms ease;
    position: relative;
    height: 140px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.stat-card.clickable-card {
    cursor: pointer;
}

.stat-card:hover,
.stat-card.clickable-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

/* Stat Card Variants with Color Coding */
.stat-card-participants {
    border-left: 4px solid var(--color-primary);
}

.stat-card-participants .stat-icon {
    color: var(--color-primary);
}

.stat-card-participants.clickable-card:hover {
    background: #EFF6FF;
    border-color: var(--color-primary);
}

.stat-card-completed {
    border-left: 4px solid var(--color-success);
}

.stat-card-completed .stat-icon {
    color: var(--color-success);
}

.stat-card-rate {
    border-left: 4px solid var(--color-orange);
}

.stat-card-rate .stat-icon {
    color: var(--color-orange);
}

.stat-card-time {
    border-left: 4px solid var(--color-purple);
}

.stat-card-time .stat-icon {
    color: var(--color-purple);
}

.stat-icon {
    margin-bottom: var(--spacing-sm);
    width: 24px;
    height: 24px;
}

.stat-number {
    font-size: var(--font-size-4xl);
    font-weight: var(--font-weight-bold);
    font-variant-numeric: tabular-nums;
    color: var(--color-text-primary);
    margin-bottom: var(--spacing-xs);
    line-height: 1;
}

.stat-label {
    color: var(--color-text-secondary);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.click-hint {
    position: absolute;
    bottom: var(--spacing-sm);
    left: 50%;
    transform: translateX(-50%);
    font-size: var(--font-size-xs);
    color: var(--color-text-muted);
    opacity: 0;
    transition: opacity 200ms ease;
}

.clickable-card:hover .click-hint {
    opacity: 1;
}

/* Question Analytics Section */
.question-analytics {
    margin-bottom: var(--spacing-2xl);
}

.question-analytics h2 {
    margin-bottom: var(--spacing-lg);
    color: var(--color-text-primary);
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
}

.question-analytics-card {
    background: var(--color-bg-primary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-xl);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: all 200ms ease;
}

.question-analytics-card:hover {
    box-shadow: var(--shadow-md);
}

/* Enhanced Question Header */
.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-xl);
    background: var(--color-bg-secondary);
    border-bottom: 1px solid var(--color-border);
}

.question-title-section {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    flex: 1;
}

.question-number-badge {
    background: var(--color-primary);
    color: white;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-lg);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-bold);
    min-width: 32px;
    text-align: center;
}

.question-title {
    margin: 0;
    color: var(--color-text-primary);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-medium);
    line-height: 1.4;
}

.question-meta {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.response-count-badge {
    background: var(--color-bg-tertiary);
    color: var(--color-text-secondary);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-lg);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
}

/* Chart Container */
.chart-container {
    padding: var(--spacing-xl);
    height: 350px;
    background: var(--color-bg-primary);
    position: relative;
}

/* Enhanced Analytics Table */
.answer-distribution {
    padding: var(--spacing-xl);
    background: var(--color-bg-primary);
}

.answer-distribution h4 {
    margin-bottom: var(--spacing-md);
    color: var(--color-text-primary);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
}

.analytics-table-container {
    border-radius: var(--radius-md);
    overflow: hidden;
    border: 1px solid var(--color-border);
}

.analytics-table {
    width: 100%;
    border-collapse: collapse;
    background: var(--color-bg-primary);
}

.analytics-table thead th {
    background: var(--color-bg-secondary);
    color: var(--color-text-primary);
    font-weight: var(--font-weight-semibold);
    font-size: var(--font-size-sm);
    padding: var(--spacing-md) var(--spacing-md);
    text-align: left;
    border-bottom: 1px solid var(--color-border);
}

.analytics-table-row {
    transition: background-color 200ms ease;
}

.analytics-table-row:hover {
    background: #EFF6FF;
}

.analytics-table-row:nth-child(even) {
    background: #FAFAFA;
}

.analytics-table-row:nth-child(even):hover {
    background: #EFF6FF;
}

.analytics-table td {
    padding: var(--spacing-sm) var(--spacing-md);
    border-bottom: 1px solid var(--color-border-light);
    font-size: var(--font-size-sm);
}

.answer-text {
    font-weight: var(--font-weight-medium);
    color: var(--color-text-primary);
}

.count-value {
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-secondary);
    font-variant-numeric: tabular-nums;
}

/* Progress Bars in Table */
.percentage-cell {
    width: 200px;
}

.percentage-container {
    position: relative;
    background: var(--color-bg-tertiary);
    border-radius: var(--radius-sm);
    height: 24px;
    overflow: hidden;
}

.percentage-bar {
    background: linear-gradient(90deg, var(--color-primary), var(--color-primary-hover));
    height: 100%;
    border-radius: var(--radius-sm);
    transition: width 300ms ease;
    min-width: 2px;
}

.percentage-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-primary);
    z-index: 1;
}

/* Text Responses Section */
.text-responses {
    padding: var(--spacing-xl);
    background: var(--color-bg-primary);
    border-top: 1px solid var(--color-border);
}

.text-responses h4 {
    margin-bottom: var(--spacing-md);
    color: var(--color-text-primary);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
}

.text-responses-list {
    max-height: 400px;
    overflow-y: auto;
    padding-right: var(--spacing-xs);
}

.text-responses-list::-webkit-scrollbar {
    width: 6px;
}

.text-responses-list::-webkit-scrollbar-track {
    background: var(--color-bg-tertiary);
    border-radius: 3px;
}

.text-responses-list::-webkit-scrollbar-thumb {
    background: var(--color-border);
    border-radius: 3px;
}

.text-response-item {
    padding: var(--spacing-md);
    margin-bottom: var(--spacing-sm);
    background: var(--color-bg-secondary);
    border-left: 4px solid var(--color-primary);
    border-radius: var(--radius-sm);
    transition: all 200ms ease;
}

.text-response-item:hover {
    background: #EFF6FF;
    transform: translateX(2px);
}

.response-text {
    font-style: italic;
    margin-bottom: var(--spacing-xs);
    color: var(--color-text-primary);
    font-size: var(--font-size-sm);
    line-height: 1.5;
}

.response-date {
    font-size: var(--font-size-xs);
    color: var(--color-text-muted);
    font-weight: var(--font-weight-medium);
}

.more-responses {
    margin-top: var(--spacing-md);
    font-style: italic;
    color: var(--color-text-muted);
    text-align: center;
    font-size: var(--font-size-sm);
}

/* Empty States */
.no-data-message, .no-survey-selected {
    text-align: center;
    padding: var(--spacing-2xl) var(--spacing-lg);
    background: var(--color-bg-primary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
}

.no-survey-selected .placeholder-content .dashicons {
    font-size: 64px;
    color: var(--color-text-muted);
    margin-bottom: var(--spacing-md);
    opacity: 0.6;
}

.no-survey-selected h3 {
    color: var(--color-text-primary);
    margin-bottom: var(--spacing-sm);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-medium);
}

.no-survey-selected p {
    color: var(--color-text-secondary);
    max-width: 500px;
    margin: 0 auto;
    line-height: 1.6;
}

/* Interactive States */
.button:focus-visible,
.survey-selector select:focus-visible {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}

/* Loading States */
.loading-skeleton {
    background: linear-gradient(90deg, var(--color-bg-tertiary) 25%, var(--color-bg-secondary) 50%, var(--color-bg-tertiary) 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Responsive Design */
@media (max-width: 1024px) {
    .analytics-wrapper {
        padding: 0 var(--spacing-lg);
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .analytics-wrapper {
        padding: 0 var(--spacing-md);
    }

    .analytics-header {
        flex-direction: column;
        gap: var(--spacing-md);
        align-items: stretch;
    }

    .survey-selector {
        flex-direction: column;
        align-items: stretch;
        gap: var(--spacing-sm);
    }

    .survey-selector select {
        min-width: auto;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-md);
    }

    .stat-card {
        height: auto;
        padding: var(--spacing-md);
    }

    .question-header {
        flex-direction: column;
        gap: var(--spacing-sm);
        align-items: stretch;
    }

    .question-title-section {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }

    .chart-container {
        padding: var(--spacing-md);
        height: 280px;
    }

    .percentage-cell {
        width: 150px;
    }
}

@media (max-width: 480px) {
    .analytics-wrapper {
        padding: 0 var(--spacing-sm);
    }

    .analytics-header,
    .question-analytics-card .question-header,
    .answer-distribution,
    .text-responses {
        padding: var(--spacing-md);
    }

    .chart-container {
        padding: var(--spacing-sm);
        height: 250px;
    }

    .analytics-table td,
    .analytics-table th {
        padding: var(--spacing-xs) var(--spacing-sm);
        font-size: var(--font-size-xs);
    }

    .percentage-cell {
        width: 100px;
    }
}

/* Enhanced Animations */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.question-analytics-card {
    animation: slideInUp 400ms ease-out;
}

.stat-card {
    animation: slideInUp 400ms ease-out;
}

.stat-card:nth-child(2) { animation-delay: 100ms; }
.stat-card:nth-child(3) { animation-delay: 200ms; }
.stat-card:nth-child(4) { animation-delay: 300ms; }
</style>