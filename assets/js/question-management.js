/**
 * Question Management JavaScript for WP Dynamic Survey Plugin
 *
 * @package FlowQ
 */

(function($) {
    'use strict';

    const FlowQQuestionManager = {
        modal: null,
        form: null,
        currentQuestionId: null,
        surveyId: null,

        init: function() {
            this.modal = $('#flowq-question-modal');
            this.form = $('#flowq-question-form');
            // Get survey ID from the hidden input in the question form or from data attribute
            this.surveyId = $('#flowq-survey-id').val() || $('#flowq-question-management-section').data('survey-id');

            this.bindEvents();
            this.initSortable();

            console.log('QuestionManager initialized with survey ID:', this.surveyId);
        },

        bindEvents: function() {
            console.log('Binding events');
            
            // Add question buttons
            $(document).on('click', '#flowq-add-first-question-btn, #flowq-add-new-question-btn', this.openAddQuestionModal.bind(this));

            // Edit question button
            $(document).on('click', '.flowq-edit-question-btn', this.openEditQuestionModal.bind(this));

            // Duplicate question button
            $(document).on('click', '.flowq-duplicate-question-btn', this.duplicateQuestion.bind(this));

            // Delete question button
            $(document).on('click', '.flowq-delete-question-btn', this.deleteQuestion.bind(this));

            // Modal controls
            $(document).on('click', '.flowq-modal-close, #flowq-cancel-question', this.closeModal.bind(this));
            $(document).on('click', '.flowq-modal-backdrop', this.closeModal.bind(this));
            $(document).on('click', '#flowq-save-question', this.saveQuestion.bind(this));

            // Question type change
            $(document).on('change', '#flowq-question-type', this.handleQuestionTypeChange.bind(this));

            // Answer options management
            $(document).on('click', '#flowq-add-answer-option', this.addAnswerOption.bind(this));
            $(document).on('click', '.flowq-remove-answer-option', this.removeAnswerOption.bind(this));

            // Reorder questions
            $(document).on('click', '#flowq-reorder-questions-btn', this.toggleReorderMode.bind(this));

            // Escape key to close modal
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && this.modal.is(':visible')) {
                    this.closeModal();
                }
            }.bind(this));
        },

        initSortable: function() {
            $('#flowq-questions-tbody').sortable({
                handle: '.flowq-drag-handle',
                placeholder: 'flowq-ui-sortable-placeholder',
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
            $('#flowq-modal-title').text(flowqAdmin.strings.add_question || 'Add New Question');
            this.showModal();
            this.handleQuestionTypeChange();
        },

        openEditQuestionModal: function(e) {
            e.preventDefault();
            const questionId = $(e.target).data('question-id');
            this.currentQuestionId = questionId;
            $('#flowq-modal-title').text(flowqAdmin.strings.edit_question || 'Edit Question');
            this.loadQuestionData(questionId);
            this.showModal();
        },

        showModal: function() {
            this.modal.show();
            $('body').addClass('flowq-modal-open');
            $('#flowq-question-title').focus();
        },

        closeModal: function(e) {
            if (e) {
                e.preventDefault();
            }
            this.modal.hide();
            $('body').removeClass('flowq-modal-open');
            this.resetForm();
        },

        resetForm: function() {
            this.form[0].reset();
            $('#flowq-question-id').val('');
            $('#flowq-answer-options-container').empty();
            $('#flowq-answer-options-section').hide();
            this.currentQuestionId = null;
        },

        handleQuestionTypeChange: function() {
            const questionType = $('#flowq-question-type').val();
            const answerOptionsSection = $('#flowq-answer-options-section');

            if (questionType === 'single_choice') {
                answerOptionsSection.show();

                // Add default options for single choice
                if ($('#flowq-answer-options-container').children().length === 0) {
                    this.addAnswerOption();
                    this.addAnswerOption();
                }
            } else {
                answerOptionsSection.hide();
                $('#flowq-answer-options-container').empty();
            }
        },

        addAnswerOption: function(e, defaultText = '') {
            if (e) {
                e.preventDefault();
            }

            const template = $('#flowq-answer-option-template').html();
            const answerRow = $(template);

            if (defaultText) {
                answerRow.find('.flowq-answer-text').val(defaultText);
            }

            // Populate next question dropdown
            this.populateNextQuestionDropdown(answerRow.find('.flowq-answer-next-question'));

            $('#flowq-answer-options-container').append(answerRow);
        },

        removeAnswerOption: function(e) {
            e.preventDefault();
            $(e.target).closest('.flowq-answer-option-row').remove();
        },

        populateNextQuestionDropdown: function(dropdown) {
            // Clear existing options except the first one
            dropdown.find('option:not(:first)').remove();

            // Add questions from the current survey
            $('#flowq-questions-tbody .flowq-question-row').each(function() {
                const questionId = $(this).data('question-id');
                const questionTitle = $(this).find('.flowq-question-title').text();

                if (questionId != FlowQQuestionManager.currentQuestionId) {
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
            $('#flowq-question-id').val(questionData.id);
            $('#flowq-question-title').val(questionData.title);
            $('#flowq-question-description').val(questionData.description);
            $('#flowq-question-type').val(questionData.type);
            $('#flowq-question-required').prop('checked', questionData.is_required == 1);
            $('#flowq-question-order').val(questionData.question_order);
            $('#flowq-redirect-url').val(questionData.redirect_url);

            // Handle question type change and load answers
            this.handleQuestionTypeChange();

            if (questionData.answers && questionData.answers.length > 0) {
                $('#flowq-answer-options-container').empty();
                questionData.answers.forEach(function(answer) {
                    this.addAnswerOption();
                    const lastRow = $('#flowq-answer-options-container .flowq-answer-option-row:last');
                    lastRow.find('.flowq-answer-id').val(answer.id);
                    lastRow.find('.flowq-answer-text').val(answer.answer_text);
                    lastRow.find('.flowq-answer-next-question').val(answer.next_question_id);
                    lastRow.find('.flowq-answer-redirect-url').val(answer.redirect_url);
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

            $('#flowq-save-question').prop('disabled', true).text(flowqAdmin.strings.saving || 'Saving...');

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
                    $('#flowq-save-question').prop('disabled', false).text(flowqAdmin.strings.save_question || 'Save Question');
                });
        },

        validateForm: function() {
            const title = $('#flowq-question-title').val().trim();
            if (!title) {
                alert('Question title is required.');
                $('#flowq-question-title').focus();
                return false;
            }

            const questionType = $('#flowq-question-type').val();
            if (questionType === 'single_choice') {
                const answers = $('#flowq-answer-options-container .flowq-answer-text');
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
            $('#flowq-questions-tbody .flowq-question-row').each(function(index) {
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
            $('#flowq-questions-tbody .flowq-question-row').each(function(index) {
                $(this).find('.flowq-question-order').text(index + 1);
            });
        },

        toggleReorderMode: function(e) {
            e.preventDefault();
            const button = $(e.target);
            const tbody = $('#flowq-questions-tbody');

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
       if ($('#flowq-question-management-section').length > 0 ||
            ($('#flowq-add-first-question-btn').length > 0)) {
            FlowQQuestionManager.init();
        }

    });

    // Expose to global scope for debugging
    window.FlowQQuestionManager = FlowQQuestionManager;

})(jQuery);