<?php
/**
 * Templates List Template
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<style>
.templates-list-wrapper {
    max-width: 1400px;
    margin-top: 15px;
}

.templates-header {
    margin-bottom: 32px;
}

.templates-header h2 {
    font-size: 20px;
    font-weight: 600;
    margin: 0 0 8px 0;
    color: #1d2327;
}

.templates-header .description {
    margin: 0;
    color: #646970;
    font-size: 14px;
}

.templates-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #f6f7f7;
    border-radius: 8px;
}

.templates-empty-state p {
    color: #646970;
    font-size: 16px;
    margin: 0;
}

.templates-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    margin-top: 24px;
}

.template-card {
    background: #ffffff;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
    flex: 1 1 calc(33.333% - 16px);
    min-width: 320px;
    max-width: 400px;
}

.template-card:hover {
    border-color: #2271b1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.template-card.active {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1, 0 4px 12px rgba(34, 113, 177, 0.15);
}

.template-preview {
    position: relative;
    background: #f6f7f7;
    height: 240px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.template-preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.template-preview-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #c3c4c7;
}

.template-preview-placeholder .dashicons {
    font-size: 64px;
    width: 64px;
    height: 64px;
}

.template-active-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: #2271b1;
    color: #ffffff;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.template-active-badge .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.template-info {
    padding: 20px;
}

.template-header {
    margin-bottom: 12px;
}

.template-name {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
    color: #1d2327;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.default-badge {
    display: inline-flex;
    align-items: center;
    background: #f0f0f1;
    color: #646970;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.template-description {
    margin: 0 0 16px 0;
    color: #646970;
    font-size: 13px;
    line-height: 1.6;
}

.template-actions {
    display: flex;
    gap: 8px;
    margin-top: 16px;
}

.template-actions .button {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.template-actions .button-primary {
    background: #2271b1;
    border-color: #2271b1;
    color: #ffffff;
}

.template-actions .button-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.template-actions .button-secondary {
    background: #ffffff;
    border-color: #2271b1;
    color: #2271b1;
}

.template-actions .button-secondary:hover {
    background: #2271b1;
    color: #ffffff;
}

.template-actions .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

/* Responsive */
@media (max-width: 768px) {
    .templates-grid {
        flex-direction: column;
    }

    .template-card {
        max-width: 100%;
    }
}
</style>

