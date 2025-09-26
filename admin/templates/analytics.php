<?php
/**
 * Analytics Dashboard Template
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html__('Survey Analytics', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h1>

    <div class="analytics-wrapper">
        <!-- Survey Selection -->
        <div class="analytics-header">
            <form method="get" action="" class="survey-selector">
                <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">

                <label for="survey_id"><?php echo esc_html__('Select Survey:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                <select name="survey_id" id="survey_id" onchange="this.form.submit()">
                    <option value=""><?php echo esc_html__('-- Choose a Survey --', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
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
                    <button type="button" class="button button-secondary" id="export-csv"
                            data-survey-id="<?php echo esc_attr($selected_survey_id); ?>">
                        <span class="dashicons dashicons-download"></span>
                        <?php echo esc_html__('Export CSV', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($selected_survey_id && !empty($analytics_data)): ?>
            <!-- Survey Overview Stats -->
            <div class="analytics-overview">
                <div class="stats-grid">
                    <div class="stat-card clickable-card" onclick="window.location.href='<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-survey-participants&survey_id=' . $selected_survey_id)); ?>'">
                        <div class="stat-number">
                            <?php echo esc_html(number_format($analytics_data['survey_stats']['total_participants'])); ?>
                        </div>
                        <div class="stat-label"><?php echo esc_html__('Total Participants', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></div>
                        <div class="click-hint"><?php echo esc_html__('Click to view details', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-number">
                            
                            <?php echo esc_html(number_format($analytics_data['survey_stats']['completed_participants'])); ?>
                        </div>
                        <div class="stat-label"><?php echo esc_html__('Completed Responses', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-number">
                            <?php echo esc_html($analytics_data['survey_stats']['completion_rate'] . '%'); ?>
                        </div>
                        <div class="stat-label"><?php echo esc_html__('Completion Rate', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-number">
                            <?php
                            $avg_time = isset($analytics_data['survey_stats']['average_completion_time']) ?
                                $analytics_data['survey_stats']['average_completion_time'] : 0;
                            echo esc_html(round($avg_time, 1) . ' min');
                            ?>
                        </div>
                        <div class="stat-label"><?php echo esc_html__('Avg. Completion Time', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></div>
                    </div>
                </div>
            </div>

            <!-- Question Analytics -->
            <div class="question-analytics">
                <h2><?php echo esc_html__('Question Analytics', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>

                <?php foreach ($analytics_data['question_stats'] as $index => $question_stat): ?>
                    <?php $question = $analytics_data['questions'][$index]; ?>

                    <div class="question-analytics-card">
                        <div class="question-header">
                            <h3>
                                <?php echo esc_html__('Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                #<?php echo esc_html($question['id']); ?>:
                                <?php echo esc_html($question['title']); ?>
                            </h3>
                        </div>

                        <div class="question-stats-summary">
                            <div class="response-count">
                                <?php echo esc_html__('Total Responses:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                <strong><?php echo esc_html(number_format($question_stat['total_responses'])); ?></strong>
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
                                <h4><?php echo esc_html__('Answer Distribution', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h4>
                                <table class="wp-list-table widefat fixed striped">
                                    <thead>
                                        <tr>
                                            <th><?php echo esc_html__('Answer', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></th>
                                            <th><?php echo esc_html__('Count', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></th>
                                            <th><?php echo esc_html__('Percentage', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($question_stat['answer_distribution'] as $answer): ?>
                                            <?php
                                            $percentage = $question_stat['total_responses'] > 0 ?
                                                round(($answer['count'] / $question_stat['total_responses']) * 100, 1) : 0;
                                            ?>
                                            <tr>
                                                <td><?php echo esc_html($answer['answer_text']); ?></td>
                                                <td><?php echo esc_html(number_format($answer['count'])); ?></td>
                                                <td><?php echo esc_html($percentage . '%'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($question_stat['text_responses'])): ?>
                            <!-- Text Responses -->
                            <div class="text-responses">
                                <h4><?php echo esc_html__('Recent Text Responses', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h4>
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
                                            __('+ %d more responses (export CSV to see all)', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
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
                <p><?php echo esc_html__('No analytics data available for this survey yet.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                <p><?php echo esc_html__('Data will appear here once participants start completing the survey.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
            </div>

        <?php else: ?>
            <!-- No Survey Selected -->
            <div class="no-survey-selected">
                <div class="placeholder-content">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <h3><?php echo esc_html__('Select a Survey to View Analytics', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>
                    <p><?php echo esc_html__('Choose a published survey from the dropdown above to see detailed analytics and response data.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
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

        button.prop('disabled', true).text('<?php echo esc_js(__('Exporting...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_dynamic_survey_admin_action',
                admin_action: 'export_responses',
                survey_id: surveyId,
                nonce: wpDynamicSurveyAdmin.nonce
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
                    alert('<?php echo esc_js(__('Export failed. Please try again.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>');
                }
            },
            error: function() {
                alert('<?php echo esc_js(__('Export failed. Please try again.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>');
            },
            complete: function() {
                button.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> <?php echo esc_js(__('Export CSV', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>');
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
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [
                        <?php foreach ($question_stat['answer_distribution'] as $answer): ?>
                        '<?php echo esc_js($answer['answer_text']); ?>',
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        data: [
                            <?php foreach ($question_stat['answer_distribution'] as $answer): ?>
                            <?php echo intval($answer['count']); ?>,
                            <?php endforeach; ?>
                        ],
                        backgroundColor: [
                            '#2271b1', '#135e96', '#005a87', '#0d5178', '#72aee6',
                            '#4f94d4', '#3582c4', '#1e5b96', '#4c7bd4', '#5cb3f0'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
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
.analytics-wrapper {
    max-width: 100%;
    margin: 20px 0;
}

.analytics-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.survey-selector {
    display: flex;
    align-items: center;
    gap: 10px;
}

.survey-selector label {
    font-weight: 600;
}

.survey-selector select {
    min-width: 250px;
}

.export-actions {
    display: flex;
    gap: 10px;
}

.analytics-overview {
    margin-bottom: 30px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    border: 1px solid #ddd;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-card.clickable-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.stat-card.clickable-card:hover {
    background: #f0f6fc;
    border-color: #2271b1;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.click-hint {
    font-size: 11px;
    color: #666;
    margin-top: 8px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.clickable-card:hover .click-hint {
    opacity: 1;
}

.stat-number {
    font-size: 2.5em;
    font-weight: bold;
    color: #2271b1;
    margin-bottom: 10px;
}

.stat-label {
    color: #666;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.question-analytics {
    margin-bottom: 30px;
}

.question-analytics h2 {
    margin-bottom: 20px;
    color: #1d2327;
}

.question-analytics-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 30px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
}

.question-header h3 {
    margin: 0;
    color: #1d2327;
    font-size: 16px;
}

.question-type {
    background: #2271b1;
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
}

.question-stats-summary {
    padding: 15px 20px;
    background: #f0f6fc;
    border-bottom: 1px solid #eee;
}

.response-count {
    font-size: 14px;
    color: #666;
}

.chart-container {
    padding: 30px;
    height: 300px;
}

.answer-distribution {
    padding: 20px;
}

.answer-distribution h4 {
    margin-bottom: 15px;
    color: #1d2327;
}

.text-responses {
    padding: 20px;
}

.text-responses h4 {
    margin-bottom: 15px;
    color: #1d2327;
}

.text-responses-list {
    max-height: 300px;
    overflow-y: auto;
}

.text-response-item {
    padding: 12px;
    margin-bottom: 10px;
    background: #f9f9f9;
    border-left: 3px solid #2271b1;
    border-radius: 4px;
}

.response-text {
    font-style: italic;
    margin-bottom: 5px;
    color: #333;
}

.response-date {
    font-size: 12px;
    color: #666;
}

.more-responses {
    margin-top: 15px;
    font-style: italic;
    color: #666;
    text-align: center;
}

.no-data-message, .no-survey-selected {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.no-survey-selected .placeholder-content .dashicons {
    font-size: 48px;
    color: #ccd0d4;
    margin-bottom: 15px;
}

.no-survey-selected h3 {
    color: #666;
    margin-bottom: 10px;
}

.no-survey-selected p {
    color: #888;
    max-width: 500px;
    margin: 0 auto;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .analytics-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }

    .survey-selector {
        flex-direction: column;
        align-items: stretch;
    }

    .survey-selector select {
        min-width: auto;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .question-header {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }

    .chart-container {
        padding: 15px;
        height: 250px;
    }
}
</style>