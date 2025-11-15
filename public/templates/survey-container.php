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

<div class="flowq-container" data-survey-id="<?php echo esc_attr($survey['id']); ?>" data-theme="<?php echo esc_attr($theme); ?>">
    <!-- Participant Information Form (Initially Visible) -->
    <div id="participant-form-step" class="survey-step active">
        <?php echo $this->render_participant_form($survey); ?>
    </div>

    <div class="hidden" id="flowq-question-template-wrapper">
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
                        <span class="skip-text"><?php echo esc_html__('Skip', 'flowq'); ?></span>
                    </button>
                </div>
                {{/if}}
            </div>
        </div>
    </div>

    <!-- Completion Template Wrapper (Hidden) -->
    <div class="hidden" id="flowq-completion-template-wrapper">
        <div class="flowq-completion">
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
                        <?php echo esc_html__('Thank You!', 'flowq'); ?>
                    </h2>
                    <p class="completion-subtitle">
                        <?php echo esc_html__('Your survey has been completed successfully.', 'flowq'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Question Interface (Hidden Initially) -->
    <div id="flowq-question-template" class="question-step hidden">

    </div>

    <!-- Completion Page (Hidden Initially) -->
    <div id="completion-step" class="survey-step" style="display: none;">
        <!-- Completion content will be loaded here via AJAX -->
    </div>

    <!-- Loading Overlay -->
    <div id="survey-loading-overlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <p class="loading-text"><?php echo esc_html__('Loading...', 'flowq'); ?></p>
        </div>
    </div>
</div>

<?php
// Add inline script for survey container initialization
$survey_init_script = "
jQuery(document).ready(function($) {
    if (typeof FlowQFrontend !== 'undefined') {
        FlowQFrontend.init();
    }
});
";
wp_add_inline_script('flowq-frontend', $survey_init_script);
?>