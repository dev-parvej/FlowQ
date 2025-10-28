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

<div class="wp-dynamic-survey-participant-form">
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
        <form id="wp-dynamic-survey-participant-form-stage1" class="participant-form stage-1" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="participant_name" class="form-label">
                        <?php echo esc_html__('Full Name', FLOWQ_TEXT_DOMAIN); ?> <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="participant_name"
                        name="participant_name"
                        class="form-control"
                        style="text-align: left !important;"
                        required
                        placeholder="<?php echo esc_attr__('John Smith', FLOWQ_TEXT_DOMAIN); ?>"
                    >
                    <div class="error-message" id="error-participant_name"></div>
                </div>
            </div>


            <div class="form-row">
                <div class="form-group">
                    <label for="participant_email" class="form-label">
                        <?php echo esc_html__('Email Address', FLOWQ_TEXT_DOMAIN); ?> <span class="required">*</span>
                    </label>
                    <input
                        type="email"
                        id="participant_email"
                        name="participant_email"
                        class="form-control"
                        required
                        placeholder="<?php echo esc_attr__('john@example.com', FLOWQ_TEXT_DOMAIN); ?>"
                    >
                    <div class="error-message" id="error-participant_email"></div>
                </div>
            </div>

            <?php if ($field_address == '1'): ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="participant_address" class="form-label">
                        <?php echo esc_html__('Address', FLOWQ_TEXT_DOMAIN); ?> <span class="optional"><?php echo esc_html__('(Optional)', FLOWQ_TEXT_DOMAIN); ?></span>
                    </label>
                    <input
                        type="text"
                        id="participant_address"
                        name="participant_address"
                        class="form-control"
                        placeholder="<?php echo esc_attr__('123 Main St, City, State', FLOWQ_TEXT_DOMAIN); ?>"
                    >
                    <div class="error-message" id="error-participant_address"></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($field_zipcode == '1'): ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="participant_zip_code" class="form-label">
                        <?php echo esc_html__('Zip Code', FLOWQ_TEXT_DOMAIN); ?> <span class="optional"><?php echo esc_html__('(Optional)', FLOWQ_TEXT_DOMAIN); ?></span>
                    </label>
                    <input
                        type="text"
                        id="participant_zip_code"
                        name="participant_zip_code"
                        class="form-control"
                        placeholder="<?php echo esc_attr__('12345', FLOWQ_TEXT_DOMAIN); ?>"
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
                        <?php echo esc_html__('Phone Number', FLOWQ_TEXT_DOMAIN); ?>
                        <span class="required">*</span>
                    </label>
                    <input
                        type="tel"
                        id="participant_phone_single"
                        name="participant_phone"
                        class="form-control"
                        required
                        maxlength="13"
                        placeholder="<?php echo esc_attr__('Enter your phone number', FLOWQ_TEXT_DOMAIN); ?>"
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
                            <?php echo esc_html__('I agree to the privacy policy', FLOWQ_TEXT_DOMAIN); ?> <span class="required">*</span>
                        </span>
                    </label>
                    <div class="error-message" id="error-privacy_agreement"></div>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-error-container" id="form-error-container" style="display: none;">
                <div class="error-alert">
                    <span class="error-text"></span>
                    <button type="button" class="error-close" aria-label="<?php echo esc_attr__('Close error message', FLOWQ_TEXT_DOMAIN); ?>">&times;</button>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary <?php echo $two_stage_form == '1' ? 'btn-continue' : 'btn-start-survey'; ?>">
                    <span class="btn-text">
                        <?php echo $two_stage_form == '1' ? esc_html__('Continue', FLOWQ_TEXT_DOMAIN) : esc_html__('Start Survey', FLOWQ_TEXT_DOMAIN); ?>
                    </span>
                    <span class="btn-loading" style="display: none;">
                        <span class="spinner"></span>
                        <?php echo $two_stage_form == '1' ? esc_html__('Continuing...', FLOWQ_TEXT_DOMAIN) : esc_html__('Starting...', FLOWQ_TEXT_DOMAIN); ?>
                    </span>
                </button>
            </div>

            <input type="hidden" name="survey_id" value="<?php echo esc_attr($survey['id']); ?>">
            <input type="hidden" name="action" value="flowq_submit_stage1_info">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('flowq_frontend_nonce'); ?>">
        </form>

        <!-- Stage 2 Form (Hidden Initially) -->
        <?php if ($field_phone == '1' && $two_stage_form == '1'): ?>
        <form id="wp-dynamic-survey-participant-form-stage2" class="participant-form stage-2 hidden" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="participant_phone" class="form-label">
                        <?php echo esc_html__('Phone Number', FLOWQ_TEXT_DOMAIN); ?>
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
                        placeholder="<?php echo esc_attr__('Enter your phone number', FLOWQ_TEXT_DOMAIN); ?>"
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
                            <?php echo esc_html__('I agree to the privacy policy', FLOWQ_TEXT_DOMAIN); ?> <span class="required">*</span>
                        </span>
                    </label>
                    <div class="error-message" id="error-privacy_agreement_stage2"></div>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-error-container" id="form-error-container-stage2" style="display: none;">
                <div class="error-alert">
                    <span class="error-text"></span>
                    <button type="button" class="error-close" aria-label="<?php echo esc_attr__('Close error message', FLOWQ_TEXT_DOMAIN); ?>">&times;</button>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary btn-back">
                    <?php echo esc_html__('Back', FLOWQ_TEXT_DOMAIN); ?>
                </button>
                <button type="submit" class="btn btn-primary btn-start-survey">
                    <span class="btn-text"><?php echo esc_html__('Start Survey', FLOWQ_TEXT_DOMAIN); ?></span>
                    <span class="btn-loading" style="display: none;">
                        <span class="spinner"></span>
                        <?php echo esc_html__('Starting...', FLOWQ_TEXT_DOMAIN); ?>
                    </span>
                </button>

                <?php if ($phone_optional == '1'): ?>
                <div class="or-divider">
                    <span><?php echo esc_html__('or', FLOWQ_TEXT_DOMAIN); ?></span>
                </div>
                <button type="button" class="btn-skip-link" id="btn-skip-phone">
                    <?php echo esc_html__('Continue without phone number', FLOWQ_TEXT_DOMAIN); ?>
                </button>
                <?php endif; ?>
            </div>

            <input type="hidden" name="survey_id" value="<?php echo esc_attr($survey['id']); ?>">
            <input type="hidden" name="action" value="flowq_submit_stage2_info">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('flowq_frontend_nonce'); ?>">
            <input type="hidden" name="stage1_data" id="stage1_data" value="">
        </form>
        <?php endif; ?>
    </div>
