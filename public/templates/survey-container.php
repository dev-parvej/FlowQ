<?php
/**
 * Survey Container Template
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
?>

<div class="wp-dynamic-survey-container" data-survey-id="<?php echo esc_attr($survey['id']); ?>" data-theme="<?php echo esc_attr($theme); ?>">
    <!-- Participant Information Form (Initially Visible) -->
    <div id="participant-form-step" class="survey-step active">
        <?php echo $this->render_participant_form($survey); ?>
    </div>

    <div class="hidden" id="wp-dynamic-survey-question-template-wrapper">
        <div class="question-container">
            <!-- Question Content -->
            <div class="question-content">
                <h2 class="question-title">{{questionTitle}}</h2>

                {{#if questionDescription}}
                <div class="question-description">
                    <p>{{questionDescription}}</p>
                </div>
                {{/if}}

                <!-- Question Type: Single Choice -->
                {{#if isSingleChoice}}
                <div class="question-answers single-choice">
                    {{#each answers}}
                    <div class="answer-option" data-answer-id="{{id}}" data-redirect-url="{{redirect_url}}">
                        <input type="radio"
                               id="answer_{{id}}"
                               name="question_{{../questionId}}"
                               value="{{id}}"
                               class="answer-input auto-submit">
                        <label for="answer_{{id}}" class="answer-label">
                            <span class="answer-indicator"></span>
                            <span class="answer-text">{{text}}</span>
                        </label>
                    </div>
                    {{/each}}
                </div>
                {{/if}}
                {{#if extraMessage}}
                    <div class="question-extra-message">{{extraMessage}}</div>
                {{/if}}

                <!-- Skip Button for Optional Questions -->
                {{#if isRequired}}
                <div class="question-skip-section">
                    <button type="button" class="skip-question-btn" data-question-id="{{questionId}}">
                        <span class="skip-icon">⏭️</span>
                        <span class="skip-text"><?php echo esc_html__('Skip', FLOWQ_TEXT_DOMAIN); ?></span>
                    </button>
                </div>
                {{/if}}
            </div>
        </div>
    </div>

    <!-- Completion Template Wrapper (Hidden) -->
    <div class="hidden" id="wp-dynamic-survey-completion-template-wrapper">
        <div class="wp-dynamic-survey-completion">
            <div class="completion-content">
                <!-- Success Icon -->
                <div class="completion-icon">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="#22c55e" stroke-width="2" fill="#f0fdf4"/>
                        <path d="M9 12l2 2 4-4" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <!-- Thank You Message -->
                <div class="completion-header">
                    <h2 class="completion-title">
                        <?php echo esc_html__('Thank You!', FLOWQ_TEXT_DOMAIN); ?>
                    </h2>
                    <p class="completion-subtitle">
                        <?php echo esc_html__('Your survey has been completed successfully.', FLOWQ_TEXT_DOMAIN); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Question Interface (Hidden Initially) -->
    <div id="wp-dynamic-survey-question-template" class="question-step hidden">

    </div>

    <!-- Completion Page (Hidden Initially) -->
    <div id="completion-step" class="survey-step" style="display: none;">
        <!-- Completion content will be loaded here via AJAX -->
    </div>

    <!-- Loading Overlay -->
    <div id="survey-loading-overlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <p class="loading-text"><?php echo esc_html__('Loading...', FLOWQ_TEXT_DOMAIN); ?></p>
        </div>
    </div>
</div>

<style>
.hidden {
    display: none !important;   
}
.wp-dynamic-survey-container {
    position: relative;
    width: 100%;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.survey-step {
    width: 100%;
    transition: opacity 0.3s ease;
}

.survey-step.active {
    display: block;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    border-radius: 8px;
}

.loading-content {
    text-align: center;
    padding: 30px;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #2271b1;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

.loading-text {
    color: #666;
    font-size: 16px;
    margin: 0;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Question Loading Overlay */
.question-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    border-radius: 8px;
    backdrop-filter: blur(2px);
}

