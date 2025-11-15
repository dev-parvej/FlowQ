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

// Localize script data for admin-templates.js
wp_localize_script('flowq-templates', 'flowqTemplates', array(
    'confirmMessage' => __('Are you sure you want to activate this template?', 'flowq'),
    'activatingText' => __('Activating...', 'flowq'),
    'selectText' => __('Select', 'flowq'),
    'activeText' => __('Active', 'flowq'),
    'selectedText' => __('Selected', 'flowq'),
    'errorText' => __('Error:', 'flowq'),
    'errorGenericText' => __('An error occurred. Please try again.', 'flowq'),
    'nonce' => wp_create_nonce('flowq_admin_nonce')
));
?>

<div class="templates-list-wrapper">
    <div class="templates-header">
        <div class="header-content">
            <h2><?php echo esc_html__('Survey Templates', 'flowq'); ?></h2>
            <p class="description">
                <?php echo esc_html__('Choose a template to apply to all your surveys. The selected template will define the visual appearance and styling.', 'flowq'); ?>
            </p>
        </div>
    </div>

    <?php if (empty($templates)): ?>
        <div class="templates-empty-state">
            <p><?php echo esc_html__('No templates found.', 'flowq'); ?></p>
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
                                <?php echo esc_html__('Active', 'flowq'); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Template Info -->
                    <div class="template-info">
                        <div class="template-header">
                            <h3 class="template-name">
                                <?php echo esc_html($template['name']); ?>
                                <?php if ($template['is_default']): ?>
                                    <span class="default-badge"><?php echo esc_html__('Default', 'flowq'); ?></span>
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
                                    <?php echo esc_html__('Selected', 'flowq'); ?>
                                </button>
                            <?php else: ?>
                                <button type="button" class="button button-secondary select-template-btn"
                                        data-template-id="<?php echo esc_attr($template['id']); ?>"
                                        data-template-name="<?php echo esc_attr($template['name']); ?>">
                                    <?php echo esc_html__('Select', 'flowq'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

