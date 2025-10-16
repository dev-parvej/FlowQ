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
    <h1 class="heading-primary"><?php echo esc_html__('Survey Participants', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h1>

    <div class="participants-wrapper">
        <!-- Survey Selection -->
        <div class="participants-header">
            <div class="header-top-row">
                <form method="get" action="" class="survey-selector">
                    <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">

                    <div class="filter-group">
                        <svg class="filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/>
                        </svg>
                        <label for="survey_id"><?php echo esc_html__('Filter by Survey:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
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
                    </div>
                </form>

                <?php if ($selected_survey_id && isset($stats)): ?>
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
                <?php endif; ?>
            </div>
        </div>

        <?php if ($selected_survey_id && !empty($participants_data)): ?>
            <!-- Participants List -->
            <div class="participants-list">
                <div class="participants-list-header">
                    <h2 class="heading-secondary"><?php echo esc_html__('Participant Responses', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>
                    <?php if ($status_filter !== 'all'): ?>
                        <span class="filter-indicator">
                            <?php
                            $filter_text = $status_filter === 'completed' ? __('Completed Only', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) : __('Incomplete Only', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
                            echo esc_html($filter_text);
                            ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="participants-grid">
                    <?php foreach ($participants_data as $index => $participant_data): ?>
                        <?php $participant = $participant_data['participant']; ?>
                        <?php $responses = $participant_data['responses']; ?>

                        <div class="participant-card">
                            <div class="participant-header" onclick="toggleAccordion(<?php echo $index; ?>)">
                                <div class="participant-info">
                                    <h3 class="text-participant-name">
                                        <?php echo esc_html($participant['participant_name']); ?>
                                        <span class="participant-email">(<?php echo esc_html($participant['participant_email']); ?>)</span>
                                    </h3>
                                </div>

                                <div class="participant-meta">
                                    <span class="start-date text-small">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="opacity: 0.6;">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>
                                        <?php echo esc_html__('Started:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                        <span class="local-time" data-utc="<?php echo esc_attr($participant['started_at']); ?>">
                                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($participant['started_at']))); ?>
                                        </span>
                                    </span>
                                    <?php if ($participant['completed_at']): ?>
                                        <span class="completion-date text-small">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="opacity: 0.6;">
                                                <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
                                            </svg>
                                            <?php echo esc_html__('Completed:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                            <span class="local-time" data-utc="<?php echo esc_attr($participant['completed_at']); ?>">
                                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($participant['completed_at']))); ?>
                                            </span>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="participant-stats">
                                    <div class="status-and-count">
                                        <span class="status-badge status-<?php echo esc_attr($participant_data['completion_status']); ?>">
                                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $participant_data['completion_status']))); ?>
                                        </span>
                                        <span class="response-count">
                                            <?php echo esc_html($participant_data['response_count']); ?> <?php echo esc_html__('responses', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                        </span>
                                    </div>
                                    <span class="accordion-arrow">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                                        </svg>
                                    </span>
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
                                    <div class="responses-header">
                                        <h4><?php echo esc_html__('Survey Responses', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h4>
                                        <?php if (!empty($responses)): ?>
                                            <div class="responses-meta">
                                                <span class="response-count-badge"><?php echo count($responses); ?> <?php echo esc_html__('responses', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($responses)): ?>
                                        <div class="responses-container">
                                            <?php foreach ($responses as $index => $response): ?>
                                                <div class="response-card" data-question-number="<?php echo $index + 1; ?>">
                                                    <div class="response-card-header">
                                                        <div class="question-info">
                                                            <span class="question-number">Q<?php echo $index + 1; ?></span>
                                                            <div class="question-content">
                                                                <h5 class="question-text"><?php echo esc_html($response['question']); ?></h5>
                                                                <div class="question-type">
                                                                    <svg class="response-type-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                                                        <?php if (isset($response['custom_answer']) && !empty($response['custom_answer'])): ?>
                                                                            <!-- Text response icon -->
                                                                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                                                        <?php elseif (isset($response['answer'])): ?>
                                                                            <!-- Multiple choice icon -->
                                                                            <path d="M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z"/>
                                                                        <?php else: ?>
                                                                            <!-- Default question icon -->
                                                                            <path d="M10,19H13V22H10V19M12,2C17.35,2.22 19.68,7.62 16.5,11.67C15.67,12.67 14.33,13.33 13.67,14.17C13,15 13,16 13,17H10C10,15.33 10,13.92 10.67,12.92C11.33,11.92 12.67,11.33 13.5,10.67C15.92,8.43 15.32,5.26 12,5A3,3 0 0,0 9,8H6A6,6 0 0,1 12,2Z"/>
                                                                        <?php endif; ?>
                                                                    </svg>
                                                                    <span class="response-type-label">
                                                                        <?php
                                                                        if (isset($response['custom_answer']) && !empty($response['custom_answer'])) {
                                                                            echo esc_html__('Text Response', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
                                                                        } elseif (isset($response['answer'])) {
                                                                            echo esc_html__('Multiple Choice', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
                                                                        } else {
                                                                            echo esc_html__('Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
                                                                        }
                                                                        ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="response-timestamp">
                                                            <span class="local-time" data-utc="<?php echo esc_attr($response['response_time']); ?>">
                                                                <?php echo esc_html(date_i18n(get_option('time_format'), strtotime($response['response_time']))); ?>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="response-card-body">
                                                        <?php if (isset($response['answer']) && !empty($response['answer'])): ?>
                                                            <div class="selected-answer">
                                                                <span class="answer-label"><?php echo esc_html__('Selected:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
                                                                <span class="answer-value"><?php echo esc_html($response['answer']); ?></span>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if (isset($response['custom_answer']) && !empty($response['custom_answer'])): ?>
                                                            <div class="custom-answer">
                                                                <span class="answer-label"><?php echo esc_html__('Response:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
                                                                <div class="custom-text"><?php echo esc_html($response['custom_answer']); ?></div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="no-responses-card">
                                            <div class="no-responses-icon">
                                                <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M9,5V9H15V5H9M12,19A3,3 0 0,1 9,16A3,3 0 0,1 12,13A3,3 0 0,1 15,16A3,3 0 0,1 12,19M12,4L13.09,8.26L17,7L14.74,10.74L19,12L14.74,13.26L17,17L13.09,15.74L12,20L10.91,15.74L7,17L9.26,13.26L5,12L9.26,10.74L7,7L10.91,8.26L12,4Z"/>
                                                </svg>
                                            </div>
                                            <h5><?php echo esc_html__('No responses recorded yet', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h5>
                                            <p><?php echo esc_html__('Responses will appear here as the participant answers questions.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
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
        arrow.classList.add('expanded');

        // Add smooth animation
        content.style.maxHeight = content.scrollHeight + 'px';

        // Add entrance animation for cards
        setTimeout(function() {
            content.style.opacity = '1';
        }, 50);
    } else {
        content.style.display = 'none';
        arrow.classList.remove('expanded');
        content.style.maxHeight = '0';
        content.style.opacity = '0';
    }
}

// Enhanced page load animation
function initializeAnimations() {
    var cards = document.querySelectorAll('.participant-card');
    cards.forEach(function(card, index) {
        card.style.setProperty('--nth-child', index);
    });
}

// Initialize animations when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeAnimations();
    initializeResponseAnimations();
    convertToLocalTime();

    // Update relative timestamps every minute
    setInterval(convertToLocalTime, 0);
});

// Enhanced time conversion with relative timestamps
function convertToLocalTime() {
    var timeElements = document.querySelectorAll('.local-time[data-utc]');

    timeElements.forEach(function(element) {
        var utcTime = element.getAttribute('data-utc');
        if (utcTime) {
            try {
                // Parse the UTC time and convert to local
                var date = new Date(utcTime + ' UTC');
                var now = new Date();
                var timeDiff = now - date;

                // Check if this is in response timestamp (use relative time)
                if (element.closest('.response-timestamp')) {
                    var relativeTime = getRelativeTime(timeDiff);
                    element.textContent = relativeTime;

                    // Add full timestamp as title
                    var fullTime = date.toLocaleString(undefined, {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                    element.title = fullTime + ' (UTC: ' + utcTime + ')';
                } else {
                    // Regular timestamp formatting for other elements
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
                }
            } catch (e) {
                console.warn('Could not parse time:', utcTime);
            }
        }
    });
}

// Helper function to get relative time
function getRelativeTime(timeDiff) {
    var seconds = Math.floor(timeDiff / 1000);
    var minutes = Math.floor(seconds / 60);
    var hours = Math.floor(minutes / 60);
    var days = Math.floor(hours / 24);

    if (seconds < 60) {
        return 'Just now';
    } else if (minutes < 60) {
        return minutes + 'm ago';
    } else if (hours < 24) {
        return hours + 'h ago';
    } else if (days < 7) {
        return days + 'd ago';
    } else {
        return Math.floor(days / 7) + 'w ago';
    }
}

// Enhanced response card animations (disabled)
function initializeResponseAnimations() {
    // Animation disabled for Survey Responses
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
/* Design System Variables */
:root {
    --color-primary: #3B82F6;
    --color-primary-hover: #2563EB;
    --color-success: #10B981;
    --color-warning: #F59E0B;
    --color-error: #EF4444;
    --color-text-primary: #374151;
    --color-text-secondary: #6B7280;
    --color-text-muted: #9CA3AF;
    --color-bg-primary: #FFFFFF;
    --color-bg-secondary: #F9FAFB;
    --color-bg-tertiary: #F3F4F6;
    --color-border: #E5E7EB;
    --color-border-light: #F3F4F6;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --radius-sm: 4px;
    --radius-md: 8px;
    --radius-lg: 12px;
    --radius-xl: 16px;
    --spacing-xs: 4px;
    --spacing-sm: 8px;
    --spacing-md: 16px;
    --spacing-lg: 24px;
    --spacing-xl: 32px;
    --spacing-2xl: 48px;
}

/* Typography System */
.heading-primary {
    font-size: 24px;
    font-weight: 600;
    color: var(--color-text-primary);
    line-height: 1.3;
    margin: 0;
}

.heading-secondary {
    font-size: 18px;
    font-weight: 500;
    color: var(--color-text-primary);
    line-height: 1.4;
    margin: 0;
}

.text-participant-name {
    font-size: 16px;
    font-weight: 500;
    color: var(--color-text-primary);
    line-height: 1.4;
    margin: 0;
}

.text-secondary {
    font-size: 14px;
    font-weight: 400;
    color: var(--color-text-secondary);
    line-height: 1.5;
}

.text-small {
    font-size: 12px;
    font-weight: 400;
    color: var(--color-text-muted);
    line-height: 1.4;
}

.text-badge {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1.2;
}

/* Main Layout */
.participants-wrapper {
    margin: var(--spacing-lg) auto;
    min-height: 600px;
}

.participants-header {
    background: var(--color-bg-primary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    box-shadow: var(--shadow-sm);
}

/* Header Components */
.header-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--spacing-lg);
}

.survey-selector {
    display: flex;
    align-items: center;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm) var(--spacing-md);
    background: var(--color-bg-primary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    width: fit-content;
    max-width: 100%;
}

.filter-icon {
    color: var(--color-text-muted);
    flex-shrink: 0;
    width: 16px;
    height: 16px;
}

.survey-selector label {
    font-weight: 500;
    color: var(--color-text-secondary);
    font-size: 14px;
    white-space: nowrap;
    margin: 0;
    flex-shrink: 0;
}

.survey-selector select {
    min-width: 200px;
    padding: var(--spacing-xs) var(--spacing-sm);
    border: none;
    border-radius: var(--radius-sm);
    background: transparent;
    font-size: 14px;
    color: var(--color-text-primary);
    transition: all 0.2s ease;
    outline: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right var(--spacing-xs) center;
    background-repeat: no-repeat;
    background-size: 16px 16px;
    padding-right: var(--spacing-xl);
}

.filter-group:hover {
    border-color: var(--color-primary);
    box-shadow: var(--shadow-md);
}

.filter-group:focus-within {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.survey-selector select:focus {
    outline: none;
    background: var(--color-bg-tertiary);
}

.controls-right {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: var(--spacing-lg);
    flex-wrap: wrap;
}

/* Dashboard Metrics */
.participants-stats {
    display: flex;
    gap: var(--spacing-md);
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    padding: var(--spacing-md) var(--spacing-lg);
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-border);
    background: var(--color-bg-primary);
    transition: all 0.2s ease;
    cursor: pointer;
    min-width: 120px;
    box-shadow: var(--shadow-sm);
}

.stat-item strong {
    font-size: 24px;
    font-weight: 700;
    color: var(--color-text-primary);
    margin-bottom: var(--spacing-xs);
    display: block;
}

.stat-item span:not(strong) {
    font-size: 12px;
    font-weight: 500;
    color: var(--color-text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: center;
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--color-primary);
    text-decoration: none;
}

.stat-item.active {
    background: var(--color-primary);
    border-color: var(--color-primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    color: white;
}

.stat-item.active strong,
.stat-item.active span {
    color: white;
}

.stat-item.active:hover {
    background: var(--color-primary-hover);
    border-color: var(--color-primary-hover);
}

/* Per-page Selector */
.per-page-selector {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-size: 14px;
}

.per-page-selector label {
    font-weight: 600;
    color: var(--color-text-secondary);
}

.per-page-selector select {
    padding: var(--spacing-xs) var(--spacing-sm);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    background: var(--color-bg-primary);
    font-size: 14px;
    color: var(--color-text-primary);
    min-height: 32px;
    transition: all 0.2s ease;
}

.per-page-selector select:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

/* Participants List Layout */
.participants-list {
    margin-bottom: var(--spacing-xl);
    padding: var(--spacing-lg);
}

.participants-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.participants-list h2 {
    margin: 0;
    color: var(--color-text-primary);
    font-size: 18px;
    font-weight: 600;
}

.filter-indicator {
    background: var(--color-warning);
    color: white;
    padding: var(--spacing-xs) var(--spacing-md);
    border-radius: var(--radius-xl);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Modern Card Grid Layout */
.participants-grid {
    display: grid;
    gap: var(--spacing-lg);
    margin-top: var(--spacing-lg);
}

.participant-card {
    background: var(--color-bg-primary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: all 0.2s ease;
    position: relative;
}

.participant-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--color-primary);
}

/* Card Header */
.participant-header {
    padding: var(--spacing-lg);
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid var(--color-border-light);
    position: relative;
}

.participant-header:hover {
    background: var(--color-bg-tertiary);
}

.participant-info {
    margin-bottom: var(--spacing-md);
}

.participant-info h3 {
    margin: 0 0 var(--spacing-xs) 0;
    color: var(--color-text-primary);
    font-size: 16px;
    font-weight: 500;
    line-height: 1.4;
}

.participant-email {
    color: var(--color-text-secondary);
    font-weight: 400;
    font-size: 14px;
    margin-left: var(--spacing-xs);
}

.participant-meta {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
    font-size: 13px;
    color: var(--color-text-muted);
    margin-bottom: var(--spacing-md);
}

.participant-meta span {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
}

/* Card Status and Stats */
.participant-stats {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: var(--spacing-sm);
}

.status-and-count {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.status-badge {
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-xl);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.status-badge.status-completed {
    background: var(--color-success);
    color: white;
}

.status-badge.status-incomplete,
.status-badge.status-in_progress {
    background: var(--color-warning);
    color: white;
}

.response-count {
    color: var(--color-text-secondary);
    font-size: 12px;
    font-weight: 500;
    padding: var(--spacing-xs) var(--spacing-sm);
    background: var(--color-bg-tertiary);
    border-radius: var(--radius-sm);
    white-space: nowrap;
}

.accordion-arrow {
    font-size: 14px;
    color: var(--color-text-muted);
    transition: transform 0.2s ease;
    padding: var(--spacing-xs);
    border-radius: var(--radius-sm);
    background: var(--color-bg-tertiary);
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Card Content Section */
.participant-content {
    padding: var(--spacing-lg);
    background: var(--color-bg-primary);
    border-top: 1px solid var(--color-border-light);
}

.participant-details {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: var(--spacing-xl);
}

.contact-info h4,
.responses-section h4 {
    margin-bottom: var(--spacing-md);
    color: var(--color-text-primary);
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.contact-info p {
    margin-bottom: var(--spacing-sm);
    font-size: 14px;
    color: var(--color-text-secondary);
    line-height: 1.5;
}

.contact-info strong {
    color: var(--color-text-primary);
    font-weight: 500;
}

/* Enhanced Responses Section */
.responses-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.response-count-badge {
    background: var(--color-primary);
    color: white;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-xl);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.responses-container {
    max-height: 500px;
    overflow-y: auto;
    padding-right: var(--spacing-xs);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.responses-container::-webkit-scrollbar {
    width: 6px;
}

.responses-container::-webkit-scrollbar-track {
    background: var(--color-bg-tertiary);
    border-radius: 3px;
}

.responses-container::-webkit-scrollbar-thumb {
    background: var(--color-border);
    border-radius: 3px;
}

.responses-container::-webkit-scrollbar-thumb:hover {
    background: var(--color-text-muted);
}

/* Individual Response Cards */
.response-card {
    background: var(--color-bg-primary);
    border: 1px solid var(--color-border);
    border-left: 4px solid var(--color-primary);
    border-radius: var(--radius-lg);
    transition: all 0.2s ease;
    box-shadow: var(--shadow-sm);
}

.response-card:hover {
    border-left-color: var(--color-primary-hover);
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
}

.response-card:nth-child(even) {
    background: var(--color-bg-secondary);
}

/* Response Card Header */
.response-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: var(--spacing-md);
    border-bottom: 1px solid var(--color-border-light);
    gap: var(--spacing-md);
}

.question-info {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-md);
    flex: 1;
}

.question-number {
    background: var(--color-primary);
    color: white;
    font-weight: 700;
    font-size: 12px;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-lg);
    min-width: 32px;
    text-align: center;
    flex-shrink: 0;
}

.question-content {
    flex: 1;
}

.question-text {
    font-size: 16px;
    font-weight: 500;
    color: var(--color-text-primary);
    line-height: 1.5;
    margin: 0 0 var(--spacing-xs) 0;
}

.question-type {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    margin-top: var(--spacing-xs);
}

.response-type-icon {
    color: var(--color-text-muted);
    flex-shrink: 0;
}

.response-type-label {
    font-size: 12px;
    color: var(--color-text-muted);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.response-timestamp {
    font-size: 11px;
    color: var(--color-text-muted);
    font-weight: 500;
    text-align: right;
    flex-shrink: 0;
    background: var(--color-bg-tertiary);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
}

/* Response Card Body */
.response-card-body {
    padding: var(--spacing-md);
}

.selected-answer,
.custom-answer {
    margin-bottom: var(--spacing-sm);
}

.selected-answer:last-child,
.custom-answer:last-child {
    margin-bottom: 0;
}

.answer-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--color-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: var(--spacing-xs);
}

.answer-value {
    font-size: 14px;
    font-weight: 500;
    color: var(--color-text-primary);
    background: var(--color-bg-tertiary);
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: var(--radius-sm);
    display: inline-block;
    border: 1px solid var(--color-border);
    line-height: 1.4;
}

.custom-text {
    font-size: 14px;
    color: var(--color-text-secondary);
    background: var(--color-bg-tertiary);
    padding: var(--spacing-md);
    border-radius: var(--radius-sm);
    border: 1px solid var(--color-border);
    line-height: 1.6;
    white-space: pre-wrap;
    word-wrap: break-word;
}

/* Enhanced Empty State */
.no-responses-card {
    background: var(--color-bg-primary);
    border: 2px dashed var(--color-border);
    border-radius: var(--radius-lg);
    padding: var(--spacing-2xl);
    text-align: center;
    margin: var(--spacing-lg) 0;
}

.no-responses-icon {
    color: var(--color-text-muted);
    margin-bottom: var(--spacing-md);
    opacity: 0.6;
}

.no-responses-card h5 {
    color: var(--color-text-primary);
    font-size: 16px;
    font-weight: 500;
    margin: 0 0 var(--spacing-sm) 0;
}

.no-responses-card p {
    color: var(--color-text-secondary);
    font-size: 14px;
    line-height: 1.5;
    margin: 0;
}

/* Empty States */
.no-data-message, .no-survey-selected {
    text-align: center;
    padding: var(--spacing-2xl) var(--spacing-lg);
    background: var(--color-bg-primary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    margin: var(--spacing-xl) 0;
    box-shadow: var(--shadow-sm);
}

.no-survey-selected .placeholder-content .dashicons {
    font-size: 64px;
    color: var(--color-text-muted);
    margin-bottom: var(--spacing-lg);
    opacity: 0.6;
}

.no-survey-selected h3 {
    color: var(--color-text-primary);
    margin-bottom: var(--spacing-md);
    font-size: 18px;
    font-weight: 500;
}

.no-survey-selected p {
    color: var(--color-text-secondary);
    max-width: 500px;
    margin: 0 auto;
    line-height: 1.6;
}

.no-data-message p {
    color: var(--color-text-secondary);
    margin-bottom: var(--spacing-sm);
    font-size: 16px;
    line-height: 1.5;
}

/* Modern Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: var(--spacing-xl);
    padding: var(--spacing-lg);
    background: var(--color-bg-primary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
}

.pagination-info {
    color: var(--color-text-secondary);
    font-size: 14px;
    font-weight: 500;
}

.pagination-nav {
    display: flex;
    gap: var(--spacing-xs);
    align-items: center;
}

.pagination-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-sm) var(--spacing-md);
    color: var(--color-primary);
    text-decoration: none;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    background: var(--color-bg-primary);
    transition: all 0.2s ease;
    font-size: 14px;
    font-weight: 500;
    min-width: 36px;
    height: 36px;
    text-align: center;
}

.pagination-link:hover {
    background: var(--color-bg-tertiary);
    border-color: var(--color-primary);
    color: var(--color-primary-hover);
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.pagination-link.current {
    background: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
    cursor: default;
    box-shadow: var(--shadow-sm);
}

.pagination-link.current:hover {
    transform: none;
    background: var(--color-primary);
}

.pagination-link.prev,
.pagination-link.next {
    font-weight: 600;
    padding: var(--spacing-sm) var(--spacing-md);
}

/* Mobile-First Responsive Design */
@media (max-width: 1024px) {
    .participants-grid {
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: var(--spacing-md);
    }

    .stat-item {
        min-width: 100px;
        padding: var(--spacing-sm) var(--spacing-md);
    }

    .stat-item strong {
        font-size: 20px;
    }
}

@media (max-width: 768px) {
    .participants-wrapper {
        margin: var(--spacing-md) 0;
        padding: 0 var(--spacing-md);
    }

    .participants-header {
        padding: var(--spacing-md);
    }

    .header-top-row {
        flex-direction: column;
        align-items: stretch;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-md);
    }

    .survey-selector {
        width: 100%;
    }

    .filter-group {
        width: 100%;
        max-width: none;
    }

    .survey-selector select {
        min-width: auto;
        flex: 1;
    }

    .controls-right {
        justify-content: center;
    }

    .participants-stats {
        flex-direction: column;
        gap: var(--spacing-sm);
    }

    .stat-item {
        flex-direction: row;
        justify-content: space-between;
        text-align: left;
        min-width: auto;
    }

    .stat-item strong {
        font-size: 18px;
        margin-bottom: 0;
    }

    .participants-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-md);
    }

    .participant-header {
        flex-direction: column;
        gap: var(--spacing-sm);
        align-items: stretch;
    }

    .participant-stats {
        flex-direction: column;
        align-items: stretch;
        gap: var(--spacing-sm);
    }

    .status-and-count {
        justify-content: space-between;
        width: 100%;
    }

    .accordion-arrow {
        align-self: center;
    }

    .participant-details {
        grid-template-columns: 1fr;
        gap: var(--spacing-lg);
    }

    /* Mobile Response Cards */
    .response-card-header {
        flex-direction: column;
        gap: var(--spacing-sm);
        align-items: stretch;
    }

    .question-info {
        flex-direction: column;
        gap: var(--spacing-sm);
    }

    .question-number {
        align-self: flex-start;
        min-width: 24px;
        font-size: 11px;
        padding: var(--spacing-xs);
    }

    .response-timestamp {
        align-self: flex-end;
        text-align: left;
    }

    .responses-container {
        max-height: 300px;
    }

    .pagination-wrapper {
        flex-direction: column;
        gap: var(--spacing-md);
        text-align: center;
        padding: var(--spacing-md);
    }

    .pagination-nav {
        justify-content: center;
        flex-wrap: wrap;
    }

    .pagination-link {
        min-width: 32px;
        height: 32px;
        padding: var(--spacing-xs) var(--spacing-sm);
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .participants-wrapper {
        padding: 0 var(--spacing-sm);
    }

    .participants-header {
        padding: var(--spacing-sm);
    }

    .participant-header {
        padding: var(--spacing-sm);
    }

    .participant-content {
        padding: var(--spacing-sm);
    }

    .pagination-wrapper {
        padding: var(--spacing-sm);
    }
}

/* Enhanced Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.participant-card {
    animation: fadeIn 0.3s ease-out;
}

.participant-card:nth-child(n) {
    animation-delay: calc(0.05s * var(--nth-child, 0));
}

/* Response Card Animations (disabled) */

.response-card:hover .question-number {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

.response-card:hover .response-type-icon {
    color: var(--color-primary);
    transition: color 0.2s ease;
}

/* Accordion Animation */
.participant-content {
    transition: all 0.3s ease;
    overflow: hidden;
}

.accordion-arrow {
    transition: transform 0.2s ease;
}

.accordion-arrow.expanded {
    transform: rotate(180deg);
}

/* Focus States for Accessibility */
.survey-selector select:focus,
.per-page-selector select:focus {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}

.participant-header:focus-visible {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}

.pagination-link:focus-visible {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}
}
</style>