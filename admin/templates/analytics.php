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
    <h1><?php echo esc_html__('Survey Analytics', 'flowq'); ?></h1>

    <div class="analytics-wrapper">
        <!-- Survey Selection -->
        <div class="analytics-header">
            <form method="get" action="" class="survey-selector">
                <input type="hidden" name="page" value="<?php echo esc_attr(sanitize_text_field($_GET['page'])); ?>">

                <label for="survey_id"><?php echo esc_html__('Select Survey:', 'flowq'); ?></label>
                <select name="survey_id" id="survey_id" onchange="this.form.submit()">
                    <option value=""><?php echo esc_html__('-- Choose a Survey --', 'flowq'); ?></option>
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
                        <span class="export-text"><?php echo esc_html__('Export CSV', 'flowq'); ?></span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($selected_survey_id && !empty($analytics_data)): ?>
            <!-- Survey Overview Stats -->
            <div class="analytics-overview">
                <div class="stats-grid">
                    <div class="stat-card stat-card-participants clickable-card" onclick="window.location.href='<?php echo esc_url(admin_url('admin.php?page=flowq-participants&survey_id=' . $selected_survey_id)); ?>'">
                        <div class="stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zM4 18v-4h3v-3c0-1.1.9-2 2-2h2c1.1 0 2 .9 2 2v3h3v4H4z"/>
                            </svg>
                        </div>
                        <div class="stat-number">
                            <?php echo esc_html(number_format($analytics_data['survey_stats']['total_participants'])); ?>
                        </div>
                        <div class="stat-label"><?php echo esc_html__('Total Participants', 'flowq'); ?></div>
                        <div class="click-hint"><?php echo esc_html__('Click to view details', 'flowq'); ?></div>
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
                        <div class="stat-label"><?php echo esc_html__('Completed Responses', 'flowq'); ?></div>
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
                        <div class="stat-label"><?php echo esc_html__('Completion Rate', 'flowq'); ?></div>
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
                        <div class="stat-label"><?php echo esc_html__('Avg. Completion Time', 'flowq'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Question Analytics -->
            <div class="question-analytics">
                <h2><?php echo esc_html__('Question Analytics', 'flowq'); ?></h2>

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
                                    <?php echo esc_html__('responses', 'flowq'); ?>
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
                                <h4><?php echo esc_html__('Answer Distribution', 'flowq'); ?></h4>
                                <div class="analytics-table-container">
                                    <table class="analytics-table">
                                        <thead>
                                            <tr>
                                                <th class="answer-column"><?php echo esc_html__('Answer', 'flowq'); ?></th>
                                                <th class="count-column"><?php echo esc_html__('Count', 'flowq'); ?></th>
                                                <th class="percentage-column"><?php echo esc_html__('Percentage', 'flowq'); ?></th>
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
                                <h4><?php echo esc_html__('Recent Text Responses', 'flowq'); ?></h4>
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
                                            __('+ %d more responses (export CSV to see all)', 'flowq'),
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
                <p><?php echo esc_html__('No analytics data available for this survey yet.', 'flowq'); ?></p>
                <p><?php echo esc_html__('Data will appear here once participants start completing the survey.', 'flowq'); ?></p>
            </div>

        <?php else: ?>
            <!-- No Survey Selected -->
            <div class="no-survey-selected">
                <div class="placeholder-content">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <h3><?php echo esc_html__('Select a Survey to View Analytics', 'flowq'); ?></h3>
                    <p><?php echo esc_html__('Choose a published survey from the dropdown above to see detailed analytics and response data.', 'flowq'); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Add inline script for chart initialization using wp_add_inline_script
if ($selected_survey_id && !empty($analytics_data)) {
    $chart_init_script = 'jQuery(document).ready(function($) {';

    // Initialize charts for multiple choice questions
    foreach ($analytics_data['question_stats'] as $index => $question_stat) {
        $question = $analytics_data['questions'][$index];

        if (!empty($question_stat['answer_distribution'])) {
            $question_id = esc_js($question['id']);
            $labels = array();
            $data = array();

            foreach ($question_stat['answer_distribution'] as $answer) {
                $labels[] = esc_js($answer['answer_text']);
                $data[] = intval($answer['count']);
            }

            $chart_init_script .= sprintf(
                'FlowQAnalytics.initChart(%s, %s, %s);',
                json_encode($question_id),
                json_encode($labels),
                json_encode($data)
            );
        }
    }

    $chart_init_script .= '});';

    // Add the inline script to the analytics script handle
    wp_add_inline_script('flowq-analytics', $chart_init_script);
}
