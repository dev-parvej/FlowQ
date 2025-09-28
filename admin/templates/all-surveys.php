<?php
/**
 * All Surveys Admin Template
 *
 * @package WP_Dynamic_Survey
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
                    <?php echo esc_html__('Surveys', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                </h1>
                <p class="page-subtitle">
                    <?php echo esc_html__('Manage and monitor your survey campaigns', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                </p>
            </div>
            <div class="header-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-add')); ?>" class="add-survey-button">
                    <svg class="add-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                    </svg>
                    <span><?php echo esc_html__('Add New Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
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
                        <?php echo esc_html__('No surveys found', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </h3>
                    <p class="empty-state-description">
                        <?php echo esc_html__('Get started by creating your first survey to collect responses and insights from your audience.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-add')); ?>" class="empty-state-button">
                        <svg class="button-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                        </svg>
                        <?php echo esc_html__('Create your first survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="surveys-content">
                <div class="surveys-table-header">
                    <h2 class="table-title"><?php echo esc_html__('All Surveys', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>
                    <div class="table-stats">
                        <span class="survey-count"><?php echo count($surveys); ?> <?php echo esc_html__('surveys', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
                    </div>
                </div>

                <div class="surveys-grid">
                    <?php foreach ($surveys as $survey):
                        $survey_manager = new WP_Dynamic_Survey_Manager();
                        $stats = $survey_manager->get_survey_statistics($survey['id']);
                    ?>
                        <div class="survey-card">
                            <div class="survey-card-header">
                                <div class="survey-info">
                                    <h3 class="survey-title">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-add&survey_id=' . $survey['id'])); ?>">
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
                                            <?php echo esc_html__('Created:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                            <?php echo esc_html(mysql2date(get_option('date_format'), $survey['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="survey-card-stats">
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo esc_html($stats['total_participants']); ?></div>
                                    <div class="stat-label"><?php echo esc_html__('Participants', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo esc_html($stats['completed_participants']); ?></div>
                                    <div class="stat-label"><?php echo esc_html__('Completed', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo esc_html($stats['completion_rate']); ?>%</div>
                                    <div class="stat-label"><?php echo esc_html__('Rate', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></div>
                                </div>
                            </div>

                            <div class="survey-card-actions">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-add&survey_id=' . $survey['id'])); ?>"
                                   class="action-button action-edit">
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                    </svg>
                                    <?php echo esc_html__('Edit', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                </a>

                                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-questions&survey_id=' . $survey['id'])); ?>"
                                   class="action-button action-questions">
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8 6h13v2H8zm0 4h13v2H8zm0 4h13v2H8zM4 6h2v2H4zm0 4h2v2H4zm0 4h2v2H4z"/>
                                    </svg>
                                    <?php echo esc_html__('Questions', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                </a>

                                <?php if ($survey['status'] === 'published'): ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-analytics&survey_id=' . $survey['id'])); ?>"
                                   class="action-button action-analytics">
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                                    </svg>
                                    <?php echo esc_html__('Analytics', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                </a>
                                <?php endif; ?>

                                <?php if ($survey['status'] !== 'published'): ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=wp_dynamic_survey_survey_action&survey_action=delete&survey_id=' . $survey['id']), 'survey_action')); ?>"
                                   class="action-button action-delete"
                                   onclick="return confirm('<?php echo esc_html__('Are you sure you want to delete this survey? This action cannot be undone.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>')">
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                    </svg>
                                    <?php echo esc_html__('Delete', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
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

    --color-text-primary: #1F2937;
    --color-text-secondary: #6B7280;
    --color-text-muted: #9CA3AF;

    --color-bg-primary: #FFFFFF;
    --color-bg-secondary: #F9FAFB;
    --color-bg-tertiary: #F3F4F6;

    --color-border: #E5E7EB;
    --color-border-light: #F3F4F6;

    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);

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

    --font-weight-normal: 400;
    --font-weight-medium: 500;
    --font-weight-semibold: 600;
    --font-weight-bold: 700;
}

/* Layout Container */
.surveys-wrapper {
    margin-top: var(--spacing-lg);
    min-height: 100vh;
}

