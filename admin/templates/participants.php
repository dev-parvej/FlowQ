<?php
/**
 * Participants Dashboard Template
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1 class="heading-primary"><?php echo esc_html__('Survey Participants', 'flowq'); ?></h1>

    <div class="participants-wrapper">
        <!-- Survey Selection -->
        <div class="participants-header">
            <div class="header-top-row">
                <form method="get" action="" class="survey-selector">
                    <?php
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce not required for page parameter in GET form
                    $page_param = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
                    ?>
                    <input type="hidden" name="page" value="<?php echo esc_attr($page_param); ?>">

                    <div class="filter-group">
                        <svg class="filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/>
                        </svg>
                        <label for="survey_id"><?php echo esc_html__('Filter by Survey:', 'flowq'); ?></label>
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
                    </div>
                </form>

                <?php if ($selected_survey_id && isset($stats)): ?>
                    <div class="participants-stats">
                        <a href="<?php echo esc_url($this->get_secure_admin_url('flowq-participants', array('survey_id' => $selected_survey_id, 'status' => 'all'))); ?>"
                           class="stat-item <?php echo ($status_filter === 'all') ? 'active' : ''; ?>">
                            <strong><?php echo esc_html($stats['total']); ?></strong>
                            <?php echo esc_html__('Total Participants', 'flowq'); ?>
                        </a>
                        <a href="<?php echo esc_url($this->get_secure_admin_url('flowq-participants', array('survey_id' => $selected_survey_id, 'status' => 'completed'))); ?>"
                           class="stat-item <?php echo ($status_filter === 'completed') ? 'active' : ''; ?>">
                            <strong><?php echo esc_html($stats['completed']); ?></strong>
                            <?php echo esc_html__('Completed', 'flowq'); ?>
                        </a>
                        <a href="<?php echo esc_url($this->get_secure_admin_url('flowq-participants', array('survey_id' => $selected_survey_id, 'status' => 'in_progress'))); ?>"
                           class="stat-item <?php echo ($status_filter === 'in_progress') ? 'active' : ''; ?>">
                            <strong><?php echo esc_html($stats['in_progress']); ?></strong>
                            <?php echo esc_html__('Incomplete', 'flowq'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($selected_survey_id && !empty($participants_data)): ?>
            <!-- Participants List -->
            <div class="participants-list">
                <div class="participants-list-header">
                    <h2 class="heading-secondary"><?php echo esc_html__('Participant Responses', 'flowq'); ?></h2>
                    <?php if ($status_filter !== 'all'): ?>
                        <span class="filter-indicator">
                            <?php
                            $filter_text = $status_filter === 'completed' ? __('Completed Only', 'flowq') : __('Incomplete Only', 'flowq');
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
                            <div class="participant-header" onclick="toggleAccordion(<?php echo absint($index); ?>)">
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
                                        <?php echo esc_html__('Started:', 'flowq'); ?>
                                        <span class="local-time" data-utc="<?php echo esc_attr($participant['started_at']); ?>">
                                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($participant['started_at']))); ?>
                                        </span>
                                    </span>
                                    <?php if ($participant['completed_at']): ?>
                                        <span class="completion-date text-small">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="opacity: 0.6;">
                                                <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
                                            </svg>
                                            <?php echo esc_html__('Completed:', 'flowq'); ?>
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
                                            <?php echo esc_html($participant_data['response_count']); ?> <?php echo esc_html__('responses', 'flowq'); ?>
                                        </span>
                                    </div>
                                    <span class="accordion-arrow">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>

                        <div class="participant-content" id="participant-content-<?php echo absint($index); ?>" style="display: none;">
                            <div class="participant-details">
                                <div class="contact-info">
                                    <h4><?php echo esc_html__('Contact Information', 'flowq'); ?></h4>
                                    <p><strong><?php echo esc_html__('Phone:', 'flowq'); ?></strong> <?php echo esc_html($participant['participant_phone']); ?></p>
                                    <?php if ($participant['participant_address']): ?>
                                        <p><strong><?php echo esc_html__('Address:', 'flowq'); ?></strong> <?php echo esc_html($participant['participant_address']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($participant['participant_zip_code'])): ?>
                                        <p><strong><?php echo esc_html__('Zip Code:', 'flowq'); ?></strong> <?php echo esc_html($participant['participant_zip_code']); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="responses-section">
                                    <div class="responses-header">
                                        <h4><?php echo esc_html__('Survey Responses', 'flowq'); ?></h4>
                                        <?php if (!empty($responses)): ?>
                                            <div class="responses-meta">
                                                <span class="response-count-badge"><?php echo absint(count($responses)); ?> <?php echo esc_html__('responses', 'flowq'); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($responses)): ?>
                                        <div class="responses-container">
                                            <?php foreach ($responses as $index => $response): ?>
                                                <div class="response-card" data-question-number="<?php echo absint($index + 1); ?>">
                                                    <div class="response-card-header">
                                                        <div class="question-info">
                                                            <span class="question-number">Q<?php echo absint($index + 1); ?></span>
                                                            <div class="question-content">
                                                                <h5 class="question-text"><?php echo esc_html($response['question']); ?></h5>
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
                                                                <span class="answer-label"><?php echo esc_html__('Selected:', 'flowq'); ?></span>
                                                                <span class="answer-value"><?php echo esc_html($response['answer']); ?></span>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if (isset($response['custom_answer']) && !empty($response['custom_answer'])): ?>
                                                            <div class="custom-answer">
                                                                <span class="answer-label"><?php echo esc_html__('Response:', 'flowq'); ?></span>
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
                                            <h5><?php echo esc_html__('No responses recorded yet', 'flowq'); ?></h5>
                                            <p><?php echo esc_html__('Responses will appear here as the participant answers questions.', 'flowq'); ?></p>
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
                                /* translators: %d: total number of participants */
                                esc_html__('Showing all %d participants', 'flowq'),
                                absint($pagination['total_items'])
                            );
                        } else {
                            echo sprintf(
                                /* translators: 1: starting participant number, 2: ending participant number, 3: total participants */
                                esc_html__('Showing %1$d-%2$d of %3$d participants', 'flowq'),
                                absint($start),
                                absint($end),
                                absint($pagination['total_items'])
                            );
                        }
                        ?>
                    </div>

                    <div class="pagination-nav">
                        <?php
                        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce not required for page parameter in GET URL
                        $page_param = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
                        $base_url = add_query_arg(array(
                            'page' => $page_param,
                            'survey_id' => $selected_survey_id,
                            'status' => $status_filter,
                            'per_page' => $pagination['per_page']
                        ), admin_url('admin.php'));

                        // Previous page
                        if ($pagination['current_page'] > 1):
                            $prev_url = add_query_arg('paged', $pagination['current_page'] - 1, $base_url);
                        ?>
                            <a href="<?php echo esc_url($prev_url); ?>" class="pagination-link prev">
                                ‹ <?php echo esc_html__('Previous', 'flowq'); ?>
                            </a>
                        <?php endif; ?>

                        <!-- Page numbers -->
                        <?php
                        $start_page = max(1, $pagination['current_page'] - 2);
                        $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);

                        for ($i = $start_page; $i <= $end_page; $i++):
                            if ($i == $pagination['current_page']):
                        ?>
                                <span class="pagination-link current"><?php echo absint($i); ?></span>
                        <?php
                            else:
                                $page_url = add_query_arg('paged', $i, $base_url);
                        ?>
                                <a href="<?php echo esc_url($page_url); ?>" class="pagination-link"><?php echo absint($i); ?></a>
                        <?php
                            endif;
                        endfor;
                        ?>

                        <!-- Next page -->
                        <?php if ($pagination['current_page'] < $pagination['total_pages']):
                            $next_url = add_query_arg('paged', $pagination['current_page'] + 1, $base_url);
                        ?>
                            <a href="<?php echo esc_url($next_url); ?>" class="pagination-link next">
                                <?php echo esc_html__('Next', 'flowq'); ?> ›
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php elseif ($selected_survey_id): ?>
            <!-- No Data Message -->
            <div class="no-data-message">
                <p><?php echo esc_html__('No participants found for this survey yet.', 'flowq'); ?></p>
                <p><?php echo esc_html__('Participants will appear here once they start taking the survey.', 'flowq'); ?></p>
            </div>

        <?php else: ?>
            <!-- No Survey Selected -->
            <div class="no-survey-selected">
                <div class="placeholder-content">
                    <span class="dashicons dashicons-groups"></span>
                    <h3><?php echo esc_html__('Select a Survey to View Participants', 'flowq'); ?></h3>
                    <p><?php echo esc_html__('Choose a survey from the dropdown above to see participant responses and details.', 'flowq'); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>