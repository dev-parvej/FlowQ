<?php
/**
 * Participant Information Form Template
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get template handler
$template_handler = new FlowQ_Template_Handler();
$template_styles = $template_handler->get_template_styles();

// Get form settings
$two_stage_form = get_option('flowq_two_stage_form', '1');
$field_address = get_option('flowq_field_address', '1');
$field_zipcode = get_option('flowq_field_zipcode', '1');
$field_phone = get_option('flowq_field_phone', '1');
$phone_optional = get_option('flowq_phone_optional', '0');

// Default privacy policy texts
$default_privacy_single = '<p>We respect your privacy and are committed to protecting your personal information. By submitting this form, you agree that your data will be used solely for the purpose of this survey and will be handled in accordance with our <a href="/privacy-policy" target="_blank">Privacy Policy</a>.</p>';
$default_privacy_stage1 = '<p>We respect your privacy. Your contact information will be used only for this survey and handled securely according to our <a href="/privacy-policy" target="_blank">Privacy Policy</a>.</p>';
$default_privacy_stage2 = '<p>Your phone number will be kept confidential and used only for survey-related communication. See our <a href="/privacy-policy" target="_blank">Privacy Policy</a> for details.</p>';

$privacy_policy = get_option('flowq_privacy_policy', $default_privacy_single);
$privacy_policy_stage1 = get_option('flowq_privacy_policy_stage1', $default_privacy_stage1);
$privacy_policy_stage2 = get_option('flowq_privacy_policy_stage2', $default_privacy_stage2);

// If phone is disabled, force single-stage form
if ($field_phone == '0') {
    $two_stage_form = '0';
}

// Determine which privacy policy to show for each stage
$show_stage1_privacy = $two_stage_form == '1' ? !empty($privacy_policy_stage1) : !empty($privacy_policy);
$show_stage2_privacy = $two_stage_form == '1' ? !empty($privacy_policy_stage2) : false;
$stage1_privacy_text = $two_stage_form == '1' ? $privacy_policy_stage1 : $privacy_policy;
$stage2_privacy_text = $privacy_policy_stage2;
?>

<div class="flowq-participant-form">
    <div class="participant-form-container">
        <?php
        // Display custom header and subtitle if enabled
        $show_header = isset($survey['show_header']) ? (int) $survey['show_header'] : 0;
        $form_header = isset($survey['form_header']) ? $survey['form_header'] : '';
        $form_subtitle = isset($survey['form_subtitle']) ? $survey['form_subtitle'] : '';

        if ($show_header && !empty($form_header)):
        ?>
            <div class="survey-header-section">
                <h1 class="survey-form-header"><?php echo esc_html($form_header); ?></h1>
                <?php if (!empty($form_subtitle)): ?>
                    <div class="survey-form-subtitle"><?php echo wp_kses_post($form_subtitle); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Stage 1 Form -->
        <form id="flowq-participant-form-stage1" class="participant-form stage-1" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="participant_name" class="form-label">
                        <?php echo esc_html__('Full Name', 'flowq'); ?> <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="participant_name"
                        name="participant_name"
                        class="form-control"
                        style="text-align: left !important;"
                        required
                        placeholder="<?php echo esc_attr__('John Smith', 'flowq'); ?>"
                    >
                    <div class="error-message" id="error-participant_name"></div>
                </div>
            </div>


            <div class="form-row">
                <div class="form-group">
                    <label for="participant_email" class="form-label">
                        <?php echo esc_html__('Email Address', 'flowq'); ?> <span class="required">*</span>
                    </label>
                    <input
                        type="email"
                        id="participant_email"
                        name="participant_email"
                        class="form-control"
                        required
                        placeholder="<?php echo esc_attr__('john@example.com', 'flowq'); ?>"
                    >
                    <div class="error-message" id="error-participant_email"></div>
                </div>
            </div>

            <?php if ($field_address == '1'): ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="participant_address" class="form-label">
                        <?php echo esc_html__('Address', 'flowq'); ?> <span class="optional"><?php echo esc_html__('(Optional)', 'flowq'); ?></span>
                    </label>
                    <input
                        type="text"
                        id="participant_address"
                        name="participant_address"
                        class="form-control"
                        placeholder="<?php echo esc_attr__('123 Main St, City, State', 'flowq'); ?>"
                    >
                    <div class="error-message" id="error-participant_address"></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($field_zipcode == '1'): ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="participant_zip_code" class="form-label">
                        <?php echo esc_html__('Zip Code', 'flowq'); ?> <span class="optional"><?php echo esc_html__('(Optional)', 'flowq'); ?></span>
                    </label>
                    <input
                        type="text"
                        id="participant_zip_code"
                        name="participant_zip_code"
                        class="form-control"
                        placeholder="<?php echo esc_attr__('12345', 'flowq'); ?>"
                        maxlength="10"
                    >
                    <div class="error-message" id="error-participant_zip_code"></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($field_phone == '1' && $two_stage_form == '0'): ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="participant_phone_single" class="form-label">
                        <?php echo esc_html__('Phone Number', 'flowq'); ?>
                        <span class="required">*</span>
                    </label>
                    <input
                        type="tel"
                        id="participant_phone_single"
                        name="participant_phone"
                        class="form-control"
                        required
                        maxlength="13"
                        placeholder="<?php echo esc_attr__('Enter your phone number', 'flowq'); ?>"
                    >
                    <div class="error-message" id="error-participant_phone_single"></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Privacy Policy for Stage 1 -->
            <?php if ($show_stage1_privacy): ?>
            <div class="privacy-policy-section">
                <div class="privacy-policy-text">
                    <?php echo wp_kses_post($stage1_privacy_text); ?>
                </div>
                <div class="privacy-policy-checkbox">
                    <label>
                        <input type="checkbox" id="privacy_agreement_stage1" name="privacy_agreement" value="1" required>
                        <span class="privacy-checkbox-text">
                            <?php echo esc_html__('I agree to the privacy policy', 'flowq'); ?> <span class="required">*</span>
                        </span>
                    </label>
                    <div class="error-message" id="error-privacy_agreement"></div>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-error-container" id="form-error-container" style="display: none;">
                <div class="error-alert">
                    <span class="error-text"></span>
                    <button type="button" class="error-close" aria-label="<?php echo esc_attr__('Close error message', 'flowq'); ?>">&times;</button>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary <?php echo $two_stage_form == '1' ? 'btn-continue' : 'btn-start-survey'; ?>">
                    <span class="btn-text">
                        <?php echo $two_stage_form == '1' ? esc_html__('Continue', 'flowq') : esc_html__('Start Survey', 'flowq'); ?>
                    </span>
                    <span class="btn-loading" style="display: none;">
                        <span class="spinner"></span>
                        <?php echo $two_stage_form == '1' ? esc_html__('Continuing...', 'flowq') : esc_html__('Starting...', 'flowq'); ?>
                    </span>
                </button>
            </div>

            <input type="hidden" name="survey_id" value="<?php echo esc_attr($survey['id']); ?>">
            <input type="hidden" name="action" value="flowq_submit_stage1_info">
            <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('flowq_frontend_nonce')); ?>">
        </form>

        <!-- Stage 2 Form (Hidden Initially) -->
        <?php if ($field_phone == '1' && $two_stage_form == '1'): ?>
        <form id="flowq-participant-form-stage2" class="participant-form stage-2 hidden" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="participant_phone" class="form-label">
                        <?php echo esc_html__('Phone Number', 'flowq'); ?>
                        <?php if ($phone_optional != '1'): ?>
                        <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <input
                        type="tel"
                        id="participant_phone"
                        name="participant_phone"
                        class="form-control"
                        <?php echo $phone_optional != '1' ? 'required' : ''; ?>
                        maxlength="13"
                        placeholder="<?php echo esc_attr__('Enter your phone number', 'flowq'); ?>"
                    >
                    <div class="error-message" id="error-participant_phone"></div>
                </div>
            </div>

            <!-- Privacy Policy for Stage 2 -->
            <?php if ($show_stage2_privacy): ?>
            <div class="privacy-policy-section">
                <div class="privacy-policy-text">
                    <?php echo wp_kses_post($stage2_privacy_text); ?>
                </div>
                <div class="privacy-policy-checkbox">
                    <label>
                        <input type="checkbox" id="privacy_agreement_stage2" name="privacy_agreement_stage2" value="1" required>
                        <span class="privacy-checkbox-text">
                            <?php echo esc_html__('I agree to the privacy policy', 'flowq'); ?> <span class="required">*</span>
                        </span>
                    </label>
                    <div class="error-message" id="error-privacy_agreement_stage2"></div>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-error-container" id="form-error-container-stage2" style="display: none;">
                <div class="error-alert">
                    <span class="error-text"></span>
                    <button type="button" class="error-close" aria-label="<?php echo esc_attr__('Close error message', 'flowq'); ?>">&times;</button>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary btn-back">
                    <?php echo esc_html__('Back', 'flowq'); ?>
                </button>
                <button type="submit" class="btn btn-primary btn-start-survey">
                    <span class="btn-text"><?php echo esc_html__('Start Survey', 'flowq'); ?></span>
                    <span class="btn-loading" style="display: none;">
                        <span class="spinner"></span>
                        <?php echo esc_html__('Starting...', 'flowq'); ?>
                    </span>
                </button>

                <?php if ($phone_optional == '1'): ?>
                <div class="or-divider">
                    <span><?php echo esc_html__('or', 'flowq'); ?></span>
                </div>
                <button type="button" class="btn-skip-link" id="btn-skip-phone">
                    <?php echo esc_html__('Continue without phone number', 'flowq'); ?>
                </button>
                <?php endif; ?>
            </div>

            <input type="hidden" name="survey_id" value="<?php echo esc_attr($survey['id']); ?>">
            <input type="hidden" name="action" value="flowq_submit_stage2_info">
            <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('flowq_frontend_nonce')); ?>">
            <input type="hidden" name="stage1_data" id="stage1_data" value="">
        </form>
        <?php endif; ?>
    </div>
</div>

<?php
// Add inline script for error alert handling
$error_alert_script = "
document.addEventListener('DOMContentLoaded', function() {
    const errorCloseButtons = document.querySelectorAll('.error-close');
    errorCloseButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.form-actions').forEach(el => {
                el.style.setProperty('margin-top', '40px', 'important');
            });
            const errorContainer = this.closest('.form-error-container');
            if (errorContainer) {
                errorContainer.style.display = 'none';
                const errorText = errorContainer.querySelector('.error-text');
                if (errorText) {
                    errorText.textContent = '';
                }
            }
        });
    });
});
";
wp_add_inline_script('flowq-frontend', $error_alert_script);
?>