/* Page Header */
.surveys-header {
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

.header-content .page-title {
    font-size: var(--font-size-3xl);
    font-weight: var(--font-weight-bold);
    color: var(--color-text-primary);
    margin: 0 0 var(--spacing-xs) 0;
    line-height: 1.2;
}

.header-content .page-subtitle {
    font-size: var(--font-size-base);
    color: var(--color-text-secondary);
    margin: 0;
    line-height: 1.4;
}

.header-actions {
    display: flex;
    gap: var(--spacing-sm);
}

/* Add Survey Button */
.add-survey-button {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    background: var(--color-primary);
    color: white;
    text-decoration: none;
    border: 1px solid var(--color-primary);
    border-radius: var(--radius-lg);
    padding: var(--spacing-sm) var(--spacing-md);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    transition: all 200ms ease;
}

.add-survey-button:hover {
    background: var(--color-primary-hover);
    border-color: var(--color-primary-hover);
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.add-survey-button:focus-visible {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}

.add-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

/* Empty State */
.empty-state {
    background: var(--color-bg-primary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: var(--spacing-2xl);
    text-align: center;
    box-shadow: var(--shadow-sm);
}

.empty-state-content {
    max-width: 400px;
    margin: 0 auto;
}

.empty-state-icon {
    color: var(--color-text-muted);
    margin-bottom: var(--spacing-lg);
    opacity: 0.6;
}

.empty-state-title {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-primary);
    margin: 0 0 var(--spacing-md) 0;
}

.empty-state-description {
    font-size: var(--font-size-base);
    color: var(--color-text-secondary);
    line-height: 1.6;
    margin: 0 0 var(--spacing-lg) 0;
}

.empty-state-button {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-xs);
    background: var(--color-primary);
    color: white;
    text-decoration: none;
    border: 1px solid var(--color-primary);
    border-radius: var(--radius-lg);
    padding: var(--spacing-md) var(--spacing-lg);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-medium);
    transition: all 200ms ease;
}

.empty-state-button:hover {
    background: var(--color-primary-hover);
    border-color: var(--color-primary-hover);
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.button-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

/* Surveys Content */
.surveys-content {
    background: var(--color-bg-primary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.surveys-table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    background: var(--color-bg-secondary);
    border-bottom: 1px solid var(--color-border);
}

.table-title {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-primary);
    margin: 0;
}

.table-stats {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.survey-count {
    background: var(--color-bg-tertiary);
    color: var(--color-text-secondary);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-lg);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
}

/* Surveys Grid */
.surveys-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: var(--spacing-lg);
    padding: var(--spacing-lg);
}

/* Survey Cards */
.survey-card {
    background: var(--color-bg-primary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: all 200ms ease;
    box-shadow: var(--shadow-sm);
}

.survey-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    border-color: var(--color-primary);
}

/* Survey Card Header */
.survey-card-header {
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--color-border-light);
}

.survey-info {
    margin-bottom: var(--spacing-sm);
}

.survey-title {
    margin: 0 0 var(--spacing-sm) 0;
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    line-height: 1.4;
}

.survey-title a {
    color: var(--color-text-primary);
    text-decoration: none;
    transition: color 200ms ease;
}

.survey-title a:hover {
    color: var(--color-primary);
    text-decoration: none;
}

.survey-meta {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    flex-wrap: wrap;
}

/* Enhanced Status Badges */
.survey-status {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-xs);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-lg);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-semibold);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.survey-status.status-published {
    background: #DCFCE7;
    color: var(--color-success);
}

.survey-status.status-draft {
    background: #FEF3C7;
    color: var(--color-warning);
}

.survey-status.status-archived {
    background: #FEE2E2;
    color: var(--color-error);
}

.status-icon {
    width: 12px;
    height: 12px;
    flex-shrink: 0;
}

.survey-date {
    font-size: var(--font-size-xs);
    color: var(--color-text-muted);
    font-weight: var(--font-weight-medium);
}

