/**
 * Add/Edit Survey Page JavaScript
 * @package FlowQ
 */

(function($) {
    'use strict';

    /**
     * Copy shortcode to clipboard
     */
    window.copyShortcode = function() {
        const input = document.getElementById('shortcode-input');
        const button = document.querySelector('.copy-button');

        if (!input || !button) return;

        // Select and copy the text
        input.select();
        input.setSelectionRange(0, 99999); // For mobile devices

        navigator.clipboard.writeText(input.value).then(function() {
            // Show success feedback
            button.classList.add('copy-success');
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="dashicons dashicons-yes"></span>' + flowqAddSurvey.copiedText;

            // Reset after 2 seconds
            setTimeout(function() {
                button.classList.remove('copy-success');
                button.innerHTML = originalText;
            }, 2000);
        }).catch(function() {
            // Fallback for older browsers
            input.select();
            document.execCommand('copy');
            alert(flowqAddSurvey.copiedText);
        });
    };

    /**
     * Legacy function for compatibility
     */
    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert(flowqAddSurvey.copiedText);
        });
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        initThankYouPageSelector();
        initHeaderFieldsToggle();
    });

    /**
     * Thank You Page Selection Handler
     */
    function initThankYouPageSelector() {
        var $thankYouPageSelect = $('#thank_you_page_slug');
        var $editPageButton = $('#edit-page-button');

        if ($thankYouPageSelect.length === 0) return;

        // Get pages data from localized script
        var pages = flowqAddSurvey.pages || [];

        // Handle thank you page selection change
        $thankYouPageSelect.on('change', function() {
            var selectedSlug = $(this).val();

            if (selectedSlug) {
                // Find the selected page
                var selectedPage = pages.find(function(page) {
                    return page.post_name === selectedSlug;
                });

                if (selectedPage) {
                    var editUrl = flowqAddSurvey.editPostUrl + selectedPage.ID + '&action=edit';
                    $editPageButton.attr('href', editUrl).show();
                }
            } else {
                $editPageButton.hide();
            }
        });

        // Initialize button visibility on page load
        if ($thankYouPageSelect.val()) {
            $editPageButton.show();
        } else {
            $editPageButton.hide();
        }
    }

    /**
     * Header Fields Toggle and Validation
     */
    function initHeaderFieldsToggle() {
        var $showHeaderCheckbox = $('#show_header');
        var $headerFieldsContainer = $('#header-fields-container');
        var $formHeaderInput = $('#form_header');
        var $charCount = $('#header-char-count');
        var $surveyForm = $('form[action*="admin-post.php"]');

        if ($showHeaderCheckbox.length === 0) return;

        // Update character count
        function updateCharCount() {
            var length = $formHeaderInput.val().length;
            $charCount.text(length);

            // Change color if approaching limit
            if (length > 240) {
                $charCount.css('color', '#dc3232');
            } else if (length > 200) {
                $charCount.css('color', '#dba617');
            } else {
                $charCount.css('color', '#1d2327');
            }
        }

        // Toggle header fields visibility
        function toggleHeaderFields() {
            if ($showHeaderCheckbox.is(':checked')) {
                $headerFieldsContainer.slideDown(300);
                $formHeaderInput.attr('required', true);
            } else {
                $headerFieldsContainer.slideUp(300);
                $formHeaderInput.attr('required', false);
            }
        }

        // Initialize on page load
        toggleHeaderFields();
        updateCharCount();

        // Listen for checkbox changes
        $showHeaderCheckbox.on('change', toggleHeaderFields);

        // Listen for input changes on header field
        $formHeaderInput.on('input', updateCharCount);

        // Form validation
        $surveyForm.on('submit', function(e) {
            if ($showHeaderCheckbox.is(':checked')) {
                var headerValue = $formHeaderInput.val().trim();

                if (headerValue === '') {
                    e.preventDefault();
                    alert(flowqAddSurvey.headerRequiredText);
                    $formHeaderInput.focus();

                    // Scroll to the field
                    $('html, body').animate({
                        scrollTop: $formHeaderInput.offset().top - 100
                    }, 500);

                    return false;
                }
            }
        });
    }

})(jQuery);
