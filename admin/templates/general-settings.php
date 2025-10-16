<?php
/**
 * General Settings Template
 *
 * @package WP_Dynamic_Survey
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="general-settings-wrapper">
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('wp_dynamic_survey_save_settings'); ?>
        <input type="hidden" name="action" value="wp_dynamic_survey_save_settings">
        <input type="hidden" name="current_tab" value="general">

        <!-- Form Settings Section -->
        <div class="settings-section">
            <h2><?php echo esc_html__('Form Settings', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>
            <table class="form-table">
                <!-- Setting 1: Two-Stage Form -->
                <tr>
                    <th scope="row">
                        <label for="two_stage_form">
                            <?php echo esc_html__('Two-Stage Form', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   id="two_stage_form"
                                   name="two_stage_form"
                                   value="1"
                                   <?php checked($two_stage_form, 1); ?>>
                            <?php echo esc_html__('Enable two-stage participant form', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </label>
                        <p class="description">
                            <?php echo esc_html__('When enabled, participants will provide basic information first (name, email, address, zipcode), then phone number in a second step. When disabled, all fields appear together.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </p>
                    </td>
                </tr>

                <!-- Setting 2: Two-Page Survey -->
                <!-- <tr>
                    <th scope="row">
                        <label for="two_page_mode">
                            <?php echo esc_html__('Two-Page Survey', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   id="two_page_mode"
                                   name="two_page_mode"
                                   value="1"
                                   <?php checked($two_page_mode, 1); ?>>
                            <?php echo esc_html__('Enable two-page survey form', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </label>
                        <p class="description">
                            <?php echo esc_html__('When enabled, participant information and survey questions will be displayed on separate pages. You\'ll need to add the survey shortcode to both pages and configure the second page URL in each survey\'s settings.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </p>
                    </td>
                </tr> -->

                <!-- Setting 3: Multiple Submissions -->
                <tr>
                    <th scope="row">
                        <label for="allow_duplicate_emails">
                            <?php echo esc_html__('Multiple Submissions', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   id="allow_duplicate_emails"
                                   name="allow_duplicate_emails"
                                   value="1"
                                   <?php checked($allow_duplicate_emails, 1); ?>>
                            <?php echo esc_html__('Allow multiple submissions with same email', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </label>
                        <p class="description">
                            <?php echo esc_html__('When enabled, users can submit the same survey multiple times using the same email address. When disabled, each email can only submit once per survey.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Participant Information Fields Section -->
        <div class="settings-section">
            <h2><?php echo esc_html__('Participant Information Fields', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php echo esc_html__('Required Fields', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </th>
                    <td>
                        <p><strong><?php echo esc_html__('Name and Email are always required', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></strong></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo esc_html__('Optional Fields', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox"
                                       id="field_address"
                                       name="field_address"
                                       value="1"
                                       <?php checked($field_address, 1); ?>>
                                <?php echo esc_html__('Collect Address', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox"
                                       id="field_zipcode"
                                       name="field_zipcode"
                                       value="1"
                                       <?php checked($field_zipcode, 1); ?>>
                                <?php echo esc_html__('Collect Zipcode', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox"
                                       id="field_phone"
                                       name="field_phone"
                                       value="1"
                                       <?php checked($field_phone, 1); ?>>
                                <?php echo esc_html__('Collect Phone Number', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Privacy Policy Settings Section -->
        <div class="settings-section">
            <h2><?php echo esc_html__('Privacy Policy Settings', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>

            <!-- Single Privacy Policy (shown when two-stage is disabled) -->
            <div id="single_privacy_policy_wrapper" style="<?php echo $two_stage_form == 1 ? 'display:none;' : ''; ?>">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="privacy_policy">
                                <?php echo esc_html__('Privacy Policy', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </label>
                        </th>
                        <td>
                            <?php
                            wp_editor($privacy_policy, 'privacy_policy', array(
                                'textarea_name' => 'privacy_policy',
                                'textarea_rows' => 10,
                                'media_buttons' => false,
                                'teeny' => true,
                                'tinymce' => array(
                                    'toolbar1' => 'bold,italic,underline,link,unlink,bullist,numlist,undo,redo'
                                )
                            ));
                            ?>
                            <p class="description">
                                <?php echo esc_html__('This text will appear below the participant form with a required checkbox. Leave empty to disable.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Two-Stage Privacy Policy (shown when two-stage is enabled) -->
            <div id="two_stage_privacy_policy_wrapper" style="<?php echo $two_stage_form != 1 ? 'display:none;' : ''; ?>">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="privacy_policy_stage1">
                                <?php echo esc_html__('Privacy Policy - Stage 1', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </label>
                        </th>
                        <td>
                            <?php
                            wp_editor($privacy_policy_stage1, 'privacy_policy_stage1', array(
                                'textarea_name' => 'privacy_policy_stage1',
                                'textarea_rows' => 10,
                                'media_buttons' => false,
                                'teeny' => true,
                                'tinymce' => array(
                                    'toolbar1' => 'bold,italic,underline,link,unlink,bullist,numlist,undo,redo'
                                )
                            ));
                            ?>
                            <p class="description">
                                <?php echo esc_html__('Appears on first form (name, email, address, zipcode) with required checkbox', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="privacy_policy_stage2">
                                <?php echo esc_html__('Privacy Policy - Stage 2 (Phone Number)', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </label>
                        </th>
                        <td>
                            <?php
                            wp_editor($privacy_policy_stage2, 'privacy_policy_stage2', array(
                                'textarea_name' => 'privacy_policy_stage2',
                                'textarea_rows' => 10,
                                'media_buttons' => false,
                                'teeny' => true,
                                'tinymce' => array(
                                    'toolbar1' => 'bold,italic,underline,link,unlink,bullist,numlist,undo,redo'
                                )
                            ));
                            ?>
                            <p class="description">
                                <?php echo esc_html__('Appears on second form (phone number) with required checkbox', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Optional Phone Number Stage -->
        <div class="settings-section" id="phone_optional_wrapper" style="<?php echo $two_stage_form != 1 ? 'display:none;' : ''; ?>">
            <h2><?php echo esc_html__('Stage 2 Options', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="phone_optional">
                            <?php echo esc_html__('Phone Number Stage', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   id="phone_optional"
                                   name="phone_optional"
                                   value="1"
                                   <?php checked($phone_optional, 1); ?>>
                            <?php echo esc_html__('Make phone number optional (Stage 2)', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </label>
                        <p class="description">
                            <?php echo esc_html__('When enabled, users can skip the phone number stage. Only applies when two-stage form is enabled.', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Save Button -->
        <p class="submit">
            <input type="submit"
                   name="submit"
                   id="submit"
                   class="button button-primary"
                   value="<?php echo esc_attr__('Save Changes', WP_DYNAMIC_SURVEY_TEXT_DOMAIN); ?>">
        </p>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Store previous two-stage form state when disabling phone
    var previousTwoStageState = $('#two_stage_form').is(':checked');

    // Handle Phone Number / Two-Stage Form Interaction
    $('#field_phone').on('change', function() {
        if (!$(this).is(':checked')) {
            // Store current state before disabling
            previousTwoStageState = $('#two_stage_form').is(':checked');

            // Phone unchecked: disable and uncheck two-stage form
            $('#two_stage_form').prop('checked', false).prop('disabled', true);
            // Trigger change to update dependent fields
            $('#two_stage_form').trigger('change');
        } else {
            // Phone checked: enable two-stage form
            $('#two_stage_form').prop('disabled', false);

            // Restore previous state
            if (previousTwoStageState) {
                $('#two_stage_form').prop('checked', true);
            }

            // Trigger change to restore dependent fields
            $('#two_stage_form').trigger('change');
        }
    });

    // Handle Privacy Policy Editor and Optional Phone Toggling
    $('#two_stage_form').on('change', function() {
        if ($(this).is(':checked')) {
            // Two-stage enabled
            $('#single_privacy_policy_wrapper').hide();
            $('#two_stage_privacy_policy_wrapper').show();
            $('#phone_optional_wrapper').show();
        } else {
            // Two-stage disabled
            $('#single_privacy_policy_wrapper').show();
            $('#two_stage_privacy_policy_wrapper').hide();
            $('#phone_optional_wrapper').hide();
        }
    });

    // On page load: check initial state and apply visibility rules
    if (!$('#field_phone').is(':checked')) {
        $('#two_stage_form').prop('disabled', true);
    }
    // Trigger change event on page load to set correct initial visibility
    $('#two_stage_form').trigger('change');
});
</script>
