<?php
/**
 * Participant Information Form Template
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wp-dynamic-survey-participant-form">
    <div class="participant-form-container">
        <h3 class="form-title">
            <?php echo esc_html__($survey['title'], WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
        </h3>

        <!-- Stage 1 Form -->
        <form id="wp-dynamic-survey-participant-form-stage1" class="participant-form stage-1" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="participant_name" class="form-label">
                        <?php echo esc_html__('Full Name', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="participant_name"
                        name="participant_name"
                        class="form-control"
                        style="text-align: left !important;"
                        required
                        placeholder="<?php echo esc_attr__('Enter your full name', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>"
                    >
                    <div class="error-message" id="error-participant_name"></div>
                </div>
            </div>


            <div class="form-row">
                <div class="form-group">
                    <label for="participant_email" class="form-label">
                        <?php echo esc_html__('Email Address', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        <span class="required">*</span>
                    </label>
                    <input
                        type="email"
                        id="participant_email"
                        name="participant_email"
                        class="form-control"
                        required
                        placeholder="<?php echo esc_attr__('Enter your email address', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>"
                    >
                    <div class="error-message" id="error-participant_email"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="participant_address" class="form-label">
                        <?php echo esc_html__('Address', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        <span class="optional"><?php echo esc_html__('(Optional)', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
                    </label>
                    <textarea
                        id="participant_address"
                        name="participant_address"
                        class="form-control"
                        rows="3"
                        placeholder="<?php echo esc_attr__('Enter your address (optional)', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>"
                    ></textarea>
                    <div class="error-message" id="error-participant_address"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="participant_zip_code" class="form-label">
                        <?php echo esc_html__('Zip Code', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        <span class="optional"><?php echo esc_html__('(Optional)', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
                    </label>
                    <input
                        type="text"
                        id="participant_zip_code"
                        name="participant_zip_code"
                        class="form-control"
                        placeholder="<?php echo esc_attr__('Enter your zip code (optional)', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>"
                        maxlength="10"
                    >
                    <div class="error-message" id="error-participant_zip_code"></div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-continue">
                    <span class="btn-text"><?php echo esc_html__('Continue', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
                    <span class="btn-loading" style="display: none;">
                        <span class="spinner"></span>
                        <?php echo esc_html__('Continuing...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </span>
                </button>
            </div>

            <input type="hidden" name="survey_id" value="<?php echo esc_attr($survey['id']); ?>">
            <input type="hidden" name="action" value="wp_dynamic_survey_submit_stage1_info">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wp_dynamic_survey_frontend_nonce'); ?>">
        </form>

        <!-- Stage 2 Form (Hidden Initially) -->
        <form id="wp-dynamic-survey-participant-form-stage2" class="participant-form stage-2 hidden" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="participant_phone" class="form-label">
                        <?php echo esc_html__('Phone Number', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        <span class="required">*</span>
                    </label>
                    <input
                        type="tel"
                        id="participant_phone"
                        name="participant_phone"
                        class="form-control"
                        required
                        placeholder="<?php echo esc_attr__('Enter your phone number', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>"
                    >
                    <div class="error-message" id="error-participant_phone"></div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary btn-back">
                    <?php echo esc_html__('Back', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                </button>
                <button type="submit" class="btn btn-primary btn-start-survey">
                    <span class="btn-text"><?php echo esc_html__('Start Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></span>
                    <span class="btn-loading" style="display: none;">
                        <span class="spinner"></span>
                        <?php echo esc_html__('Starting...', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </span>
                </button>
            </div>

            <input type="hidden" name="survey_id" value="<?php echo esc_attr($survey['id']); ?>">
            <input type="hidden" name="action" value="wp_dynamic_survey_submit_stage2_info">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wp_dynamic_survey_frontend_nonce'); ?>">
            <input type="hidden" name="stage1_data" id="stage1_data" value="">
        </form>

        <div class="form-notes">
            <p class="privacy-note">
                <small>
                    <?php echo esc_html__('Your information will be used only for this survey and will be handled according to our privacy policy.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                </small>
            </p>

            <p class="required-note">
                <small>
                    <span class="required">*</span>
                    <?php echo esc_html__('Required fields', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                </small>
            </p>
        </div>
    </div>

    <div class="form-error-container" id="form-error-container" style="display: none;">
        <div class="error-alert">
            <span class="error-icon">⚠</span>
            <span class="error-text"></span>
        </div>
    </div>
</div>

<style>
.wp-dynamic-survey-participant-form {
    max-width: 600px;
    margin: 0 auto;
    padding: 30px 20px;
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
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-title {
    color: #1d2327;
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 25px;
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

.form-actions {
    margin-top: 30px;
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
    min-width: 160px;
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
    padding: 12px 16px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.error-icon {
    font-size: 18px;
    flex-shrink: 0;
}

/* Responsive design */
@media (max-width: 768px) {
    .wp-dynamic-survey-participant-form {
        padding: 20px 15px;
    }

    .participant-form-container {
        padding: 20px;
    }

    .survey-title {
        font-size: 24px;
    }

    .form-title {
        font-size: 18px;
    }

    .btn {
        width: 100%;
        padding: 16px 20px;
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
</style>