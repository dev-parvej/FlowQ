<?php
/**
 * Settings Page Template
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wp-dynamic-survey-settings">
    <div class="settings-wrapper">
        <div class="settings-header">
            <div class="header-content">
                <h1 class="page-title">
                    <?php echo esc_html__('Settings', FLOWQ_TEXT_DOMAIN); ?>
                </h1>
                <p class="page-subtitle">
                    <?php echo esc_html__('Configure your survey plugin settings', FLOWQ_TEXT_DOMAIN); ?>
                </p>
            </div>
        </div>

        <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html__('Settings saved successfully.', FLOWQ_TEXT_DOMAIN); ?></p>
            </div>
        <?php endif; ?>

        <!-- Tab Navigation -->
        <nav class="nav-tab-wrapper wp-clearfix">
            <?php foreach ($tabs as $tab_key => $tab_label): ?>
                <a href="<?php echo esc_url(add_query_arg(array('page' => $this->menu_slug, 'tab' => $tab_key), admin_url('admin.php'))); ?>"
                   class="nav-tab <?php echo $current_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html($tab_label); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Tab Content -->
        <div class="settings-content">
            <?php
            // Render content based on current tab
            switch ($current_tab) {
                case 'general':
                    $this->render_general_tab();
                    break;

                case 'templates':
                    $this->render_templates_tab();
                    break;

                // Additional tabs will be added here in future updates

                default:
                    $this->render_general_tab();
                    break;
            }
            ?>
        </div>
    </div>
</div>
