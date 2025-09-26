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

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo esc_html__('Surveys', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
    </h1>

    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-add')); ?>" class="page-title-action">
        <?php echo esc_html__('Add New Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
    </a>

    <hr class="wp-header-end">

    <?php if (empty($surveys)): ?>
        <div class="notice notice-info">
            <p>
                <?php echo esc_html__('No surveys found.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-add')); ?>">
                    <?php echo esc_html__('Create your first survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                </a>
            </p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped surveys">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-title column-primary">
                        <?php echo esc_html__('Title', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </th>
                    <th scope="col" class="manage-column column-status">
                        <?php echo esc_html__('Status', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </th>
                    <th scope="col" class="manage-column column-responses">
                        <?php echo esc_html__('Responses', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </th>
                    <th scope="col" class="manage-column column-date">
                        <?php echo esc_html__('Date', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($surveys as $survey):
                    $survey_manager = new WP_Dynamic_Survey_Manager();
                    $stats = $survey_manager->get_survey_statistics($survey['id']);
                ?>
                    <tr>
                        <td class="column-title column-primary" data-colname="Title">
                            <strong>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-add&survey_id=' . $survey['id'])); ?>">
                                    <?php echo esc_html($survey['title']); ?>
                                </a>
                            </strong>


                            <div class="row-actions">
                                <span class="edit">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-add&survey_id=' . $survey['id'])); ?>">
                                        <?php echo esc_html__('Edit', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                    </a> |
                                </span>

                                <span class="questions">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-questions&survey_id=' . $survey['id'])); ?>">
                                        <?php echo esc_html__('Manage Questions', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                    </a> |
                                </span>

                                <span class="view">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-analytics&survey_id=' . $survey['id'])); ?>">
                                        <?php echo esc_html__('Analytics', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                    </a><?php if ($survey['status'] !== 'published'): ?> |<?php endif; ?>
                                </span>

                                <?php if ($survey['status'] !== 'published'): ?>
                                <span class="trash">
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=wp_dynamic_survey_survey_action&survey_action=delete&survey_id=' . $survey['id']), 'survey_action')); ?>"
                                       onclick="return confirm('<?php echo esc_html__('Are you sure you want to delete this survey? This action cannot be undone.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>')">
                                        <?php echo esc_html__('Delete', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                    </a>
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td class="column-status" data-colname="Status">
                            <span class="survey-status status-<?php echo esc_attr($survey['status']); ?>">
                                <?php echo esc_html(ucfirst($survey['status'])); ?>
                            </span>
                        </td>


                        <td class="column-responses" data-colname="Responses">
                            <strong><?php echo esc_html($stats['total_participants']); ?></strong> participants<br>
                            <small><?php echo esc_html($stats['completed_participants']); ?> completed (<?php echo esc_html($stats['completion_rate']); ?>%)</small>
                        </td>

                        <td class="column-date" data-colname="Date">
                            <?php echo esc_html(mysql2date(get_option('date_format'), $survey['created_at'])); ?><br>
                            <small><?php echo esc_html__('Updated:', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?> <?php echo esc_html(mysql2date(get_option('date_format'), $survey['updated_at'])); ?></small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.survey-status {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.survey-status.status-published {
    background: #d4edda;
    color: #155724;
}

.survey-status.status-draft {
    background: #fff3cd;
    color: #856404;
}

.survey-status.status-archived {
    background: #f8d7da;
    color: #721c24;
}

.dashicons-admin-home {
    color: #2271b1;
    margin-left: 5px;
}
</style>