</div>

<style>
.wp-dynamic-survey-participant-form {
    max-width: 600px;
    margin: 0 auto;
    padding: 15px 0px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.survey-intro {
    text-align: center;
    margin-bottom: 40px;
}

.survey-title {
    color: #1d2327;
    font-size: 28px;
    font-weight: 600;
    line-height: 1.3;
}

.survey-description {
    color: #666;
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 0;
}

.participant-form-container {
    background: #fff;
    border: none;
    border-radius: 8px;
    box-shadow: none;
    padding: 20px;
}

/* Survey Header Section */
.survey-header-section {
    margin-bottom: 35px;
    text-align: center;
}

.survey-form-header {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 12px 0;
    line-height: 1.3;
    color: inherit;
}

.survey-form-subtitle {
    font-size: 1.125rem;
    opacity: 0.85;
    margin: 0;
    line-height: 1.6;
    color: inherit;
}

.form-title {
    color: #1d2327;
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 35px;
    text-align: center;
}

.form-row {
    margin-bottom: 20px;
}

.form-group {
    width: 100%;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #1d2327;
    margin-bottom: 8px;
    font-size: 14px;
}

.required {
    color: #d63638;
    font-weight: bold;
}

.optional {
    color: #666;
    font-weight: normal;
    font-style: italic;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #ddd;
    border-radius: 6px;
    font-size: 16px;
    line-height: 1.5;
    transition: border-color 0.2s ease;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #2271b1;
    box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.1);
}

.form-control.error {
    border-color: #d63638;
}

.form-control.error:focus {
    border-color: #d63638;
    box-shadow: 0 0 0 3px rgba(214, 54, 56, 0.1);
}

.error-message {
    color: #d63638;
    font-size: 13px;
    margin-top: 5px;
    min-height: 18px;
}