.question-loading-overlay .loading-content {
    text-align: center;
    padding: 20px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.question-loading-overlay .loading-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #2271b1;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto 12px;
}

.question-loading-overlay .loading-text {
    color: #555;
    font-size: 14px;
    font-weight: 500;
    margin: 0;
}

/* Question Template Styles */
.question-container {
    background: #fff;
    border-radius: 8px;
    box-shadow: none;
    margin-bottom: 20px;
    padding: 20px;
}

/* Progress Bar */
.progress-container {
    margin-bottom: 30px;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e1e5e9;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #2271b1, #135e96);
    transition: width 0.3s ease;
    border-radius: 4px;
}

.progress-text {
    text-align: center;
    font-size: 14px;
    color: #666;
    font-weight: 500;
}

.question-title {
    font-size: 24px;
    color: #1d2327;
    margin-bottom: 15px;
    font-weight: 600;
    line-height: 1.4;
}

.question-description {
    margin-bottom: 25px;
}

.question-description p {
    font-size: 16px;
    color: #666;
    line-height: 1.6;
    margin: 0;
}

/* Answer Options - Single Choice */
.question-answers {
    margin-bottom: 25px;
}

.single-choice .answer-option {
    margin-bottom: 12px;
    transition: transform 0.2s ease;
}

.single-choice .answer-option:hover {
    transform: translateX(4px);
}

.single-choice .answer-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.single-choice .answer-label {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    background: #f8f9fa;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.single-choice .answer-label:hover {
    border-color: #2271b1;
    background: #f0f6fc;
}

.single-choice .answer-input:checked + .answer-label {
    border-color: #2271b1;
    background: #f0f6fc;
}

.single-choice .answer-indicator {
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-radius: 50%;
    margin-right: 15px;
    position: relative;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.single-choice .answer-input:checked + .answer-label .answer-indicator {
    border-color: #2271b1;
    background: #2271b1;
}

.single-choice .answer-input:checked + .answer-label .answer-indicator::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
}

.single-choice .answer-text {
    font-size: 16px;
    color: #1d2327;
    line-height: 1.5;
}


/* Boolean Input */
.boolean-input {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.boolean-button {
    padding: 16px 32px;
    background: #f8f9fa;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 120px;
}

.boolean-button:hover {
    border-color: #2271b1;
    background: #f0f6fc;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(34, 113, 177, 0.2);
}

.boolean-button.selected {
    border-color: #2271b1;
    background: #2271b1;
    color: white;
}


/* Required Notice */
.required-notice {
    text-align: center;
    color: #666;
    font-size: 14px;
    margin-top: 15px;
}

.required-star {
    color: #d63638;
    font-weight: bold;
}

/* Skip Question Section */
.question-skip-section {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #e1e5e9;
}

.skip-question-btn {
    background: #f8f9fa;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #646970;
    text-decoration: none;
}

.skip-question-btn:hover {
    border-color: #646970;
    background: #f0f0f1;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.skip-question-btn:active {
    transform: translateY(0);
    box-shadow: none;
}

.skip-icon {
    font-size: 18px;
}

.skip-text {
    font-weight: 500;
}

.skip-notice {
    font-size: 14px;
    color: #646970;
    margin: 8px 0 0 0;
    font-style: italic;
}

/* Error Display */
.question-error {
    margin-top: 15px;
}

.error-message {
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .wp-dynamic-survey-container {
        max-width: 100%;
    }

    .question-title {
        font-size: 20px;
    }

    .boolean-input {
        flex-direction: column;
        align-items: stretch;
    }

    .boolean-button {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .question-title {
        font-size: 18px;
    }

    .single-choice .answer-label {
        padding: 14px 16px;
    }
}
</style>

<!-- Template-specific styles -->
<?php echo $template_handler->render_template_css(); ?>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize survey container
    if (typeof WPDynamicSurveyFrontend !== 'undefined') {
        WPDynamicSurveyFrontend.init();
    }
});
</script>