<?php
/**
 * General Settings Template
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="general-settings-wrapper">
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('flowq_save_settings'); ?>
        <input type="hidden" name="action" value="flowq_save_settings">
        <input type="hidden" name="current_tab" value="general">

        <!-- Form Settings Section -->
        <div class="settings-section">
            <h2><?php echo esc_html__('Form Settings', 'flowq'); ?></h2>
            <table class="form-table">
                <!-- Setting 1: Two-Stage Form -->
                <tr>
                    <th scope="row">
                        <label for="two_stage_form">
                            <?php echo esc_html__('Two-Stage Form', 'flowq'); ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   id="two_stage_form"
                                   name="two_stage_form"
                                   value="1"
                                   <?php checked($two_stage_form, 1); ?>>
                            <?php echo esc_html__('Enable two-stage participant form', 'flowq'); ?>
                        </label>
                        <p class="description">
                            <?php echo esc_html__('When enabled, participants will provide basic information first (name, email, address, zipcode), then phone number in a second step. When disabled, all fields appear together.', 'flowq'); ?>
                        </p>
                    </td>
                </tr>

                <!-- Setting 2: Two-Page Survey -->
                <!-- <tr>
                    <th scope="row">
                        <label for="two_page_mode">
                            <?php echo esc_html__('Two-Page Survey', 'flowq'); ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   id="two_page_mode"
                                   name="two_page_mode"
                                   value="1"
                                   <?php checked($two_page_mode, 1); ?>>
                            <?php echo esc_html__('Enable two-page survey form', 'flowq'); ?>
                        </label>
                        <p class="description">
                            <?php echo esc_html__('When enabled, participant information and survey questions will be displayed on separate pages. You\'ll need to add the survey shortcode to both pages and configure the second page URL in each survey\'s settings.', 'flowq'); ?>
                        </p>
                    </td>
                </tr> -->

                <!-- Setting 3: Multiple Submissions -->
                <tr>
                    <th scope="row">
                        <label for="allow_duplicate_emails">
                            <?php echo esc_html__('Multiple Submissions', 'flowq'); ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   id="allow_duplicate_emails"
                                   name="allow_duplicate_emails"
                                   value="1"
                                   <?php checked($allow_duplicate_emails, 1); ?>>
                            <?php echo esc_html__('Allow multiple submissions with same email', 'flowq'); ?>
                        </label>
                        <p class="description">
                            <?php echo esc_html__('When enabled, users can submit the same survey multiple times using the same email address. When disabled, each email can only submit once per survey.', 'flowq'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Participant Information Fields Section -->
        <div class="settings-section">
            <h2><?php echo esc_html__('Participant Information Fields', 'flowq'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php echo esc_html__('Required Fields', 'flowq'); ?>
                    </th>
                    <td>
                        <p><strong><?php echo esc_html__('Name and Email are always required', 'flowq'); ?></strong></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo esc_html__('Optional Fields', 'flowq'); ?>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox"
                                       id="field_address"
                                       name="field_address"
                                       value="1"
                                       <?php checked($field_address, 1); ?>>
                                <?php echo esc_html__('Collect Address', 'flowq'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox"
                                       id="field_zipcode"
                                       name="field_zipcode"
                                       value="1"
                                       <?php checked($field_zipcode, 1); ?>>
                                <?php echo esc_html__('Collect Zipcode', 'flowq'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox"
                                       id="field_phone"
                                       name="field_phone"
                                       value="1"
                                       <?php checked($field_phone, 1); ?>>
                                <?php echo esc_html__('Collect Phone Number', 'flowq'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Privacy Policy Settings Section -->
        <div class="settings-section">
            <h2><?php echo esc_html__('Privacy Policy Settings', 'flowq'); ?></h2>

            <!-- Single Privacy Policy (shown when two-stage is disabled) -->
            <div id="single_privacy_policy_wrapper" style="<?php echo $two_stage_form == 1 ? 'display:none;' : ''; ?>">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="privacy_policy">
                                <?php echo esc_html__('Privacy Policy', 'flowq'); ?>
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
                                <?php echo esc_html__('This text will appear below the participant form with a required checkbox. Leave empty to disable.', 'flowq'); ?>
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
                                <?php echo esc_html__('Privacy Policy - Stage 1', 'flowq'); ?>
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
                                <?php echo esc_html__('Appears on first form (name, email, address, zipcode) with required checkbox', 'flowq'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="privacy_policy_stage2">
                                <?php echo esc_html__('Privacy Policy - Stage 2 (Phone Number)', 'flowq'); ?>
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
                                <?php echo esc_html__('Appears on second form (phone number) with required checkbox', 'flowq'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Optional Phone Number Stage -->
        <div class="settings-section" id="phone_optional_wrapper" style="<?php echo $two_stage_form != 1 ? 'display:none;' : ''; ?>">
            <h2><?php echo esc_html__('Stage 2 Options', 'flowq'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="phone_optional">
                            <?php echo esc_html__('Phone Number Stage', 'flowq'); ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   id="phone_optional"
                                   name="phone_optional"
                                   value="1"
                                   <?php checked($phone_optional, 1); ?>>
                            <?php echo esc_html__('Make phone number optional (Stage 2)', 'flowq'); ?>
                        </label>
                        <p class="description">
                            <?php echo esc_html__('When enabled, users can skip the phone number stage. Only applies when two-stage form is enabled.', 'flowq'); ?>
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
                   value="<?php echo esc_attr__('Save Changes', 'flowq'); ?>">
        </p>
    </form>
</div>