/* Back Button at Top - Positioning Only */
.form-back-button {
    margin-bottom: 30px;
}

.btn-back-link {
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.back-arrow {
    display: inline-block;
}

/* Stage Heading - Spacing Only */
.stage-heading {
    margin-top: 40px;
    margin-bottom: 30px;
    text-align: center;
}

/* Form Actions - Alignment Only */
.form-actions {
    margin-top: 40px;
    text-align: center;
}

.form-actions-vertical {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

.btn-full-width {
    width: 100%;
    max-width: 400px;
}

.or-divider {
    text-align: center;
    margin: 10px 0;
}

.btn-skip-link {
    background: none;
    border: none;
    padding: 10px;
    cursor: pointer;
    text-align: center;
}

.btn {
    display: inline-block;
    padding: 14px 30px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 200px;
    position: relative;
}

.btn-primary {
    background: #2271b1;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #135e96;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(34, 113, 177, 0.3);
}

.btn-secondary {
    background: #f0f0f1;
    color: #1d2327;
    border: 1px solid #ddd;
    margin-right: 10px;
}

.btn-secondary:hover:not(:disabled) {
    background: #e0e0e1;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-actions button {
    margin: 0 5px;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: none !important;
}

.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
    margin-right: 8px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.form-notes {
    margin-top: 25px;
    text-align: center;
}

.privacy-note {
    margin-bottom: 10px;
}

.required-note {
    margin-bottom: 0;
}

.form-error-container {
    margin-top: 20px;
}

.error-alert {
    background: #fdf2f2;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 12px 40px 12px 16px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
    position: relative;
}

.error-close {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    background: none;
    border: none;
    font-size: 24px;
    line-height: 1;
    color: #721c24;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.6;
    transition: opacity 0.2s ease;
}

.error-close:hover {
    opacity: 1;
}

.error-close:focus {
    outline: 2px solid #721c24;
    outline-offset: 2px;
    border-radius: 2px;
}

.error-icons {
    font-size: 18px;
    flex-shrink: 0;
}

/* Responsive design */
@media (max-width: 768px) {
    .wp-dynamic-survey-participant-form {
        padding: 15px 0px;
    }

    .survey-title {
        font-size: 24px;
    }

    .form-title {
        font-size: 18px;
    }

    .survey-form-header {
        font-size: 1.5rem;
    }

    .survey-form-subtitle {
        font-size: 1rem;
    }

    .btn {
        width: 100%;
        padding: 16px 20px;
    }
    .btn-start-survey {
        margin-top: 10px !important;
    }
}

@media (max-width: 480px) {
    .survey-title {
        font-size: 20px;
    }

    .form-control {
        font-size: 16px; /* Prevents zoom on iOS */
    }
}
.hidden {
    display: none !important;
}

/* Privacy Policy Section */
.privacy-policy-section {
    margin: 35px 0 0 0;
    padding-top: 30px;
    border-top: 1px solid #e5e7eb;
    text-align: left;
}

.privacy-policy-text {
    font-size: 13px;
    line-height: 1.6;
    margin-bottom: 20px;
    text-align: left;
    color: #6b7280;
}

.privacy-policy-text p {
    margin: 0 0 10px 0;
    text-align: left;
}

.privacy-policy-text p:last-child {
    margin-bottom: 0;
}

.privacy-policy-text a {
    color: #2271b1;
    text-decoration: none;
}

.privacy-policy-text a:hover {
    text-decoration: underline;
}

.privacy-policy-checkbox {
    text-align: left;
    margin-bottom: 20px;
}

.privacy-policy-checkbox label {
    display: flex;
    align-items: flex-start;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    gap: 10px;
    text-align: left;
    color: #1d2327;
}

.privacy-policy-checkbox input[type="checkbox"] {
    margin-top: 2px;
    cursor: pointer;
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.privacy-checkbox-text {
    line-height: 1.5;
}

.privacy-policy-checkbox .required {
    margin-left: 2px;
    color: #d63638;
}
</style>

<!-- Template-specific styles -->
<?php echo $template_handler->render_template_css(); ?>

<script>
// Handle error alert close button
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
                // Clear error text
                const errorText = errorContainer.querySelector('.error-text');
                if (errorText) {
                    errorText.textContent = '';
                }
            }
        });
    });
});
</script>