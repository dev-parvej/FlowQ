/**
 * General Settings JavaScript
 * @package FlowQ
 */

(function($) {
    'use strict';

    $(document).ready(function() {
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

})(jQuery);