<div class="templates-list-wrapper">
    <div class="templates-header">
        <div class="header-content">
            <h2><?php echo esc_html__('Survey Templates', FLOWQ_TEXT_DOMAIN); ?></h2>
            <p class="description">
                <?php echo esc_html__('Choose a template to apply to all your surveys. The selected template will define the visual appearance and styling.', FLOWQ_TEXT_DOMAIN); ?>
            </p>
        </div>
    </div>

    <?php if (empty($templates)): ?>
        <div class="templates-empty-state">
            <p><?php echo esc_html__('No templates found.', FLOWQ_TEXT_DOMAIN); ?></p>
        </div>
    <?php else: ?>
        <div class="templates-grid">
            <?php foreach ($templates as $template): ?>
                <div class="template-card <?php echo $template['id'] == $active_template_id ? 'active' : ''; ?>"
                     data-template-id="<?php echo esc_attr($template['id']); ?>">

                    <!-- Preview Image -->
                    <div class="template-preview">
                        <?php if (!empty($template['preview_image'])): ?>
                            <img src="<?php echo esc_url($template['preview_image']); ?>"
                                 alt="<?php echo esc_attr($template['name']); ?>"
                                 class="template-preview-image">
                        <?php else: ?>
                            <div class="template-preview-placeholder">
                                <span class="dashicons dashicons-format-image"></span>
                            </div>
                        <?php endif; ?>

                        <!-- Active Badge -->
                        <?php if ($template['id'] == $active_template_id): ?>
                            <div class="template-active-badge">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php echo esc_html__('Active', FLOWQ_TEXT_DOMAIN); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Template Info -->
                    <div class="template-info">
                        <div class="template-header">
                            <h3 class="template-name">
                                <?php echo esc_html($template['name']); ?>
                                <?php if ($template['is_default']): ?>
                                    <span class="default-badge"><?php echo esc_html__('Default', FLOWQ_TEXT_DOMAIN); ?></span>
                                <?php endif; ?>
                            </h3>
                        </div>

                        <?php if (!empty($template['description'])): ?>
                            <p class="template-description">
                                <?php echo esc_html($template['description']); ?>
                            </p>
                        <?php endif; ?>

                        <!-- Template Actions -->
                        <div class="template-actions">
                            <?php if ($template['id'] == $active_template_id): ?>
                                <button type="button" class="button button-primary" disabled>
                                    <span class="dashicons dashicons-yes"></span>
                                    <?php echo esc_html__('Selected', FLOWQ_TEXT_DOMAIN); ?>
                                </button>
                            <?php else: ?>
                                <button type="button" class="button button-secondary select-template-btn"
                                        data-template-id="<?php echo esc_attr($template['id']); ?>"
                                        data-template-name="<?php echo esc_attr($template['name']); ?>">
                                    <?php echo esc_html__('Select', FLOWQ_TEXT_DOMAIN); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    $('.select-template-btn').on('click', function() {
        const button = $(this);

        const buttonExist = $('button[disabled]');

        const templateId = button.data('template-id');
        const templateName = button.data('template-name');
        const card = button.closest('.template-card');

        // Confirm before selecting
        if (!confirm('<?php echo esc_js(__('Are you sure you want to activate this template?', FLOWQ_TEXT_DOMAIN)); ?>')) {
            return;
        }

        // Disable button and show loading state
        button.prop('disabled', true).text('<?php echo esc_js(__('Activating...', FLOWQ_TEXT_DOMAIN)); ?>');

        // AJAX request to select template
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'flowq_select_template',
                template_id: templateId,
                nonce: '<?php echo wp_create_nonce('flowq_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Remove active class from all cards
                    $('.template-card').removeClass('active');
                    $('.template-active-badge').remove();

                    // Update all buttons
                    $('.select-template-btn').each(function() {
                        $(this).removeClass('button-primary').addClass('button-secondary')
                            .prop('disabled', false)
                            .html('<?php echo esc_js(__('Select', FLOWQ_TEXT_DOMAIN)); ?>');
                    });

                    // Add active class to selected card
                    card.addClass('active');

                    // Add active badge to preview
                    card.find('.template-preview').append(
                        '<div class="template-active-badge">' +
                        '<span class="dashicons dashicons-yes-alt"></span>' +
                        '<?php echo esc_js(__('Active', FLOWQ_TEXT_DOMAIN)); ?>' +
                        '</div>'
                    );

                    buttonExist.prop('disabled', false).addClass('button-primary select-template-btn').text('<?php echo esc_js(__('Select', FLOWQ_TEXT_DOMAIN)); ?>');

                    // Update button to selected state
                    button.removeClass('button-secondary').addClass('button-primary')
                        .prop('disabled', true)
                        .html('<span class="dashicons dashicons-yes"></span><?php echo esc_js(__('Selected', FLOWQ_TEXT_DOMAIN)); ?>');

                    // Show success notice
                    const notice = $('<div class="notice notice-success is-dismissible"><p>' +
                        response.data.message +
                        '</p></div>');
                    $('.templates-list-wrapper').before(notice);

                    // Auto-dismiss notice after 3 seconds
                    setTimeout(function() {
                        notice.fadeOut(function() {
                            $(this).remove();
                            window.location.reload();
                        });
                    }, 500);
                } else {
                    alert('<?php echo esc_js(__('Error:', FLOWQ_TEXT_DOMAIN)); ?> ' + response.data);
                    button.prop('disabled', false).text('<?php echo esc_js(__('Select', FLOWQ_TEXT_DOMAIN)); ?>');
                }
            },
            error: function(xhr, status, error) {
                alert('<?php echo esc_js(__('An error occurred. Please try again.', FLOWQ_TEXT_DOMAIN)); ?>');
                button.prop('disabled', false).text('<?php echo esc_js(__('Select', FLOWQ_TEXT_DOMAIN)); ?>');
            }
        });
    });
});
</script>
