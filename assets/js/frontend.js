/**
 * WP Dynamic Survey Frontend JavaScript
 *
 * @package WP_Dynamic_Survey
 */

(function($) {
    'use strict';

    /**
     * Main Frontend Survey Class
     */
    window.WPDynamicSurveyFrontend = {

        // Configuration
        config: {
            ajaxUrl: wpDynamicSurvey.ajaxurl,
            nonce: wpDynamicSurvey.nonce,
            strings: wpDynamicSurvey.strings
        },

        // Current survey data
        currentSurvey: {
            id: null,
            sessionId: null,
            participantId: null,
            totalQuestions: 0,
            currentQuestionIndex: 0,
            currentQuestionId: null,
            startTime: null
        },

        // Stage 1 data storage
        stage1Data: null,

        // DOM elements
        elements: {
            container: null,
            participantForm: null,
            questionStep: null,
            completionStep: null,
            loadingOverlay: null
        },

        /**
         * Initialize the frontend
         */
        init: function() {
            this.bindEvents();
            this.initElements();
            $('.error-message').addClass('hidden');
            console.log('WP Dynamic Survey Frontend initialized');
        },

        /**
         * Initialize DOM elements
         */
        initElements: function() {
            this.elements.container = $('.wp-dynamic-survey-container');
            this.elements.participantForm = $('#wp-dynamic-survey-participant-form');
            this.elements.questionStep = $('#question-step');
            this.elements.completionStep = $('#completion-step');
            this.elements.loadingOverlay = $('#survey-loading-overlay');

            if (this.elements.container.length) {
                this.currentSurvey.id = this.elements.container.data('survey-id');
            }
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Stage 1 form submission
            $(document).off('submit', '#wp-dynamic-survey-participant-form-stage1').on('submit', '#wp-dynamic-survey-participant-form-stage1', this.handleStage1FormSubmit.bind(this));

            // Stage 2 form submission
            $(document).off('submit', '#wp-dynamic-survey-participant-form-stage2').on('submit', '#wp-dynamic-survey-participant-form-stage2', this.handleStage2FormSubmit.bind(this));

            // Back button click
            $(document).off('click', '.btn-back').on('click', '.btn-back', this.handleBackButton.bind(this));

            // Auto-submit on answer selection (single choice radio buttons)
            $(document).off('change', 'input[type="radio"].auto-submit').on('change', 'input[type="radio"].auto-submit', this.handleAutoSubmit.bind(this));

            // Auto-submit on button clicks
            $(document).on('click', 'button.auto-submit', this.handleAutoSubmit.bind(this));

            // Form validation on input
            $(document).on('input change', '.form-control', this.validateField.bind(this));

            // Answer selection visual feedback
            $(document).on('change', 'input[type="radio"]', this.handleAnswerSelection.bind(this));

            // Skip question button click
            $(document).off('click', '.skip-question-btn').on('click', '.skip-question-btn', this.handleSkipQuestion.bind(this));
        },

        /**
         * Handle Stage 1 form submission
         */
        handleStage1FormSubmit: function(e) {
            e.preventDefault();
            e.stopPropagation();

            const form = $(e.target);
            const formData = new FormData(form[0]);

            // Validate form
            if (!this.validateStage1Form(form)) {
                return false;
            }

            // Show loading state
            this.setFormLoading(form, true);
            this.hideFormErrors();

            // Submit via AJAX
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: this.handleStage1FormSuccess.bind(this),
                error: this.handleStage1FormError.bind(this),
                complete: function() {
                    this.setFormLoading(form, false);
                }.bind(this)
            });

            return false;
        },

        /**
         * Handle Stage 2 form submission
         */
        handleStage2FormSubmit: function(e) {
            e.preventDefault();
            e.stopPropagation();

            const form = $(e.target);
            const formData = new FormData(form[0]);

            // Validate form
            if (!this.validateStage2Form(form)) {
                return false;
            }

            // Show loading state
            this.setFormLoading(form, true);
            this.hideFormErrors();

            // Set start time
            this.currentSurvey.startTime = new Date();

            // Submit via AJAX
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: this.handleStage2FormSuccess.bind(this),
                error: this.handleStage2FormError.bind(this),
                complete: function() {
                    this.setFormLoading(form, false);
                }.bind(this)
            });

            return false;
        },

        /**
         * Handle back button click
         */
        handleBackButton: function(e) {
            e.preventDefault();
            this.showStage1Form();
        },

        /**
         * Handle successful Stage 1 form submission
         */
        handleStage1FormSuccess: function(response) {
            if (response.success) {
                // Store stage 1 data for stage 2
                this.stage1Data = response.data;
                this.showStage2Form();
            } else {
                this.showFormError(response.data || this.config.strings.error);
            }
        },

        /**
         * Handle successful Stage 2 form submission
         */
        handleStage2FormSuccess: function(response) {
            if (response.success) {
                // Store session data
                this.currentSurvey.sessionId = response.data.session_id;
                this.currentSurvey.participantId = response.data.participant_id;
                this.currentSurvey.totalQuestions = response.data.total_questions;
                this.currentSurvey.currentQuestionIndex = 1;

                // Hide participant form and show first question
                this.hideStep('participant-form-step');
                this.showQuestion(response.data.first_question);
            } else {
                this.showFormError(response.data || this.config.strings.error);
            }
        },

        /**
         * Handle Stage 1 form submission error
         */
        handleStage1FormError: function(xhr, status, error) {
            console.error('Stage 1 form error:', error);
            this.showFormError(this.config.strings.error);
        },

        /**
         * Handle Stage 2 form submission error
         */
        handleStage2FormError: function(xhr, status, error) {
            console.error('Stage 2 form error:', error);
            this.showFormError(this.config.strings.error);
        },

        /**
         * Validate participant form
         */
        validateParticipantForm: function(form) {
            let isValid = true;
            const requiredFields = form.find('input[required], textarea[required]');
            // Clear previous errors
            form.find('.form-control').removeClass('error');
            form.find('.error-message').text('');

            requiredFields.each(function(fieldIndex) {
                const field = requiredFields[fieldIndex];
                const value = field.value.trim();
                const attribute = $(field).attr('type');

                if (!value) {
                    this.showFieldError(field, this.config.strings.required_field);
                    isValid = false;
                } else if (attribute === 'email' && !this.isValidEmail(value)) {
                    this.showFieldError(field, this.config.strings.invalid_email);
                    isValid = false;
                } else if (attribute === 'tel' && !this.isValidPhone(value)) {
                    this.showFieldError(field, this.config.strings.invalid_phone);
                    isValid = false;
                }
            }.bind(this));

            // Validate optional zip code if provided
            const zipCodeField = form.find('input[name="participant_zip_code"]');
            if (zipCodeField.length && zipCodeField.val().trim()) {
                const zipValue = zipCodeField.val().trim();
                if (!this.isValidZipCode(zipValue)) {
                    this.showFieldError(zipCodeField[0], 'Please enter a valid zip code');
                    isValid = false;
                }
            }

            return isValid;
        },

        /**
         * Show Stage 2 form and hide Stage 1
         */
        showStage2Form: function() {
            $('#wp-dynamic-survey-participant-form-stage1').addClass('hidden');
            $('#wp-dynamic-survey-participant-form-stage2').removeClass('hidden');

            // Store stage 1 data in hidden field for stage 2 submission
            $('#stage1_data').val(JSON.stringify(this.stage1Data));

            // Update form title
            $('.form-title').text('Please provide your phone number to start the survey');
        },

        /**
         * Show Stage 1 form and hide Stage 2
         */
        showStage1Form: function() {
            $('#wp-dynamic-survey-participant-form-stage2').addClass('hidden');
            $('#wp-dynamic-survey-participant-form-stage1').removeClass('hidden');

            // Reset form title
            $('.form-title').text('Please provide your information to start the survey');
        },

        /**
         * Alias for validateParticipantForm to handle stage 1
         */
        validateStage1Form: function(form) {
            return this.validateParticipantForm(form);
        },

        /**
         * Alias for validateParticipantForm to handle stage 2
         */
        validateStage2Form: function(form) {
            return this.validateParticipantForm(form);
        },

        /**
         * Handle auto-submit for radio button answers and buttons
         */
        handleAutoSubmit: function(e) {
            this.showQuestionLoading(true);
            const target = $(e.target);
            let answerId;

            // Handle radio inputs (single choice)
            if (target.is('input[type="radio"]')) {
                answerId = target.val();
                this.showAnswerLoading(target);
            }
            // Handle answer buttons
            else if (target.is('button.answer-button') || target.closest('.answer-button').length) {
                const button = target.closest('.answer-button').length ? target.closest('.answer-button') : target;
                answerId = button.data('value');
                this.showAnswerLoading(button);

                // Add visual selection for answer button
                button.siblings('.answer-button').removeClass('selected');
                button.addClass('selected');
            }

            // Find the question container instead of looking for a form
            const questionContainer = target.closest('.question-container');

            if (!questionContainer.length) {
                console.error('Question container not found');
                return;
            }

            // Disable all other options to prevent multiple selections
            questionContainer.find('.auto-submit').not(target).prop('disabled', true);

            // Small delay for visual feedback, then submit
            setTimeout(() => {
                this.submitAnswerChoice(questionContainer, answerId, null);
            }, 300);
        },


        /**
         * Submit answer choice (unified method)
         */
        submitAnswerChoice: function(container, answerId, answerText) {
            // Show question loading overlay
            this.hideFormErrors();

            // Prepare form data
            const formData = new FormData();
            formData.append('session_id', this.currentSurvey.sessionId);
            formData.append('question_id', this.currentSurvey.currentQuestionId);
            formData.append('action', 'wp_dynamic_survey_submit_answer');
            formData.append('nonce', this.config.nonce);

            if (answerId) {
                formData.append('answer_id', answerId);
            }

            if (answerText) {
                formData.append('answer_text', answerText);
            }

            // Submit via AJAX
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: this.handleAnswerSubmissionSuccess.bind(this),
                error: this.handleAnswerSubmissionError.bind(this),
                complete: () => {
                    this.showQuestionLoading(false);
                    this.hideAnswerLoading();
                }
            });
        },

        /**
         * Handle successful answer submission
         */
        handleAnswerSubmissionSuccess: function(response) {            
            if (response.success) {
                const nextStep = response.data;               
                switch (nextStep.type) {
                    case 'question':
                        this.currentSurvey.currentQuestionIndex++;
                        setTimeout(() => {
                            this.showQuestion(nextStep.question);
                        }); // Brief delay for smooth transition
                        break;

                    case 'redirect':
                        this.handleRedirectAction(nextStep);
                        break;
                    case 'complete':
                        setTimeout(() => {
                            this.showCompletion(nextStep);
                        });
                        break;

                    default:
                        console.error('Unknown next step type:', nextStep.type);
                        this.showFormError('Unexpected response from server');
                        break;
                }
            } else {
                this.showFormError(response.data || this.config.strings.error);
                this.resetFormState();
            }
        },

        /**
         * Handle answer submission error
         */
        handleAnswerSubmissionError: function(xhr, status, error) {
            console.error('Answer submission error:', error);
            this.showFormError(this.config.strings.error);
            this.resetFormState();
        },

        /**
         * Handle skip question button click
         */
        handleSkipQuestion: function(e) {
            e.preventDefault();
            e.stopPropagation();

            const skipBtn = $(e.target).closest('.skip-question-btn');
            const questionContainer = skipBtn.closest('.question-container');

            if (!questionContainer.length) {
                console.error('Question container not found');
                return;
            }

            // Disable the skip button to prevent multiple clicks
            skipBtn.prop('disabled', true).addClass('loading');

            // Add loading state to skip button
            const originalText = skipBtn.find('.skip-text').text();
            skipBtn.find('.skip-text').text(this.config.strings.loading || 'Skipping...');

            // Submit skip request
            this.submitSkipQuestion(questionContainer, originalText, skipBtn);
        },

        /**
         * Submit skip question request
         */
        submitSkipQuestion: function(container, originalText, skipBtn) {
            // Show question loading overlay
            this.hideFormErrors();

            // Prepare form data
            const formData = new FormData();
            formData.append('session_id', this.currentSurvey.sessionId);
            formData.append('question_id', this.currentSurvey.currentQuestionId);
            formData.append('action', 'wp_dynamic_survey_skip_question');
            formData.append('nonce', this.config.nonce);

            // Submit via AJAX
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                timeout: 30000,
                success: (response) => {
                    if (response.success) {
                        const nextStep = response.data;
                        switch (nextStep.type) {
                            case 'question':
                                this.currentSurvey.currentQuestionIndex++;
                                setTimeout(() => {
                                    this.showQuestion(nextStep.question);
                                });
                                break;

                            case 'complete':
                                setTimeout(() => {
                                    this.showCompletion(nextStep);
                                });
                                break;

                            default:
                                console.error('Unknown next step type:', nextStep.type);
                                this.showFormError('Unexpected response from server');
                                break;
                        }
                    } else {
                        this.showFormError(response.data || this.config.strings.error);
                        this.resetSkipButton(skipBtn, originalText);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Skip question error:', error);
                    this.showFormError(this.config.strings.error);
                    this.resetSkipButton(skipBtn, originalText);
                },
                complete: () => {
                    this.showQuestionLoading(false);
                }
            });
        },

        /**
         * Reset skip button state
         */
        resetSkipButton: function(skipBtn, originalText) {
            skipBtn.prop('disabled', false).removeClass('loading');
            skipBtn.find('.skip-text').text(originalText);
        },

        /**
         * Show a question
         */
        showQuestion: function(questionData) {
            // Store current question ID
            this.currentSurvey.currentQuestionId = questionData.id;
            

            // Compile question template
            this.renderQuestionTemplate(questionData);

            // Show question step
            this.showStep('question-step');

            // Initialize question-specific behavior
            this.initQuestionBehavior(questionData);

            // Scroll to top
            this.scrollToTop();
        },

        /**
         * Render question template
         */
        renderQuestionTemplate: function(questionData) {
            // Get template
            const template = $('#wp-dynamic-survey-question-template-wrapper').html();
            $('#wp-dynamic-survey-question-template').removeClass('hidden');

            if (!template) {
                console.error('Question template not found');
                return '<div>Error: Template not found</div>';
            }
            // Prepare template data
            const templateData = {
                questionId: questionData.id,
                questionTitle: questionData.title,
                questionDescription: questionData.description,
                extraMessage: questionData.extra_message,
                isRequired: questionData.is_required == 1,
                isSingleChoice: true, // Always single choice
                answers: questionData.answers || [],
                sessionId: this.currentSurvey.sessionId,
                currentQuestion: this.currentSurvey.currentQuestionIndex,
                totalQuestions: this.currentSurvey.totalQuestions,
                progressPercentage: Math.round((this.currentSurvey.currentQuestionIndex / this.currentSurvey.totalQuestions) * 100)
            };

            // Simple template replacement
            let html = template;

            // Replace simple variables
            html = html.replace(/\{\{questionId\}\}/g, templateData.questionId);
            html = html.replace(/\{\{questionTitle\}\}/g, this.escapeHtml(templateData.questionTitle));
            html = html.replace(/\{\{questionDescription\}\}/g, this.escapeHtml(templateData.questionDescription || ''));
            html = html.replace(/\{\{extraMessage\}\}/g, this.escapeHtml(templateData.extraMessage || ''));
            html = html.replace(/\{\{sessionId\}\}/g, templateData.sessionId);
            html = html.replace(/\{\{currentQuestion\}\}/g, templateData.currentQuestion);
            html = html.replace(/\{\{totalQuestions\}\}/g, templateData.totalQuestions);
            html = html.replace(/\{\{progressPercentage\}\}/g, templateData.progressPercentage);
            

            // Handle conditional blocks
            html = this.processConditionals(html, templateData);

            // Handle answer loops
            html = this.processAnswerLoop(html, templateData.answers);

            $('#wp-dynamic-survey-question-template').html(html);

            return html;
        },

        /**
         * Process conditional blocks in template
         */
        processConditionals: function(html, data) {
            // Handle {{#if questionDescription}}
            if (data.questionDescription) {
                html = html.replace(/\{\{#if questionDescription\}\}([\s\S]*?)\{\{\/if\}\}/g, '$1');
            } else {
                html = html.replace(/\{\{#if questionDescription\}\}([\s\S]*?)\{\{\/if\}\}/g, '');
            }

            // Handle {{#if extraMessage}}
            if (data.extraMessage) {
                html = html.replace(/\{\{#if extraMessage\}\}([\s\S]*?)\{\{\/if\}\}/g, '$1');
            } else {
                html = html.replace(/\{\{#if extraMessage\}\}([\s\S]*?)\{\{\/if\}\}/g, '');
            }

            // Handle {{#if isRequired}}
            if (!data.isRequired) {
                html = html.replace(/\{\{#if isRequired\}\}([\s\S]*?)\{\{\/if\}\}/g, '$1');
            } else {
                html = html.replace(/\{\{#if isRequired\}\}([\s\S]*?)\{\{\/if\}\}/g, '');
            }

            // Always show single choice content
            html = html.replace(/\{\{#if isSingleChoice\}\}([\s\S]*?)\{\{\/if\}\}/g, '$1');
            html = html.replace(/\{\{#if isBoolean\}\}([\s\S]*?)\{\{\/if\}\}/g, '');

            return html;
        },

        /**
         * Process answer loop in template
         */
        processAnswerLoop: function(html, answers) {
            // Find {{#each answers}} blocks
            const eachRegex = /\{\{#each answers\}\}([\s\S]*?)\{\{\/each\}\}/g;

            html = html.replace(eachRegex, function(match, template) {
                let result = '';

                answers.forEach(function(answer) {
                    let answerHtml = template;
                    answerHtml = answerHtml.replace(/\{\{id\}\}/g, answer.id);
                    answerHtml = answerHtml.replace(/\{\{text\}\}/g, this.escapeHtml(answer.answer_text));
                    answerHtml = answerHtml.replace(/\{\{answer_text\}\}/g, this.escapeHtml(answer.answer_text));
                    answerHtml = answerHtml.replace(/\{\{answer_value\}\}/g, this.escapeHtml(answer.answer_value || ''));
                    answerHtml = answerHtml.replace(/\{\{redirect_url\}\}/g, this.escapeHtml(answer.redirect_url || ''));
                    result += answerHtml;
                }.bind(this));

                return result;
            }.bind(this));

            return html;
        },

        /**
         * Initialize question-specific behavior
         */
        initQuestionBehavior: function(questionData) {
            // Auto-focus first input (for radio buttons)
            setTimeout(function() {
                const firstInput = this.elements.questionStep.find('input[type="radio"]').first();
                if (firstInput.length) {
                    firstInput.focus();
                }
            }.bind(this), 100);
        },

        /**
         * Handle redirect
         */
        handleRedirectAction: function(redirectData) {
            // Show redirect message
            this.showRedirectMessage(redirectData.url);
            // Open redirect URL in new tab
            if (redirectData.url) {
                window.open(redirectData.url, '_blank');
            }

            // If there's a continue question, show it after delay
            if (redirectData.question) {
               setTimeout(() => {
                this.showQuestion(redirectData.question);
               });
            } else {
                // No continue question, complete survey
                setTimeout(() => {
                    this.showCompletion(redirectData);
                });
            }
        },

        /**
         * Show completion page
         */
        showCompletion: function(completionData) {
            // Check if we need to handle completion redirect
            if (this.handleCompletionRedirection(completionData)) {
                return;
            } else {
                // Standard completion flow
                this.loadCompletionData(completionData)
                    .then(data => {
                        // Render completion page
                        this.renderCompletionPage(data);

                        // Track completion
                        this.trackSurveyCompletion(data);
                    })
                    .catch(error => {
                        console.error('Error loading completion data:', error);
                        this.showBasicCompletion();
                    });
            }
        },

        /**
         * Handle completion redirection to custom thank you page
         */
        handleCompletionRedirection: function(completionData) {
            if (!completionData || !completionData.redirect_url) {
                return false;
            }

            // Show overlay with redirect message
            this.showRedirectOverlay();

            // Redirect after short delay
            setTimeout(() => {
                window.location.href = completionData.redirect_url;
            }, 1500);

            return true;
        },

        /**
         * Show redirect overlay
         */
        showRedirectOverlay: function() {
            const overlayHtml = `
                <div class="wp-dynamic-survey-redirect-overlay" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.8);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 999999;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                ">
                    <div style="
                        background: white;
                        padding: 40px;
                        border-radius: 8px;
                        text-align: center;
                        max-width: 400px;
                        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
                    ">
                        <div style="
                            width: 60px;
                            height: 60px;
                            border: 4px solid #4CAF50;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin: 0 auto 20px;
                        ">
                            <svg viewBox="0 0 24 24" width="32" height="32">
                                <path fill="#4CAF50" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M11,16.5L18,9.5L16.59,8.09L11,13.67L7.91,10.59L6.5,12L11,16.5Z"/>
                            </svg>
                        </div>
                        <h2 style="
                            margin: 0 0 15px;
                            color: #333;
                            font-size: 24px;
                            font-weight: 600;
                        ">Redirecting to completion page</h2>
                        <p style="
                            margin: 0;
                            color: #666;
                            font-size: 16px;
                        ">Please wait...</p>
                        <div style="
                            margin-top: 20px;
                            width: 30px;
                            height: 30px;
                            border: 3px solid #f3f3f3;
                            border-top: 3px solid #4CAF50;
                            border-radius: 50%;
                            animation: spin 1s linear infinite;
                            margin: 20px auto 0;
                        "></div>
                    </div>
                </div>
                <style>
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                </style>
            `;

            // Add overlay to body
            $('body').append(overlayHtml);
        },

        /**
         * Load comprehensive completion data
         */
        loadCompletionData: function(baseData) {
            return new Promise((resolve, reject) => {
                // Get detailed session data via AJAX
                $.ajax({
                    url: this.config.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'wp_dynamic_survey_get_completion_data',
                        session_id: this.currentSurvey.sessionId,
                        nonce: this.config.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            resolve(this.prepareCompletionData(response.data));
                        } else {
                            reject(response.data);
                        }
                    }.bind(this),
                    error: function(xhr, status, error) {
                        reject(error);
                    }
                });
            });
        },

        /**
         * Prepare completion data for template
         */
        prepareCompletionData: function(serverData) {
            const completionTime = this.calculateCompletionTime();
            const completionDate = new Date().toLocaleDateString();
            const avgTimePerQuestion = this.calculateAverageTimePerQuestion();

            return {
                sessionId: this.currentSurvey.sessionId,
                surveyTitle: serverData.survey_title || 'Survey',
                participantName: serverData.participant_name || '',
                questionsAnswered: this.currentSurvey.currentQuestionIndex,
                totalQuestions: this.currentSurvey.totalQuestions,
                completionTime: completionTime,
                completionDate: completionDate,
                completionPercentage: Math.round((this.currentSurvey.currentQuestionIndex / this.currentSurvey.totalQuestions) * 100),
                avgTimePerQuestion: avgTimePerQuestion,
                hasDownloads: serverData.downloads && serverData.downloads.length > 0,
                downloads: serverData.downloads || [],
                showResponseSummary: serverData.show_response_summary || false,
                responses: serverData.responses || [],
                contactEmail: serverData.contact_email || 'support@example.com'
            };
        },

        /**
         * Render completion page
         */
        renderCompletionPage: function(data) {
            // Check if we have a custom template from the server
            if (data.template && data.template.has_custom_template && data.template.html) {
                // Use custom template rendered by the server
                $('#wp-dynamic-survey-question-template').html(data.template.html);
            } else {
                // Fall back to default completion template
                const template = $('#wp-dynamic-survey-completion-template-wrapper').html();
                if (!template) {
                    console.error('Completion template not found');
                    this.showBasicCompletion();
                    return;
                }

                // Render default completion template
                let html = this.renderCompletionTemplate(template, data);
                $('#wp-dynamic-survey-question-template').html(html);
            }

            // Show completion step
            this.showStep('completion-step');

            // Scroll to top
            this.scrollToTop();

            // Add completion-specific event handlers
            this.bindCompletionEvents(data);
        },

        /**
         * Show basic completion fallback
         */
        showBasicCompletion: function() {
            const basicHtml = `
                <div class="wp-dynamic-survey-completion">
                    <div class="completion-content">
                        <div class="completion-icon">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" stroke="#22c55e" stroke-width="2" fill="#f0fdf4"/>
                                <path d="M9 12l2 2 4-4" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="completion-header">
                            <h2 class="completion-title">${this.config.strings.thank_you || 'Thank You!'}</h2>
                            <p class="completion-subtitle">Your survey has been completed successfully.</p>
                        </div>
                        <div class="completion-actions">
                            <button type="button" class="btn btn-primary" onclick="window.location.reload()">
                                Take Survey Again
                            </button>
                        </div>
                    </div>
                </div>
            `;

            this.elements.completionStep.html(basicHtml);
            this.showStep('completion-step');
            this.scrollToTop();
        },

        /**
         * Track survey completion
         */
        trackSurveyCompletion: function(data) {
            // Send completion tracking to server
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_dynamic_survey_track_completion',
                    session_id: data.sessionId,
                    completion_time: data.completionTime,
                    questions_answered: data.questionsAnswered,
                    nonce: this.config.nonce
                },
                success: function(response) {
                    console.log('Completion tracked successfully');
                },
                error: function(xhr, status, error) {
                    console.error('Failed to track completion:', error);
                }
            });

            // Fire completion event for external integrations
            $(document).trigger('wp_dynamic_survey_completed', [data]);
        },

        /**
         * Bind completion-specific events
         */
        bindCompletionEvents: function(data) {
            // Store data for completion methods
            this.completionData = data;

            const completionState = {
                sessionId: data.sessionId,
                surveyTitle: data.surveyTitle,
                completedAt: new Date().toISOString(),
                questionsAnswered: data.questionsAnswered
            };

            // Auto-save completion state
            this.saveCompletionStateToLocalStorage(completionState);
        },

        /**
         * Save completion state to localStorage
         */
        saveCompletionStateToLocalStorage: function(data) {
            try {
                localStorage.setItem('wp_dynamic_survey_last_completion', JSON.stringify(data));
                // Also save completion state to database
                this.saveCompletionStateToDatabase(data);
            } catch (error) {
                console.warn('Could not save completion state to localStorage:', error);
            }
        },

        /**
         * Save completion state to database
         */
        saveCompletionStateToDatabase: function(data) {
            $.ajax({
                url: wpDynamicSurvey.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_dynamic_survey_track_completion',
                    nonce: wpDynamicSurvey.nonce,
                    session_id: data.sessionId
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Completion state saved to database successfully');
                    } else {
                        console.warn('Failed to save completion state to database:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.warn('Error saving completion state to database:', error);
                }
            });
        },

        /**
         * Download responses summary
         */
        downloadResponsesSummary: function() {
            if (!this.completionData) {
                console.error('No completion data available');
                return;
            }

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_dynamic_survey_download_summary',
                    session_id: this.completionData.sessionId,
                    format: 'pdf',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success && response.data.download_url) {
                        // Create temporary download link
                        const link = document.createElement('a');
                        link.href = response.data.download_url;
                        link.download = response.data.filename || 'survey-summary.pdf';
                        link.style.display = 'none';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        alert('Failed to generate summary. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Download error:', error);
                    alert('Failed to download summary. Please try again.');
                }
            });
        },


        /**
         * Render completion template
         */
        renderCompletionTemplate: function(template, data) {
            let html = template;
            // Handle downloads conditional
            if (data.hasDownloads) {
                html = html.replace(/\{\{#if hasDownloads\}\}([\s\S]*?)\{\{\/if\}\}/g, '$1');
            } else {
                html = html.replace(/\{\{#if hasDownloads\}\}([\s\S]*?)\{\{\/if\}\}/g, '');
            }

            return html;
        },

        /**
         * Calculate completion time
         */
        calculateCompletionTime: function() {
            if (!this.currentSurvey.startTime) {
                return '0 minutes';
            }

            const endTime = new Date();
            const diffMs = endTime - this.currentSurvey.startTime;
            const diffMins = Math.round(diffMs / 60000);

            if (diffMins < 1) {
                return 'Less than a minute';
            } else if (diffMins === 1) {
                return '1 minute';
            } else {
                return diffMins + ' minutes';
            }
        },

        /**
         * Calculate average time per question
         */
        calculateAverageTimePerQuestion: function() {
            if (!this.currentSurvey.startTime || this.currentSurvey.currentQuestionIndex === 0) {
                return '0s';
            }

            const endTime = new Date();
            const diffMs = endTime - this.currentSurvey.startTime;
            const avgMs = diffMs / this.currentSurvey.currentQuestionIndex;
            const avgSeconds = Math.round(avgMs / 1000);

            if (avgSeconds < 60) {
                return avgSeconds + 's';
            } else {
                const minutes = Math.floor(avgSeconds / 60);
                const seconds = avgSeconds % 60;
                return minutes + 'm ' + seconds + 's';
            }
        },

        showStep: function(stepId) {
            $('#' + stepId).show().addClass('active');
        },

        hideStep: function(stepId) {
            $('#' + stepId).hide().removeClass('active');
        },

        /**
         * Form state management
         */
        setFormLoading: function(form, loading) {
            const submitBtn = form.find('button[type="submit"]');
            const btnText = submitBtn.find('.btn-text');
            const btnLoading = submitBtn.find('.btn-loading');

            if (loading) {
                submitBtn.prop('disabled', true);
                btnText.hide();
                btnLoading.show();
            } else {
                submitBtn.prop('disabled', false);
                btnText.show();
                btnLoading.hide();
            }
        },

        /**
         * Error handling
         */
        showFormError: function(message) {
            const errorContainer = $('#form-error-container, .form-error-container').first();
            if (errorContainer.length) {
                errorContainer.find('.error-text').text(message);
                errorContainer.show();
                this.scrollToError();
            } else {
                alert(message);
            }
        },

        hideFormErrors: function() {
            $('#form-error-container, .form-error-container').hide();
        },

        showFieldError: function(field, message) {
            $(field).addClass('error');
            const errorId = 'error-' + $(field).attr('name');
            $('#' + errorId).text(message);
        },

        /**
         * Field validation
         */
        validateField: function(e) {
            const field = $(e.target);
            const value = field.val().trim();

            // Remove error state if field has value
            if (value) {
                field.removeClass('error');
                const errorId = 'error-' + field.attr('name');
                $('#' + errorId).text('');
            }
        },


        /**
         * Handle answer selection
         */
        handleAnswerSelection: function(e) {
            const input = $(e.target);

            // Remove selection from other options in the same group
            const name = input.attr('name');
            $('input[name="' + name + '"]').not(input).parent('.answer-label').removeClass('selected');

            // Add selection to current option
            input.parent('.answer-label').addClass('selected');
        },

        /**
         * Utility functions
         */
        scrollToTop: function() {
            const container = this.elements.container;
            if (container.length) {
                $('html, body').animate({
                    scrollTop: container.offset().top - 50
                }, 500);
            }
        },

        scrollToError: function() {
            const errorContainer = $('#form-error-container, .form-error-container').first();
            if (errorContainer.length) {
                $('html, body').animate({
                    scrollTop: errorContainer.offset().top - 50
                }, 500);
            }
        },

        escapeHtml: function(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        isValidEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        isValidPhone: function(phone) {
            // Basic phone validation - at least 10 digits
            const digitsOnly = phone.replace(/\D/g, '');
            return digitsOnly.length >= 10;
        },

        isValidZipCode: function(zipCode) {
            // US zip code validation (5 digits or 5+4 format)
            const zipPattern = /^\d{5}(-\d{4})?$/;
            return zipPattern.test(zipCode);
        },

        /**
         * Loading state management
         */
        showQuestionLoading: function(show) {
            const questionContainer = $('#wp-dynamic-survey-question-template');

            if (show) {
                // Create overlay if it doesn't exist
                if (!questionContainer.find('.question-loading-overlay').length) {
                    const overlay = $(`
                        <div class="question-loading-overlay">
                            <div class="loading-content">
                                <div class="loading-spinner"></div>
                                <p class="loading-text">Submitting your answer...</p>
                            </div>
                        </div>
                    `);
                    questionContainer.css('position', 'relative').append(overlay);
                }
                questionContainer.find('.question-loading-overlay').fadeIn(200);
            } else {
                questionContainer.find('.question-loading-overlay').fadeOut(200);
            }
        },

        showAnswerLoading: function(input) {
            const label = input.closest('.answer-label');
            label.find('.answer-loading').show();
            label.addClass('loading');
        },

        hideAnswerLoading: function() {
            $('.answer-loading').hide();
            $('.answer-label').removeClass('loading');
        },

        showRedirectMessage: function(url) {
            const message = `
                <div class="redirect-message">
                    <div class="redirect-content">
                        <h3>${this.config.strings.redirect_title || 'External Link'}</h3>
                        <p>${this.config.strings.redirect_message || 'Opening external link in new tab...'}</p>
                        <p class="redirect-url">${url}</p>
                        <p class="redirect-continue">${this.config.strings.redirect_continue || 'The survey will continue automatically.'}</p>
                    </div>
                </div>
            `;

            this.elements.questionStep.html(message);
        },

        /**
         * Reset form state after error
         */
        resetFormState: function() {
            // Re-enable all inputs
            $('.auto-submit').prop('disabled', false);

            // Hide loading states
            this.hideAnswerLoading();

            // Reset button states
            $('.btn-submit-text').each(function() {
                const btn = $(this);
                btn.prop('disabled', false);
                btn.find('.btn-text').show();
                btn.find('.btn-loading').hide();
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        // Auto-initialize if survey container exists
        if ($('.wp-dynamic-survey-container').length) {
            WPDynamicSurveyFrontend.init();
        }
    });

})(jQuery);