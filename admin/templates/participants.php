<?php
/**
 * Participants Dashboard Template
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html__('Survey Participants', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h1>

    <div class="participants-wrapper">
        <!-- Survey Selection -->
        <div class="participants-header">
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

            <?php if ($selected_survey_id && isset($stats)): ?>
                <div class="participants-controls">
                    <div class="participants-stats">
                        <a href="<?php echo esc_url(add_query_arg(array('page' => $_GET['page'], 'survey_id' => $selected_survey_id, 'status' => 'all'), admin_url('admin.php'))); ?>"
                           class="stat-item <?php echo ($status_filter === 'all') ? 'active' : ''; ?>">
                            <strong><?php echo esc_html($stats['total']); ?></strong>
                            <?php echo esc_html__('Total Participants', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(array('page' => $_GET['page'], 'survey_id' => $selected_survey_id, 'status' => 'completed'), admin_url('admin.php'))); ?>"
                           class="stat-item <?php echo ($status_filter === 'completed') ? 'active' : ''; ?>">
                            <strong><?php echo esc_html($stats['completed']); ?></strong>
                            <?php echo esc_html__('Completed', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(array('page' => $_GET['page'], 'survey_id' => $selected_survey_id, 'status' => 'in_progress'), admin_url('admin.php'))); ?>"
                           class="stat-item <?php echo ($status_filter === 'in_progress') ? 'active' : ''; ?>">
                            <strong><?php echo esc_html($stats['in_progress']); ?></strong>
                            <?php echo esc_html__('Incomplete', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </a>
                    </div>

                    <div class="controls-right">

                        <div class="per-page-selector">
                            <label for="per_page"><?php echo esc_html__('Show:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                            <select name="per_page" id="per_page" onchange="changePerPage(this.value)">
                                <option value="10" <?php selected($pagination['per_page'], '10'); ?>>10</option>
                                <option value="20" <?php selected($pagination['per_page'], '20'); ?>>20</option>
                                <option value="30" <?php selected($pagination['per_page'], '30'); ?>>30</option>
                                <option value="40" <?php selected($pagination['per_page'], '40'); ?>>40</option>
                                <option value="all" <?php selected($pagination['per_page'], 'all'); ?>><?php echo esc_html__('All', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($selected_survey_id && !empty($participants_data)): ?>
            <!-- Participants List -->
            <div class="participants-list">
                <div class="participants-list-header">
                    <h2><?php echo esc_html__('Participant Responses', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>
                    <?php if ($status_filter !== 'all'): ?>
                        <span class="filter-indicator">
                            <?php
                            $filter_text = $status_filter === 'completed' ? __('Completed Only', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) : __('Incomplete Only', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
                            echo esc_html($filter_text);
                            ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php foreach ($participants_data as $index => $participant_data): ?>
                    <?php $participant = $participant_data['participant']; ?>
                    <?php $responses = $participant_data['responses']; ?>

                    <div class="participant-card">
                        <div class="participant-header" onclick="toggleAccordion(<?php echo $index; ?>)">
                            <div class="participant-info">
                                <h3>
                                    <?php echo esc_html($participant['participant_name']); ?>
                                    <span class="participant-email">(<?php echo esc_html($participant['participant_email']); ?>)</span>
                                </h3>
                                <div class="participant-meta">
                                    <span class="start-date">
                                        <?php echo esc_html__('Started:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                        <span class="local-time" data-utc="<?php echo esc_attr($participant['started_at']); ?>">
                                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($participant['started_at']))); ?>
                                        </span>
                                    </span>
                                    <?php if ($participant['completed_at']): ?>
                                        <span class="completion-date">
                                            <?php echo esc_html__('Completed:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                            <span class="local-time" data-utc="<?php echo esc_attr($participant['completed_at']); ?>">
                                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($participant['completed_at']))); ?>
                                            </span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="participant-stats">
                                <span class="status-badge status-<?php echo esc_attr($participant_data['completion_status']); ?>">
                                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $participant_data['completion_status']))); ?>
                                </span>
                                <span class="response-count">
                                    <?php echo esc_html($participant_data['response_count']); ?>
                                    <?php echo esc_html__('responses', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                </span>
                                <span class="accordion-arrow">▼</span>
                            </div>
                        </div>

                        <div class="participant-content" id="participant-content-<?php echo $index; ?>" style="display: none;">
                            <div class="participant-details">
                                <div class="contact-info">
                                    <h4><?php echo esc_html__('Contact Information', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h4>
                                    <p><strong><?php echo esc_html__('Phone:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></strong> <?php echo esc_html($participant['participant_phone']); ?></p>
                                    <?php if ($participant['participant_address']): ?>
                                        <p><strong><?php echo esc_html__('Address:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></strong> <?php echo esc_html($participant['participant_address']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($participant['participant_zip_code'])): ?>
                                        <p><strong><?php echo esc_html__('Zip Code:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></strong> <?php echo esc_html($participant['participant_zip_code']); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="responses-section">
                                    <h4><?php echo esc_html__('Survey Responses', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h4>

                                    <?php if (!empty($responses)): ?>
                                        <div class="responses-list">
                                            <?php foreach ($responses as $response): ?>
                                                <div class="response-item">
                                                    <div class="question-title">
                                                        <strong><?php echo esc_html($response['question']); ?></strong>
                                                    </div>

                                                    <div class="response-answer">
                                                        <?php if (isset($response['answer'])): ?>
                                                            <span class="selected-answer"><?php echo esc_html($response['answer']); ?></span>
                                                        <?php endif; ?>

                                                        <?php if (isset($response['custom_answer'])): ?>
                                                            <div class="custom-text"><?php echo esc_html($response['custom_answer']); ?></div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="response-time">
                                                        <span class="local-time" data-utc="<?php echo esc_attr($response['response_time']); ?>">
                                                            <?php echo esc_html(date_i18n(get_option('time_format'), strtotime($response['response_time']))); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="no-responses"><?php echo esc_html__('No responses recorded yet.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        <?php
                        $start = ($pagination['current_page'] - 1) * (int)$pagination['per_page'] + 1;
                        $end = min($pagination['current_page'] * (int)$pagination['per_page'], $pagination['total_items']);

                        if ($pagination['per_page'] === 'all') {
                            echo sprintf(
                                esc_html__('Showing all %d participants', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                                $pagination['total_items']
                            );
                        } else {
                            echo sprintf(
                                esc_html__('Showing %d-%d of %d participants', WP_DYNAMIC_SURVEY_TEXT_DOMAIN),
                                $start,
                                $end,
                                $pagination['total_items']
                            );
                        }
                        ?>
                    </div>

                    <div class="pagination-nav">
                        <?php
                        $base_url = add_query_arg(array(
                            'page' => $_GET['page'],
                            'survey_id' => $selected_survey_id,
                            'status' => $status_filter,
                            'per_page' => $pagination['per_page']
                        ), admin_url('admin.php'));

                        // Previous page
                        if ($pagination['current_page'] > 1):
                            $prev_url = add_query_arg('paged', $pagination['current_page'] - 1, $base_url);
                        ?>
                            <a href="<?php echo esc_url($prev_url); ?>" class="pagination-link prev">
                                ‹ <?php echo esc_html__('Previous', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </a>
                        <?php endif; ?>

                        <!-- Page numbers -->
                        <?php
                        $start_page = max(1, $pagination['current_page'] - 2);
                        $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);

                        for ($i = $start_page; $i <= $end_page; $i++):
                            if ($i == $pagination['current_page']):
                        ?>
                                <span class="pagination-link current"><?php echo $i; ?></span>
                        <?php
                            else:
                                $page_url = add_query_arg('paged', $i, $base_url);
                        ?>
                                <a href="<?php echo esc_url($page_url); ?>" class="pagination-link"><?php echo $i; ?></a>
                        <?php
                            endif;
                        endfor;
                        ?>

                        <!-- Next page -->
                        <?php if ($pagination['current_page'] < $pagination['total_pages']):
                            $next_url = add_query_arg('paged', $pagination['current_page'] + 1, $base_url);
                        ?>
                            <a href="<?php echo esc_url($next_url); ?>" class="pagination-link next">
                                <?php echo esc_html__('Next', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?> ›
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php elseif ($selected_survey_id): ?>
            <!-- No Data Message -->
            <div class="no-data-message">
                <p><?php echo esc_html__('No participants found for this survey yet.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                <p><?php echo esc_html__('Participants will appear here once they start taking the survey.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
            </div>

        <?php else: ?>
            <!-- No Survey Selected -->
            <div class="no-survey-selected">
                <div class="placeholder-content">
                    <span class="dashicons dashicons-groups"></span>
                    <h3><?php echo esc_html__('Select a Survey to View Participants', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>
                    <p><?php echo esc_html__('Choose a survey from the dropdown above to see participant responses and details.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<script type="text/javascript">
function toggleAccordion(index) {
    var content = document.getElementById('participant-content-' + index);
    var arrow = content.parentElement.querySelector('.accordion-arrow');

    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        arrow.textContent = '▲';
        arrow.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        arrow.textContent = '▼';
        arrow.style.transform = 'rotate(0deg)';
    }
}

// Convert UTC times to local timezone
function convertToLocalTime() {
    var timeElements = document.querySelectorAll('.local-time[data-utc]');

    timeElements.forEach(function(element) {
        var utcTime = element.getAttribute('data-utc');
        if (utcTime) {
            try {
                // Parse the UTC time and convert to local
                var date = new Date(utcTime + ' UTC');

                // Format the local time
                var options = {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                };

                // Check if this is just a time (no date needed)
                if (element.closest('.response-time')) {
                    options = {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    };
                }

                var localTimeString = date.toLocaleString(undefined, options);
                element.textContent = localTimeString;

                // Add timezone info as title
                element.title = 'Your local time (UTC time: ' + utcTime + ')';

            } catch (e) {
                console.warn('Could not parse time:', utcTime);
            }
        }
    });
}

// Run conversion when page loads
document.addEventListener('DOMContentLoaded', convertToLocalTime);

// Handle per-page change
function changePerPage(value) {
    var currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('per_page', value);
    currentUrl.searchParams.delete('paged'); // Reset to first page when changing per-page
    window.location.href = currentUrl.toString();
}

</script>

<style>
.participants-wrapper {
    max-width: 100%;
    margin: 20px 0;
}

.participants-header {
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

.participants-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.controls-right {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.participants-stats {
    display: flex;
    gap: 20px;
}

.per-page-selector {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.per-page-selector label {
    font-weight: 600;
    color: #666;
}

.per-page-selector select {
    padding: 4px 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
}

.stat-item {
    color: #666;
    font-size: 14px;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 6px;
    border: 1px solid transparent;
    transition: all 0.3s ease;
    cursor: pointer;
}

.stat-item:hover {
    background: #f0f6fc;
    border-color: #2271b1;
    color: #2271b1;
    text-decoration: none;
}

.stat-item.active {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}

.stat-item.active:hover {
    background: #135e96;
    border-color: #135e96;
    color: #fff;
}

.participants-list {
    margin-bottom: 30px;
}

.participants-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.participants-list h2 {
    margin: 0;
    color: #1d2327;
}

.filter-indicator {
    background: #fff3cd;
    color: #856404;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #ffeaa7;
}

.participant-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 15px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.participant-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.participant-header:hover {
    background: #e9ecef;
}

.participant-info h3 {
    margin: 0 0 8px 0;
    color: #1d2327;
    font-size: 16px;
}

.participant-email {
    color: #666;
    font-weight: normal;
    font-size: 14px;
}

.participant-meta {
    display: flex;
    gap: 15px;
    font-size: 13px;
    color: #666;
}

.participant-stats {
    display: flex;
    align-items: center;
    gap: 15px;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-badge.status-incomplete {
    background: #fff3cd;
    color: #856404;
}

.response-count {
    color: #666;
    font-size: 13px;
}

.accordion-arrow {
    font-size: 12px;
    color: #666;
    transition: transform 0.3s ease;
}

.participant-content {
    padding: 20px;
    background: #fff;
}

.participant-details {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 30px;
}

.contact-info h4,
.responses-section h4 {
    margin-bottom: 15px;
    color: #1d2327;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.contact-info p {
    margin-bottom: 8px;
    font-size: 14px;
}

.responses-list {
    max-height: 400px;
    overflow-y: auto;
}

.response-item {
    padding: 15px;
    margin-bottom: 12px;
    background: #f9f9f9;
    border-left: 3px solid #2271b1;
    border-radius: 4px;
}

.question-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}


.response-answer {
    margin-bottom: 8px;
}

.selected-answer {
    font-weight: 600;
    color: #1d2327;
}

.custom-text {
    font-style: italic;
    color: #666;
    margin-top: 5px;
    padding: 8px;
    background: #fff;
    border-radius: 3px;
    border: 1px solid #ddd;
}

.response-time {
    font-size: 12px;
    color: #666;
    text-align: right;
}

.no-responses {
    color: #666;
    font-style: italic;
    text-align: center;
    padding: 20px;
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

/* Pagination Styles */
.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    padding: 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.pagination-info {
    color: #666;
    font-size: 14px;
}

.pagination-nav {
    display: flex;
    gap: 5px;
    align-items: center;
}

.pagination-link {
    display: inline-block;
    padding: 8px 12px;
    color: #2271b1;
    text-decoration: none;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
    transition: all 0.3s ease;
    font-size: 14px;
    min-width: 20px;
    text-align: center;
}

.pagination-link:hover {
    background: #f0f6fc;
    border-color: #2271b1;
    color: #135e96;
    text-decoration: none;
}

.pagination-link.current {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
    cursor: default;
}

.pagination-link.prev,
.pagination-link.next {
    font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .participants-header {
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

    .participants-controls {
        flex-direction: column;
        align-items: stretch;
    }

    .controls-right {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }

    .participants-stats {
        justify-content: center;
        flex-wrap: wrap;
    }

    .participant-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }

    .participant-meta {
        flex-direction: column;
        gap: 5px;
    }

    .participant-details {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .pagination-wrapper {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }

    .pagination-nav {
        justify-content: center;
    }

        order: -1;
    }
}
</style>