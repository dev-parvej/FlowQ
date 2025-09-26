<?php
/**
 * Question Form Template
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$is_editing = !empty($question_data);
$form_action = $is_editing ? 'update_question' : 'create_question';
?>

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <?php wp_nonce_field('wp_dynamic_survey_question_action'); ?>

    <input type="hidden" name="action" value="wp_dynamic_survey_save_question">
    <input type="hidden" name="question_action" value="<?php echo esc_attr($form_action); ?>">
    <input type="hidden" name="survey_id" value="<?php echo esc_attr($selected_survey_id); ?>">

    <?php if ($is_editing): ?>
        <input type="hidden" name="question_id" value="<?php echo esc_attr($question_data['id']); ?>">
    <?php endif; ?>

    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="question_title"><?php echo esc_html__('Question Title', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?> *</label>
                </th>
                <td>
                    <input type="text" id="question_title" name="question_title" class="large-text" rows="2" required value="<?php echo esc_textarea($question_data['title'] ?? ''); ?>" />
                    <p class="description"><?php echo esc_html__('Enter the question text that participants will see.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="question_description"><?php echo esc_html__('Description', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <textarea id="question_description" name="question_description" class="large-text" rows="3"><?php echo esc_textarea($question_data['description'] ?? ''); ?></textarea>
                    <p class="description"><?php echo esc_html__('Optional additional information or instructions for this question.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="question_extra_message"><?php echo esc_html__('Extra Message', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <textarea id="question_extra_message" name="question_extra_message" class="large-text" rows="3"><?php echo esc_textarea($question_data['extra_message'] ?? ''); ?></textarea>
                    <p class="description"><?php echo esc_html__('Optional message shown after participant answers this question. Example: "If you choose yes, our expert will contact you about next steps."', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                </td>
            </tr>


        </tbody>
    </table>

    <!-- Answer Options Section -->
    <div id="answer-options-section">
        <h3><?php echo esc_html__('Answer Options', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h3>
            
        <div id="answer-options-container">
            <?php if ($is_editing && !empty($question_data['answers'])): ?>
                <?php foreach ($question_data['answers'] as $answer): ?>
                    <div class="answer-option-row">
                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row" style="width: 150px;">
                                        <label><?php echo esc_html__('Answer Text', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="answer_text[]" class="regular-text" value="<?php echo esc_attr($answer['answer_text']); ?>" required>
                                        <input type="hidden" name="answer_id[]" value="<?php echo esc_attr($answer['id']); ?>">
                                    </td>
                                    <td style="width: 100px;">
                                        <button type="button" class="button button-small button-link-delete remove-answer-option">
                                            <?php echo esc_html__('Remove', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label><?php echo esc_html__('Next Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                                    </th>
                                    <td colspan="2">
                                        <select name="next_question_id[]" class="regular-text">
                                            <option value=""><?php echo esc_html__('Select Next Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                                            <?php foreach ($questions as $q): ?>
                                                <?php if ($q['id'] != ($question_data['id'] ?? 0)): ?>
                                                    <option value="<?php echo esc_attr($q['id']); ?>" <?php selected($answer['next_question_id'], $q['id']); ?>>
                                                        <?php echo esc_html($q['title']); ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label><?php echo esc_html__('Redirect URL', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                                    </th>
                                    <td colspan="2">
                                        <input type="url" name="answer_redirect_url[]" class="regular-text" value="<?php echo esc_attr($answer['redirect_url']); ?>">
                                        <p class="description"><?php echo esc_html__('Optional: Redirect to external URL when this answer is selected.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <hr>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <p>
            <button type="button" class="button button-secondary" id="add-answer-option">
                <?php echo esc_html__('Add Answer Option', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
            </button>
        </p>
    </div>

    <p class="submit">
        <input type="submit" class="button button-primary" value="<?php echo esc_attr($is_editing ? __('Update Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN) : __('Create Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN)); ?>">
        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-dynamic-surveys-questions&survey_id=' . $selected_survey_id)); ?>" class="button button-secondary">
            <?php echo esc_html__('Cancel', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
        </a>
    </p>
</form>

<!-- Answer Option Template -->
<div id="answer-option-template" style="display: none;">
    <div class="answer-option-row">
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row" style="width: 150px;">
                        <label><?php echo esc_html__('Answer Text', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                    </th>
                    <td>
                        <input type="text" name="answer_text[]" class="regular-text" required>
                        <input type="hidden" name="answer_id[]" value="">
                    </td>
                    <td style="width: 100px;">
                        <button type="button" class="button button-small button-link-delete remove-answer-option">
                            <?php echo esc_html__('Remove', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php echo esc_html__('Next Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                    </th>
                    <td colspan="2">
                        <select name="next_question_id[]" class="regular-text">
                            <option value=""><?php echo esc_html__('Select Next Question', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></option>
                            <?php if (!empty($questions)): ?>
                                <?php foreach ($questions as $q): ?>
                                    <option value="<?php echo esc_attr($q['id']); ?>">
                                        <?php echo esc_html($q['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php echo esc_html__('Redirect URL', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></label>
                    </th>
                    <td colspan="2">
                        <input type="url" name="answer_redirect_url[]" class="regular-text">
                        <p class="description"><?php echo esc_html__('Optional: Redirect to external URL when this answer is selected.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize answer options for single choice
    if ($('#answer-options-container').children().length === 0) {
        addAnswerOption();
        addAnswerOption();
    }

    // Add answer option
    $('#add-answer-option').on('click', function() {
        addAnswerOption();
    });

    // Remove answer option
    $(document).on('click', '.remove-answer-option', function() {
        $(this).closest('.answer-option-row').remove();
    });

    function addAnswerOption(defaultText = '') {
        var template = $('#answer-option-template').html();
        var answerRow = $(template);

        if (defaultText) {
            answerRow.find('input[name="answer_text[]"]').val(defaultText);
        }

        $('#answer-options-container').append(answerRow);
    }

});
</script>

<style>
.answer-option-row {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: #f9f9f9;
}

.answer-option-row hr {
    margin: 15px 0 0 0;
    border: none;
    border-top: 1px solid #ddd;
}

.survey-selection-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    margin-bottom: 20px;
}

.survey-info-section {
    background: #f0f6fc;
    border: 1px solid #c3dbf0;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.questions-list-section {
    margin-top: 20px;
}

.question-form-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    margin-top: 20px;
}
</style>