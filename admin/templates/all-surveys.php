<?php
/**
 * All Surveys Admin Template
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div>
    <div class="surveys-wrapper">
        <div class="surveys-header">
            <div class="header-content">
                <h1 class="page-title">
                    <?php echo esc_html__('Surveys', 'flowq'); ?>
                </h1>
                <p class="page-subtitle">
                    <?php echo esc_html__('Manage and monitor your survey campaigns', 'flowq'); ?>
                </p>
            </div>
            <div class="header-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=flowq-add')); ?>" class="add-survey-button">
                    <svg class="add-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                    </svg>
                    <span><?php echo esc_html__('Add New Survey', 'flowq'); ?></span>
                </a>
            </div>
        </div>

        <?php if (empty($surveys)): ?>
            <div class="empty-state">
                <div class="empty-state-content">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">
                        <?php echo esc_html__('No surveys found', 'flowq'); ?>
                    </h3>
                    <p class="empty-state-description">
                        <?php echo esc_html__('Get started by creating your first survey to collect responses and insights from your audience.', 'flowq'); ?>
                    </p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=flowq-add')); ?>" class="empty-state-button">
                        <svg class="button-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                        </svg>
                        <?php echo esc_html__('Create your first survey', 'flowq'); ?>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="surveys-content">
                <div class="surveys-table-header">
                    <h2 class="table-title"><?php echo esc_html__('All Surveys', 'flowq'); ?></h2>
                    <div class="table-stats">
                        <span class="survey-count"><?php echo count($surveys); ?> <?php echo esc_html__('surveys', 'flowq'); ?></span>
                    </div>
                </div>

                <div class="surveys-grid">
                    <?php foreach ($surveys as $survey):
                        $survey_manager = new FlowQ_Survey_Manager();
                        $stats = $survey_manager->get_survey_statistics($survey['id']);

                        // Question count is already included from the JOIN query
                        $question_count = isset($survey['question_count']) ? intval($survey['question_count']) : 0;
                    ?>
                        <div class="survey-card">
                            <div class="survey-card-header">
                                <div class="survey-info">
                                    <h3 class="survey-title">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=flowq-add&survey_id=' . $survey['id'])); ?>">
                                            <?php echo esc_html($survey['title']); ?>
                                        </a>
                                    </h3>
                                    <div class="survey-meta">
                                        <span class="survey-status status-<?php echo esc_attr($survey['status']); ?>">
                                            <svg class="status-icon" width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                                <?php if ($survey['status'] === 'published'): ?>
                                                    <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
                                                <?php elseif ($survey['status'] === 'draft'): ?>
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                                <?php else: ?>
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                                                <?php endif; ?>
                                            </svg>
                                            <?php echo esc_html(ucfirst($survey['status'])); ?>
                                        </span>
                                        <span class="survey-date">
                                            <?php echo esc_html__('Created:', 'flowq'); ?>
                                            <?php echo esc_html(mysql2date(get_option('date_format'), $survey['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="survey-card-details">
                                <?php if (!empty($survey['form_header'])): ?>
                                    <div class="survey-detail-item">
                                        <span class="detail-label"><?php echo esc_html__('Form Header:', 'flowq'); ?></span>
                                        <span class="detail-value"><?php echo esc_html($survey['form_header']); ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($survey['status'] === 'published'): ?>
                                    <div class="survey-detail-item">
                                        <span class="detail-label"><?php echo esc_html__('Shortcode:', 'flowq'); ?></span>
                                        <code class="detail-shortcode">[dynamic_survey id="<?php echo esc_attr($survey['id']); ?>"]</code>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($survey['thank_you_page_slug'])): ?>
                                    <div class="survey-detail-item">
                                        <span class="detail-label"><?php echo esc_html__('Thank You Page:', 'flowq'); ?></span>
                                        <span class="detail-value"><?php echo esc_html($survey['thank_you_page_slug']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="survey-card-stats">
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo esc_html($question_count); ?></div>
                                    <div class="stat-label"><?php echo esc_html__('Questions', 'flowq'); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo esc_html($stats['total_participants']); ?></div>
                                    <div class="stat-label"><?php echo esc_html__('Participants', 'flowq'); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo esc_html($stats['completed_participants']); ?></div>
                                    <div class="stat-label"><?php echo esc_html__('Completed', 'flowq'); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo esc_html($stats['completion_rate']); ?>%</div>
                                    <div class="stat-label"><?php echo esc_html__('Rate', 'flowq'); ?></div>
                                </div>
                            </div>

                            <div class="survey-card-actions">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=flowq-add&survey_id=' . $survey['id'])); ?>"
                                   class="action-button action-edit">
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                    </svg>
                                    <?php echo esc_html__('Edit', 'flowq'); ?>
                                </a>

                                <a href="<?php echo esc_url(admin_url('admin.php?page=flowq-questions&survey_id=' . $survey['id'])); ?>"
                                   class="action-button action-questions">
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8 6h13v2H8zm0 4h13v2H8zm0 4h13v2H8zM4 6h2v2H4zm0 4h2v2H4zm0 4h2v2H4z"/>
                                    </svg>
                                    <?php echo esc_html__('Questions', 'flowq'); ?>
                                </a>

                                <?php if ($survey['status'] === 'published'): ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=flowq-analytics&survey_id=' . $survey['id'])); ?>"
                                   class="action-button action-analytics">
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                                    </svg>
                                    <?php echo esc_html__('Analytics', 'flowq'); ?>
                                </a>
                                <?php endif; ?>

                                <?php if ($survey['status'] !== 'published'): ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=flowq_survey_action&survey_action=delete&survey_id=' . $survey['id']), 'survey_action')); ?>"
                                   class="action-button action-delete"
                                   onclick="return confirm('<?php echo esc_html__('Are you sure you want to delete this survey? This action cannot be undone.', 'flowq'); ?>')">
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                    </svg>
                                    <?php echo esc_html__('Delete', 'flowq'); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

