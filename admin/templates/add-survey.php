<?php
/**
 * Add/Edit Survey Admin Template
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$is_edit = !empty($survey);
$page_title = $is_edit ? __('Edit Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) : __('Add New Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN);
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="wp_dynamic_survey_save_survey">
        <?php wp_nonce_field('wp_dynamic_survey_save_survey'); ?>

        <?php if ($is_edit): ?>
            <input type="hidden" name="survey_id" value="<?php echo esc_attr($survey['id']); ?>">
        <?php endif; ?>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="survey_title"><?php echo esc_html__('Survey Title', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                    </th>
                    <td>
                        <input type="text"
                               id="survey_title"
                               name="survey_title"
                               class="regular-text"
                               value="<?php echo esc_attr($survey['title'] ?? ''); ?>"
                               required>
                        <p class="description">
                            <?php echo esc_html__('Enter a descriptive title for your survey.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="survey_description"><?php echo esc_html__('Description', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                    </th>
                    <td>
                        <textarea id="survey_description"
                                  name="survey_description"
                                  class="large-text"
                                  rows="4"><?php echo esc_textarea($survey['description'] ?? ''); ?></textarea>
                        <p class="description">
                            <?php echo esc_html__('Optional description of what this survey is about.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="thank_you_page_slug"><?php echo esc_html__('Thank You Page Slug', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                    </th>
                    <td>
                        <input type="text"
                               id="thank_you_page_slug"
                               name="thank_you_page_slug"
                               class="regular-text"
                               value="<?php echo esc_attr($survey['thank_you_page_slug'] ?? ''); ?>"
                               placeholder="my-custom-thank-you-page">
                        <p class="description">
                            <?php echo esc_html__('Optional: Create a published page with your thank you message. After a survey is completed, a unique token is generated. The user can only access the thank you page with that token, and it expires in 1 hour.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="survey_status"><?php echo esc_html__('Status', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                    </th>
                    <td>
                        <select id="survey_status" name="survey_status">
                            <option value="draft" <?php selected($survey['status'] ?? 'draft', 'draft'); ?>>
                                <?php echo esc_html__('Draft', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </option>
                            <option value="published" <?php selected($survey['status'] ?? '', 'published'); ?>>
                                <?php echo esc_html__('Published', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </option>
                            <option value="archived" <?php selected($survey['status'] ?? '', 'archived'); ?>>
                                <?php echo esc_html__('Archived', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php echo esc_html__('Only published surveys are accessible to participants.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </p>
                    </td>
                </tr>

                <?php if ($is_edit): ?>
                <?php endif; ?>


            </tbody>
        </table>

        <?php submit_button($is_edit ? __('Update Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) : __('Create Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>
    </form>

    <?php if ($is_edit): ?>
        <hr>
        <h2><?php echo esc_html__('Manage Questions', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>
        <p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-questions&survey_id=' . $survey['id'])); ?>" class="button button-primary">
                <?php echo esc_html__('Manage Questions', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
            </a>
            <span class="description">
                <?php echo sprintf(__('This survey has %d question(s). Use the questions manager to add, edit, or remove questions.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN), count($questions)); ?>
            </span>
        </p>

        <hr>

        <h2><?php echo esc_html__('Survey Actions', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>
        <p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-analytics&survey_id=' . $survey['id'])); ?>"
               class="button button-secondary">
                <?php echo esc_html__('View Analytics', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
            </a>

            <?php if ($survey['status'] === 'published'): ?>
                 &nbsp;Shortcode: &nbsp;
                <input type="text" class="" style="width: 212px;" value="<?php echo esc_js('[wp_dynamic_survey id="' . $survey['id'] . '"]'); ?>" />
            <?php endif; ?>

            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wp-dynamic-surveys&action=delete&survey_id=' . $survey['id']), 'survey_action')); ?>"
               class="button button-link-delete"
               onclick="return confirm('<?php echo esc_html__('Are you sure you want to delete this survey? This action cannot be undone.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>')">
                <?php echo esc_html__('Delete Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
            </a>
        </p>
    <?php endif; ?>
</div>


<script>
function copyToClipboard(text) {
    console.log(text);
    
    navigator.clipboard.writeText(text).then(function() {
        alert('<?php echo esc_html__('Shortcode copied to clipboard!', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>');
    });
}
</script>