/* Survey Card Stats */
.survey-card-stats {
    display: flex;
    padding: var(--spacing-md) var(--spacing-lg);
    background: var(--color-bg-secondary);
    border-bottom: 1px solid var(--color-border-light);
}

.stat-item {
    flex: 1;
    text-align: center;
    padding: 0 var(--spacing-sm);
}

.stat-item:not(:last-child) {
    border-right: 1px solid var(--color-border);
}

.stat-number {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-bold);
    color: var(--color-text-primary);
    line-height: 1.2;
    font-variant-numeric: tabular-nums;
}

.stat-label {
    font-size: var(--font-size-xs);
    color: var(--color-text-secondary);
    font-weight: var(--font-weight-medium);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: var(--spacing-xs);
}

/* Survey Card Actions */
.survey-card-actions {
    display: flex;
    gap: var(--spacing-xs);
    padding: var(--spacing-md) var(--spacing-lg);
    background: var(--color-bg-primary);
    flex-wrap: wrap;
}

.action-button {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-xs);
    padding: var(--spacing-xs) var(--spacing-sm);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    background: var(--color-bg-primary);
    color: var(--color-text-secondary);
    text-decoration: none;
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    transition: all 200ms ease;
    white-space: nowrap;
}

.action-button:hover {
    background: var(--color-bg-tertiary);
    border-color: var(--color-primary);
    color: var(--color-primary);
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.action-button:focus-visible {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}

/* Action Button Variants */
.action-edit:hover {
    background: #EFF6FF;
    border-color: var(--color-primary);
    color: var(--color-primary);
}

.action-questions:hover {
    background: #F0F9FF;
    border-color: var(--color-primary);
    color: var(--color-primary);
}

.action-analytics:hover {
    background: #ECFDF5;
    border-color: var(--color-success);
    color: var(--color-success);
}

.action-delete:hover {
    background: #FEF2F2;
    border-color: var(--color-error);
    color: var(--color-error);
}

.action-icon {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .surveys-wrapper {
        padding: 0 var(--spacing-lg);
    }

    .surveys-grid {
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: var(--spacing-md);
    }
}

@media (max-width: 768px) {
    .surveys-wrapper {
        padding: 0 var(--spacing-md);
    }

    .surveys-header {
        flex-direction: column;
        gap: var(--spacing-md);
        align-items: stretch;
        text-align: center;
    }

    .header-actions {
        justify-content: center;
    }

    .surveys-table-header {
        flex-direction: column;
        gap: var(--spacing-sm);
        align-items: stretch;
        text-align: center;
    }

    .surveys-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-md);
        padding: var(--spacing-md);
    }

    .survey-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-xs);
    }

    .survey-card-stats {
        flex-direction: column;
        gap: var(--spacing-sm);
    }

    .stat-item {
        padding: var(--spacing-sm) 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: left;
    }

    .stat-item:not(:last-child) {
        border-right: none;
        border-bottom: 1px solid var(--color-border);
    }

    .survey-card-actions {
        flex-direction: column;
        gap: var(--spacing-xs);
    }

    .action-button {
        justify-content: center;
        padding: var(--spacing-sm) var(--spacing-md);
        font-size: var(--font-size-sm);
    }
}

@media (max-width: 480px) {
    .surveys-wrapper {
        padding: 0 var(--spacing-sm);
    }

    .surveys-header,
    .surveys-table-header,
    .survey-card-header,
    .survey-card-actions {
        padding: var(--spacing-md);
    }

    .surveys-grid {
        padding: var(--spacing-sm);
    }

    .empty-state {
        padding: var(--spacing-lg);
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

.survey-card {
    animation: slideInUp 400ms ease-out;
}

.survey-card:nth-child(1) { animation-delay: 0ms; }
.survey-card:nth-child(2) { animation-delay: 100ms; }
.survey-card:nth-child(3) { animation-delay: 200ms; }
.survey-card:nth-child(4) { animation-delay: 300ms; }
.survey-card:nth-child(5) { animation-delay: 400ms; }
.survey-card:nth-child(6) { animation-delay: 500ms; }

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
</style>