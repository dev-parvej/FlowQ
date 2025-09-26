/**
 * Shortcode Builder JavaScript
 * Enhanced shortcode generation and preview functionality
 *
 * @package WP_Dynamic_Survey
 */

(function($) {
    'use strict';

    /**
     * Shortcode Builder Class
     */
    class WPDynamicSurveyShortcodeBuilder {

        constructor() {
            this.currentTab = 'survey';
            this.config = wpDynamicSurveyShortcode || {};
            this.init();
        }

        /**
         * Initialize the builder
         */
        init() {
            this.bindEvents();
            this.updateShortcode();
        }

        /**
         * Bind all event handlers
         */
        bindEvents() {
            // Tab switching
            $(document).on('click', '.tab-button', (e) => {
                this.switchTab($(e.target).data('tab'));
            });

            // Form changes
            $(document).on('change input', '.shortcode-builder-form input, .shortcode-builder-form select', () => {
                this.updateShortcode();
            });

            // Generate button
            $(document).on('click', '#generate-shortcode', () => {
                this.updateShortcode();
            });

            // Preview button
            $(document).on('click', '#preview-shortcode', () => {
                this.previewShortcode();
            });

            // Copy button
            $(document).on('click', '#copy-shortcode', () => {
                this.copyToClipboard();
            });

            // Media button modal
            $(document).on('click', '#wp-dynamic-survey-shortcode-button', () => {
                this.openModal();
            });

            // Modal events
            $(document).on('click', '.modal-close, .modal-cancel', () => {
                this.closeModal();
            });

            $(document).on('click', '#insert-shortcode-btn', () => {
                this.insertShortcode();
            });

            // Close modal on background click
            $(document).on('click', '.wp-dynamic-survey-modal', (e) => {
                if (e.target === e.currentTarget) {
                    this.closeModal();
                }
            });

            // Escape key to close modal
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeModal();
                }
            });
        }

        /**
         * Switch active tab
         */
        switchTab(tabName) {
            this.currentTab = tabName;

            // Update tab buttons
            $('.tab-button').removeClass('active');
            $(`.tab-button[data-tab="${tabName}"]`).addClass('active');

            // Update tab content
            $('.tab-content').removeClass('active');
            $(`#tab-${tabName}`).addClass('active');

            // Update shortcode
            this.updateShortcode();
        }

        /**
         * Update shortcode based on current form values
         */
        updateShortcode() {
            let shortcode = '';

            switch (this.currentTab) {
                case 'survey':
                    shortcode = this.generateSurveyShortcode();
                    break;
                case 'list':
                    shortcode = this.generateListShortcode();
                    break;
                case 'stats':
                    shortcode = this.generateStatsShortcode();
                    break;
                case 'button':
                    shortcode = this.generateButtonShortcode();
                    break;
                case 'embed':
                    shortcode = this.generateEmbedShortcode();
                    break;
            }

            $('#shortcode-output').val(shortcode);
            $('#copy-shortcode').prop('disabled', !shortcode);
        }

        /**
         * Generate survey shortcode
         */
        generateSurveyShortcode() {
            const surveyId = $('#survey-id').val();
            if (!surveyId) return '';

            const attributes = [];
            attributes.push(`id="${surveyId}"`);

            const theme = $('#survey-theme').val();
            if (theme && theme !== 'default') {
                attributes.push(`theme="${theme}"`);
            }

            const width = $('#survey-width').val().trim();
            if (width && width !== '100%') {
                attributes.push(`width="${width}"`);
            }

            const height = $('#survey-height').val().trim();
            if (height && height !== 'auto') {
                attributes.push(`height="${height}"`);
            }

            if (!$('#show-title').is(':checked')) {
                attributes.push('show_title="false"');
            }

            if (!$('#show-description').is(':checked')) {
                attributes.push('show_description="false"');
            }

            if (!$('#show-progress').is(':checked')) {
                attributes.push('show_progress="false"');
            }

            if ($('#auto-start').is(':checked')) {
                attributes.push('auto_start="true"');
            }

            if ($('#enable-print').is(':checked')) {
                attributes.push('enable_print="true"');
            }

            const cssClass = $('#survey-css-class').val().trim();
            if (cssClass) {
                attributes.push(`css_class="${cssClass}"`);
            }

            return `[wp_dynamic_survey ${attributes.join(' ')}]`;
        }

        /**
         * Generate list shortcode
         */
        generateListShortcode() {
            const attributes = [];

            const status = $('#list-status').val();
            if (status && status !== 'published') {
                attributes.push(`status="${status}"`);
            }

            const limit = $('#list-limit').val();
            if (limit && limit !== '10') {
                attributes.push(`limit="${limit}"`);
            }

            if (!$('#list-show-description').is(':checked')) {
                attributes.push('show_description="false"');
            }

            if ($('#list-show-stats').is(':checked')) {
                attributes.push('show_stats="true"');
            }

            return `[survey_list${attributes.length ? ' ' + attributes.join(' ') : ''}]`;
        }

        /**
         * Generate stats shortcode
         */
        generateStatsShortcode() {
            const surveyId = $('#stats-survey-id').val();
            if (!surveyId) return '';

            const attributes = [];
            attributes.push(`id="${surveyId}"`);

            const show = $('#stats-show').val();
            if (show && show !== 'all') {
                attributes.push(`show="${show}"`);
            }

            const format = $('#stats-format').val();
            if (format && format !== 'inline') {
                attributes.push(`format="${format}"`);
            }

            return `[survey_stats ${attributes.join(' ')}]`;
        }

        /**
         * Generate button shortcode
         */
        generateButtonShortcode() {
            const surveyId = $('#button-survey-id').val();
            if (!surveyId) return '';

            const attributes = [];
            attributes.push(`id="${surveyId}"`);

            const text = $('#button-text').val().trim();
            if (text && text !== 'Take Survey') {
                attributes.push(`text="${text}"`);
            }

            const style = $('#button-style').val();
            if (style && style !== 'button') {
                attributes.push(`style="${style}"`);
            }

            const size = $('#button-size').val();
            if (size && size !== 'medium') {
                attributes.push(`size="${size}"`);
            }

            const color = $('#button-color').val();
            if (color && color !== 'primary') {
                attributes.push(`color="${color}"`);
            }

            if ($('#button-new-window').is(':checked')) {
                attributes.push('new_window="true"');
            }

            return `[survey_button ${attributes.join(' ')}]`;
        }

        /**
         * Generate embed shortcode
         */
        generateEmbedShortcode() {
            const surveyId = $('#embed-survey-id').val();
            if (!surveyId) return '';

            const attributes = [];
            attributes.push(`id="${surveyId}"`);

            const width = $('#embed-width').val().trim();
            if (width && width !== '100%') {
                attributes.push(`width="${width}"`);
            }

            const height = $('#embed-height').val().trim();
            if (height && height !== '600px') {
                attributes.push(`height="${height}"`);
            }

            return `[survey_embed ${attributes.join(' ')}]`;
        }

        /**
         * Preview shortcode
         */
        previewShortcode() {
            const shortcode = $('#shortcode-output').val().trim();
            if (!shortcode) {
                this.showMessage('Please generate a shortcode first.', 'error');
                return;
            }

            const $previewContainer = $('#preview-container');
            $previewContainer.html('<div class="loading-spinner">Loading preview...</div>');

            $.ajax({
                url: this.config.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_dynamic_survey_preview_shortcode',
                    shortcode: shortcode,
                    nonce: this.config.nonce
                },
                success: (response) => {
                    if (response.success) {
                        $previewContainer.html(response.data.html);
                        this.showMessage('Preview loaded successfully!', 'success');
                    } else {
                        $previewContainer.html(`<div class="error">Preview failed: ${response.data || 'Unknown error'}</div>`);
                        this.showMessage('Preview failed to load.', 'error');
                    }
                },
                error: (xhr, status, error) => {
                    $previewContainer.html(`<div class="error">Preview failed: ${error}</div>`);
                    this.showMessage('Preview failed to load.', 'error');
                }
            });
        }

        /**
         * Copy shortcode to clipboard
         */
        async copyToClipboard() {
            const shortcode = $('#shortcode-output').val();
            if (!shortcode) return;

            try {
                await navigator.clipboard.writeText(shortcode);
                this.showMessage('Shortcode copied to clipboard!', 'success');
            } catch (err) {
                // Fallback for older browsers
                const $textarea = $('#shortcode-output');
                $textarea.select();
                document.execCommand('copy');
                this.showMessage('Shortcode copied to clipboard!', 'success');
            }
        }

        /**
         * Open modal
         */
        openModal() {
            $('#wp-dynamic-survey-shortcode-modal').show();
            $('body').addClass('modal-open');
        }

        /**
         * Close modal
         */
        closeModal() {
            $('#wp-dynamic-survey-shortcode-modal').hide();
            $('body').removeClass('modal-open');
        }

        /**
         * Insert shortcode from modal
         */
        insertShortcode() {
            const surveyId = $('#modal-survey-select').val();
            if (!surveyId) {
                this.showMessage('Please select a survey.', 'error');
                return;
            }

            const attributes = [`id="${surveyId}"`];

            const theme = $('#modal-theme-select').val();
            if (theme && theme !== 'default') {
                attributes.push(`theme="${theme}"`);
            }

            if (!$('#modal-show-title').is(':checked')) {
                attributes.push('show_title="false"');
            }

            if (!$('#modal-show-progress').is(':checked')) {
                attributes.push('show_progress="false"');
            }

            const shortcode = `[wp_dynamic_survey ${attributes.join(' ')}]`;

            // Insert into editor
            if (typeof tinymce !== 'undefined' && tinymce.activeEditor && !tinymce.activeEditor.isHidden()) {
                tinymce.activeEditor.insertContent(shortcode);
            } else {
                // Fallback to textarea
                const $textarea = $('#content');
                if ($textarea.length) {
                    const cursorPos = $textarea[0].selectionStart;
                    const content = $textarea.val();
                    const newContent = content.substring(0, cursorPos) + shortcode + content.substring(cursorPos);
                    $textarea.val(newContent);
                }
            }

            this.closeModal();
            this.showMessage('Shortcode inserted successfully!', 'success');
        }

        /**
         * Show notification message
         */
        showMessage(message, type = 'info') {
            const $notice = $(`<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`);
            $('.wrap h1').after($notice);

            setTimeout(() => {
                $notice.fadeOut(() => $notice.remove());
            }, 4000);
        }

        /**
         * Validate form inputs
         */
        validateForm() {
            const errors = [];

            if (this.currentTab === 'survey' || this.currentTab === 'stats' || this.currentTab === 'button' || this.currentTab === 'embed') {
                const surveySelect = $(`#${this.currentTab === 'survey' ? 'survey-id' : this.currentTab + '-survey-id'}`);
                if (!surveySelect.val()) {
                    errors.push('Please select a survey.');
                }
            }

            return errors;
        }

        /**
         * Theme compatibility check
         */
        checkThemeCompatibility() {
            // This would check for theme conflicts and provide warnings
            const activeTheme = $('body').attr('class');
            const warnings = [];

            // Check for common theme conflicts
            if (activeTheme && activeTheme.includes('elementor')) {
                warnings.push('Elementor theme detected. Some animations may conflict.');
            }

            if (activeTheme && activeTheme.includes('divi')) {
                warnings.push('Divi theme detected. Consider using minimal theme for best compatibility.');
            }

            return warnings;
        }

        /**
         * Auto-save functionality
         */
        autoSave() {
            const formData = this.getFormData();
            localStorage.setItem('wp_dynamic_survey_shortcode_builder', JSON.stringify(formData));
        }

        /**
         * Load saved form data
         */
        loadSaved() {
            const saved = localStorage.getItem('wp_dynamic_survey_shortcode_builder');
            if (saved) {
                try {
                    const formData = JSON.parse(saved);
                    this.populateForm(formData);
                } catch (e) {
                    console.warn('Failed to load saved shortcode builder data');
                }
            }
        }

        /**
         * Get current form data
         */
        getFormData() {
            const data = {};
            $('.shortcode-builder-form input, .shortcode-builder-form select').each(function() {
                const $field = $(this);
                const name = $field.attr('name') || $field.attr('id');
                if (name) {
                    if ($field.attr('type') === 'checkbox') {
                        data[name] = $field.is(':checked');
                    } else {
                        data[name] = $field.val();
                    }
                }
            });
            return data;
        }

        /**
         * Populate form with data
         */
        populateForm(data) {
            Object.keys(data).forEach(key => {
                const $field = $(`[name="${key}"], #${key}`);
                if ($field.length) {
                    if ($field.attr('type') === 'checkbox') {
                        $field.prop('checked', data[key]);
                    } else {
                        $field.val(data[key]);
                    }
                }
            });
        }
    }

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        if ($('.shortcode-builder-container').length || $('#wp-dynamic-survey-shortcode-button').length) {
            window.WPDynamicSurveyShortcodeBuilder = new WPDynamicSurveyShortcodeBuilder();
        }
    });

    // Add CSS for modal
    const modalCSS = `
        <style>
        body.modal-open {
            overflow: hidden;
        }

        .loading-spinner {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .loading-spinner:before {
            content: "";
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #2271b1;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
            vertical-align: middle;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .error {
            color: #d63638;
            background: #fdf2f2;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
        }

        .success {
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 4px;
        }

        .shortcode-builder-form .form-table th {
            width: 150px;
            padding-left: 0;
        }

        .shortcode-builder-form .form-table td {
            padding-left: 0;
        }

        .shortcode-result {
            background: #f9f9f9;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 15px;
        }

        #shortcode-output:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
        }

        .shortcode-documentation {
            background: #f9f9f9;
            border-radius: 4px;
            padding: 20px;
        }

        @media (max-width: 782px) {
            .shortcode-type-tabs {
                flex-wrap: wrap;
            }

            .tab-button {
                font-size: 12px;
                padding: 8px 10px;
            }

            .modal-content {
                width: 95%;
                margin: 0 auto;
            }
        }
        </style>
    `;

    $('head').append(modalCSS);

})(jQuery);