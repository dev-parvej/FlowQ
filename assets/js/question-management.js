/**
 * Question Management JavaScript for WP Dynamic Survey Plugin
 *
 * @package FlowQ
 */

(function($) {
    'use strict';

    const WPDynamicSurveyQuestionManager = {
        modal: null,
        form: null,
        currentQuestionId: null,
        surveyId: null,

        init: function() {
            this.modal = $('#wp-dynamic-survey-question-modal');
            this.form = $('#wp-dynamic-survey-question-form');
            // Get survey ID from the hidden input in the question form or from data attribute
            this.surveyId = $('#wp-dynamic-survey-survey-id').val() || $('#wp-dynamic-survey-question-management-section').data('survey-id');

            this.bindEvents();
            this.initSortable();

            console.log('QuestionManager initialized with survey ID:', this.surveyId);
        },

        bindEvents: function() {
            console.log('Binding events');
            
            // Add question buttons
            $(document).on('click', '#wp-dynamic-survey-add-first-question-btn, #wp-dynamic-survey-add-new-question-btn', this.openAddQuestionModal.bind(this));

            // Edit question button
            $(document).on('click', '.wp-dynamic-survey-edit-question-btn', this.openEditQuestionModal.bind(this));

            // Duplicate question button
            $(document).on('click', '.wp-dynamic-survey-duplicate-question-btn', this.duplicateQuestion.bind(this));

            // Delete question button
            $(document).on('click', '.wp-dynamic-survey-delete-question-btn', this.deleteQuestion.bind(this));

            // Modal controls
            $(document).on('click', '.wp-dynamic-survey-modal-close, #wp-dynamic-survey-cancel-question', this.closeModal.bind(this));
            $(document).on('click', '.wp-dynamic-survey-modal-backdrop', this.closeModal.bind(this));
            $(document).on('click', '#wp-dynamic-survey-save-question', this.saveQuestion.bind(this));

            // Question type change
            $(document).on('change', '#wp-dynamic-survey-question-type', this.handleQuestionTypeChange.bind(this));

            // Answer options management
            $(document).on('click', '#wp-dynamic-survey-add-answer-option', this.addAnswerOption.bind(this));
            $(document).on('click', '.wp-dynamic-survey-remove-answer-option', this.removeAnswerOption.bind(this));

            // Reorder questions
            $(document).on('click', '#wp-dynamic-survey-reorder-questions-btn', this.toggleReorderMode.bind(this));

            // Escape key to close modal
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && this.modal.is(':visible')) {
                    this.closeModal();
                }
            }.bind(this));
        },

        initSortable: function() {
            $('#wp-dynamic-survey-questions-tbody').sortable({
                handle: '.wp-dynamic-survey-drag-handle',
                placeholder: 'wp-dynamic-survey-ui-sortable-placeholder',
                helper: function(e, ui) {
                    ui.children().each(function() {
                        $(this).width($(this).width());
                    });
                    return ui;
                },
                update: this.updateQuestionOrder.bind(this)
            });
        },

        openAddQuestionModal: function(e) {
            e.preventDefault();
            this.currentQuestionId = null;            
            this.resetForm();
            $('#wp-dynamic-survey-modal-title').text(flowqAdmin.strings.add_question || 'Add New Question');
            this.showModal();
            this.handleQuestionTypeChange();
        },

        openEditQuestionModal: function(e) {
            e.preventDefault();
            const questionId = $(e.target).data('question-id');
            this.currentQuestionId = questionId;
            $('#wp-dynamic-survey-modal-title').text(flowqAdmin.strings.edit_question || 'Edit Question');
            this.loadQuestionData(questionId);
            this.showModal();
        },

        showModal: function() {
            this.modal.show();
            $('body').addClass('wp-dynamic-survey-modal-open');
            $('#wp-dynamic-survey-question-title').focus();
        },

        closeModal: function(e) {
            if (e) {
                e.preventDefault();
            }
            this.modal.hide();
            $('body').removeClass('wp-dynamic-survey-modal-open');
            this.resetForm();
        },

        resetForm: function() {
            this.form[0].reset();
            $('#wp-dynamic-survey-question-id').val('');
            $('#wp-dynamic-survey-answer-options-container').empty();
            $('#wp-dynamic-survey-answer-options-section').hide();
            this.currentQuestionId = null;
        },

        handleQuestionTypeChange: function() {
            const questionType = $('#wp-dynamic-survey-question-type').val();
            const answerOptionsSection = $('#wp-dynamic-survey-answer-options-section');

            if (questionType === 'single_choice') {
                answerOptionsSection.show();

                // Add default options for single choice
                if ($('#wp-dynamic-survey-answer-options-container').children().length === 0) {
                    this.addAnswerOption();
                    this.addAnswerOption();
                }
            } else {
                answerOptionsSection.hide();
                $('#wp-dynamic-survey-answer-options-container').empty();
            }
        },

        addAnswerOption: function(e, defaultText = '') {
            if (e) {
                e.preventDefault();
            }

            const template = $('#wp-dynamic-survey-answer-option-template').html();
            const answerRow = $(template);

            if (defaultText) {
                answerRow.find('.wp-dynamic-survey-answer-text').val(defaultText);
            }

            // Populate next question dropdown
            this.populateNextQuestionDropdown(answerRow.find('.wp-dynamic-survey-answer-next-question'));

            $('#wp-dynamic-survey-answer-options-container').append(answerRow);
        },

        removeAnswerOption: function(e) {
            e.preventDefault();
            $(e.target).closest('.wp-dynamic-survey-answer-option-row').remove();
        },

        populateNextQuestionDropdown: function(dropdown) {
            // Clear existing options except the first one
            dropdown.find('option:not(:first)').remove();

            // Add questions from the current survey
            $('#wp-dynamic-survey-questions-tbody .wp-dynamic-survey-question-row').each(function() {
                const questionId = $(this).data('question-id');
                const questionTitle = $(this).find('.wp-dynamic-survey-question-title').text();

                if (questionId != WPDynamicSurveyQuestionManager.currentQuestionId) {
                    dropdown.append(`<option value="${questionId}">${questionTitle}</option>`);
                }
            });
        },

        loadQuestionData: function(questionId) {
            const data = {
                action: 'flowq_get_question',
                question_id: questionId,
                nonce: flowqAdmin.nonce
            };

            $.post(flowqAdmin.ajaxurl, data)
                .done(function(response) {
                    if (response.success) {
                        this.populateForm(response.data);
                    } else {
                        alert(response.data || flowqAdmin.strings.error);
                    }
                }.bind(this))
                .fail(function() {
                    alert(flowqAdmin.strings.error);
                });
        },

        populateForm: function(questionData) {
            $('#wp-dynamic-survey-question-id').val(questionData.id);
            $('#wp-dynamic-survey-question-title').val(questionData.title);
            $('#wp-dynamic-survey-question-description').val(questionData.description);
            $('#wp-dynamic-survey-question-type').val(questionData.type);
            $('#wp-dynamic-survey-question-required').prop('checked', questionData.is_required == 1);
            $('#wp-dynamic-survey-question-order').val(questionData.question_order);
            $('#wp-dynamic-survey-redirect-url').val(questionData.redirect_url);

            // Handle question type change and load answers
            this.handleQuestionTypeChange();

            if (questionData.answers && questionData.answers.length > 0) {
                $('#wp-dynamic-survey-answer-options-container').empty();
                questionData.answers.forEach(function(answer) {
                    this.addAnswerOption();
                    const lastRow = $('#wp-dynamic-survey-answer-options-container .wp-dynamic-survey-answer-option-row:last');
                    lastRow.find('.wp-dynamic-survey-answer-id').val(answer.id);
                    lastRow.find('.wp-dynamic-survey-answer-text').val(answer.answer_text);
                    lastRow.find('.wp-dynamic-survey-answer-next-question').val(answer.next_question_id);
                    lastRow.find('.wp-dynamic-survey-answer-redirect-url').val(answer.redirect_url);
                }.bind(this));
            }
        },

        saveQuestion: function(e) {
            e.preventDefault();

            if (!this.validateForm()) {
                return;
            }

            const formData = this.form.serialize();
            const action = this.currentQuestionId ? 'flowq_update_question' : 'flowq_create_question';

            const data = formData + `&action=${action}&nonce=${flowqAdmin.nonce}`;

            $('#wp-dynamic-survey-save-question').prop('disabled', true).text(flowqAdmin.strings.saving || 'Saving...');

            $.post(flowqAdmin.ajaxurl, data)
                .done(function(response) {
                    if (response.success) {
                        this.closeModal();
                        this.refreshQuestionsList();
                        this.showNotice(flowqAdmin.strings.saved || 'Question saved successfully!', 'success');
                    } else {
                        alert(response.data || flowqAdmin.strings.error);
                    }
                }.bind(this))
                .fail(function() {
                    alert(flowqAdmin.strings.error);
                })
                .always(function() {
                    $('#wp-dynamic-survey-save-question').prop('disabled', false).text(flowqAdmin.strings.save_question || 'Save Question');
                });
        },

        validateForm: function() {
            const title = $('#wp-dynamic-survey-question-title').val().trim();
            if (!title) {
                alert('Question title is required.');
                $('#wp-dynamic-survey-question-title').focus();
                return false;
            }

            const questionType = $('#wp-dynamic-survey-question-type').val();
            if (questionType === 'single_choice') {
                const answers = $('#wp-dynamic-survey-answer-options-container .wp-dynamic-survey-answer-text');
                let hasValidAnswer = false;

                answers.each(function() {
                    if ($(this).val().trim()) {
                        hasValidAnswer = true;
                        return false;
                    }
                });

                if (!hasValidAnswer) {
                    alert('At least one answer option is required for this question type.');
                    return false;
                }
            }

            return true;
        },

        duplicateQuestion: function(e) {
            e.preventDefault();
            const questionId = $(e.target).data('question-id');

            const data = {
                action: 'flowq_duplicate_question',
                question_id: questionId,
                nonce: flowqAdmin.nonce
            };

            $.post(flowqAdmin.ajaxurl, data)
                .done(function(response) {
                    if (response.success) {
                        this.refreshQuestionsList();
                        this.showNotice('Question duplicated successfully!', 'success');
                    } else {
                        alert(response.data || flowqAdmin.strings.error);
                    }
                }.bind(this))
                .fail(function() {
                    alert(flowqAdmin.strings.error);
                });
        },

        deleteQuestion: function(e) {
            e.preventDefault();
            const questionId = $(e.target).data('question-id');

            if (!confirm(flowqAdmin.strings.confirm_delete_question || 'Are you sure you want to delete this question? This action cannot be undone.')) {
                return;
            }

            const data = {
                action: 'flowq_delete_question',
                question_id: questionId,
                nonce: flowqAdmin.nonce
            };

            $.post(flowqAdmin.ajaxurl, data)
                .done(function(response) {
                    if (response.success) {
                        $(e.target).closest('tr').fadeOut(function() {
                            $(this).remove();
                            this.updateQuestionNumbers();
                        }.bind(this));
                        this.showNotice('Question deleted successfully!', 'success');
                    } else {
                        alert(response.data || flowqAdmin.strings.error);
                    }
                }.bind(this))
                .fail(function() {
                    alert(flowqAdmin.strings.error);
                });
        },

        updateQuestionOrder: function(event, ui) {
            const questionIds = [];
            $('#wp-dynamic-survey-questions-tbody .wp-dynamic-survey-question-row').each(function(index) {
                questionIds.push({
                    id: $(this).data('question-id'),
                    order: index + 1
                });
            });

            const data = {
                action: 'flowq_reorder_questions',
                survey_id: this.surveyId,
                question_orders: JSON.stringify(questionIds),
                nonce: flowqAdmin.nonce
            };

            $.post(flowqAdmin.ajaxurl, data)
                .done(function(response) {
                    if (response.success) {
                        this.updateQuestionNumbers();
                        this.showNotice('Questions reordered successfully!', 'success');
                    } else {
                        alert(response.data || flowqAdmin.strings.error);
                    }
                }.bind(this))
                .fail(function() {
                    alert(flowqAdmin.strings.error);
                });
        },

        updateQuestionNumbers: function() {
            $('#wp-dynamic-survey-questions-tbody .wp-dynamic-survey-question-row').each(function(index) {
                $(this).find('.wp-dynamic-survey-question-order').text(index + 1);
            });
        },

        toggleReorderMode: function(e) {
            e.preventDefault();
            const button = $(e.target);
            const tbody = $('#wp-dynamic-survey-questions-tbody');

            if (tbody.hasClass('ui-sortable-disabled')) {
                tbody.sortable('enable');
                button.text('Disable Reordering');
                this.showNotice('Drag and drop questions to reorder them.', 'info');
            } else {
                tbody.sortable('disable');
                button.text('Enable Reordering');
            }
        },

        refreshQuestionsList: function() {
            // Reload the page to refresh the questions list
            // In a more advanced implementation, this could be done via AJAX
            window.location.reload();
        },

        showNotice: function(message, type) {
            const notice = $(`<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`);
            $('.wrap h1').after(notice);

            setTimeout(function() {
                notice.fadeOut();
            }, 3000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
       if ($('#wp-dynamic-survey-question-management-section').length > 0 ||
            ($('#wp-dynamic-survey-add-first-question-btn').length > 0)) {
            WPDynamicSurveyQuestionManager.init();
        }

    });

    // Expose to global scope for debugging
    window.WPDynamicSurveyQuestionManager = WPDynamicSurveyQuestionManager;

})(jQuery);