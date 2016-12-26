/**
 * @author: Merlin Mai
 */
;
jQuery(document).ready(function() {
    /**
     * Check password with custom rule
     */
    $.validator.addMethod("passwordCheck", function(value, element) {
        return this.optional(element) || /^[A-Za-z0-9\d=!\-@._*]*$/.test(value) && (// consists 1 of 3 these
                (/[a-z]/.test(value) && /[A-Z]/.test(value)) || // has lower and uppercase
                (/\d/.test(value) && /[A-Z]/.test(value)) || // has uppercase and numeric
                (/\d/.test(value) && /[a-z]/.test(value))       // has lowercase and numeric
                )
    }, "Contain at least 2 of the following: uppercase, lowercase, numeric.");

    /**
     * Check verify password after password is valid
     */
    $.validator.addMethod("confirmPasswordCheck", function(value, element, data) {
//        No need, cause already reset in modal. Make error message not disappear if apply
//        if (valid) {
//             Auto remove error marker
//            jQuery(element).removeClass('error');     
//            jQuery("#" + jQuery(element).attr('id') + '-error').remove();
//        }
        return value == $(data).val();
    }, "Password does not match.");

    /**
     * Check username only accept alphabetic and numeric characters
     */
    jQuery.validator.addMethod("alphabetNumber", function(value, element) {
        return this.optional(element) || /^[a-zA-Z0-9]+$/.test(value);
    }, 'Please enter only alphabet and/or numeric.');

    /**
     * Allow letter only
     */
    jQuery.validator.addMethod("letterOnly", function(value, element) {
        return this.optional(element) || /^[a-zA-Z]+$/.test(value);
    }, 'Letter only.');

    /**
     * Email custom
     */
    jQuery.validator.addMethod("emailCustom", function(value, element) {
        return this.optional(element) ||
                /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,})\.([a-z]{2,}(?:\.[a-z]{2})?)$/.test(value);
    }, 'Please enter a valid email address.');
    
    /**
     * Positive integer and not allow space
     */
    jQuery.validator.addMethod("positiveIntNoSpace", function(value, element) {
        if (value == "")    {
            return true;
        } else  {
            return /^[0-9]+$/.test(value) && value.indexOf(" ") < 0;
        }
    }, 'Please enter only numeric.');

});