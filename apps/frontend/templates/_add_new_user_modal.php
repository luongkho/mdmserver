<?php /**
 * Created by PhpStorm.
 * User: longtm
 * Date: 6/1/2015
 * Time: 5:21 PM
 */ ?>
<?php use_javascript('jquery.validate.min.js') ?>
<?php use_javascript('jquery.validate.custom.methods.js') ?>
<?php use_stylesheet('bootstrap-datepicker3.min.css') ?>
<?php use_javascript('moment.min.js') ?>
<?php use_javascript('bootstrap-datepicker.min.js') ?>
<?php use_javascript('components-pickers.js') ?>

<!-- BEGIN MODAL ADD-USER -->
<div class="modal fade bs-modal-lg" id="basic" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f5f5f5">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" default-title="Add New User">Add New User</h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body form">
                    <!-- BEGIN FORM-->
                    <form action="<?php echo url_for('ajax_add_new_user', array('sf_format' => 'json')); ?>" id="add_new_person_form" class="form-horizontal" novalidate="novalidate">
                        <div class="form-body">
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4" style="padding-right:0">Username<span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-7">
                                        <input type="text" name="user_name" tabindex="1" class="form-control" maxlength="50">
                                    </div>
                                    <a href="javascript:;" onclick="tooltip_help(this);
                                            return false;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                            title="<?php echo $userTooltip['username']['content']; ?>"
                                            data-header="<?php echo $userTooltip["username"]['title'] ?>"
                                            data-content="<?php echo $userTooltip["username"]['content'] ?>">
                                    </a>
                                </div>
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4"style="padding-right:0">First Name <span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-8">
                                        <input name="first_name" type="text" tabindex="6" class="form-control" maxlength="50">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4" style="padding-right:0">Email <span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-7">
                                        <input name="email" type="text" tabindex="2" class="email form-control" maxlength="500">
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4" style="padding-right:0">Last Name <span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-8">
                                        <input name="last_name" type="text" tabindex="7" class="form-control" maxlength="50">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4" style="padding-right:0">Password <span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-7">
                                        <input name="password" id="new_user_modal_password" tabindex="3" type="password" class="form-control">
                                    </div>
                                    <a href="javascript:;" onclick="tooltip_help(this);
                                            return false;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                            title="<?php echo $userTooltip["password"]['content'] ?>"
                                            data-header="<?php echo $userTooltip["password"]['title'] ?>"
                                            data-content="<?php echo $userTooltip["password"]['content'] ?>">
                                    </a>
                                </div>
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4" style="padding-right:0">Provider
                                    </label>
                                    <div class="col-xs-8">
                                        <select class="form-control select-picker" tabindex="8" name="provider">
                                            <?php
                                            $providers = array(
                                                '@' => 'None',
                                                '@txt.att.net' => 'AT&T(USA)',
                                                '@vtext.com' => 'Verizon(USA)',
                                                '@tmomail.net' => 'T-Mobile(USA)',
                                                '@blueskyfrog.com' => 'Blue Sky Frog(AUS)',
                                                '@optusmobile.com.au' => 'Optus Mobile(AUS)',
                                                '@sms.utbox.net' => 'UTBox(AUS)'
                                            );
                                            foreach ($providers as $key => $provider) {
                                                echo "<option value='{$key}'>{$provider}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4" style="padding-right:0">Verify Password <span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-7">
                                        <input name="confirm_password" id="new_user_modal_confirm_password" tabindex="4" type="password" class="form-control">
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4" style="padding-right:0">Phone Number
                                    </label>
                                    <div class="col-xs-8">
                                        <input name="phone_number" type="text" tabindex="9" class="form-control" maxlength="20">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-xs-6 offset-6">
                                    <label class="control-label col-xs-4" style="padding-right:0">Role <span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-7">
                                        <select class="required form-control select-picker" tabindex="5" name="role">
                                            <?php
                                            if (!empty($roles))
                                                foreach ($roles as $role) {
                                                    echo "<option value='{$role->role_id}'>{$role->getRoleName()}</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="error-holder alert-danger">
                                <span></span>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="text-center" style="margin: 0 auto">
                                        <button type="submit" class="btn green"><span class="loading "><i class="fa-spin fa fa-refresh"></i>&nbsp;</span>Submit</button>
                                        <button type="button" class="btn default" data-dismiss="modal">Cancel</button>
                                        <input id="new_user_modal_user_id" type="hidden" name="id" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- END FORM-->
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_partial('global/help_prompt_modal') ?>

<script>
    var add_validator;
    var add_errorHolder = $("#add_new_person_form").find('.error-holder');
    
    function tooltip_help(element) {
        var header = $(element).attr('data-header');
        // Change break line and list symbol in tooltip to html
        var content = $(element).attr('data-content');
        content = content.replace("\n", "<br>");
        content = content.replace(/\./g, "<li>");
        // Set header and content for promt
        $("#help_modal").find("h3").html(header);
        $("#help_modal").find(".modal-body").html(content);
        $("#help_modal").modal("show");
    }

    jQuery(document).ready(function() {

        ComponentsPickers.init();
        jQuery('body').on('reset_add_new_user_form', function(evt) {
            reset_add_new_user_form_fields();
        });
    
        add_validator = jQuery("#add_new_person_form").validate({
            rules: {
                user_name: {
                    required: true,
                    minlength: 6,
                    alphabetNumber: true
                },
                first_name: {
                    required: true
                },
                email:  {
                    required: true,
                    emailCustom: true
                },
                last_name:  {
                    required: true
                },
                phone_number: {
                    positiveIntNoSpace: true
                },
                password: {
                    required: true,
                    minlength: 8,
                    passwordCheck: true
                },
                confirm_password: {
                    required: true,
                    confirmPasswordCheck: '#new_user_modal_password',
                }
            },
            onfocusout: function(element) {
                this.element(element);  // <- "eager validation"
            },
            submitHandler: function(form) {
                ajax_add_new_user(form);
            }
        });
    });
    
    // Reset validate form when close
    $('#basic').on('hide.bs.modal', function() {
        add_validator.resetForm();//remove error class on name elements and clear history
        add_validator.reset();//remove all error and success data
        add_errorHolder.html('');
    });

    function ajax_add_new_user(form) {
        var self = jQuery(form);
        var url = self.attr('action');
        var loading = self.find('.loading').first();
        if (loading.is(':visible'))
            return false;
        loading.show();
        jQuery.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: self.serialize(),
            success: function(data) {
                if (data.error.status) {
                    var error_msg = data.error.msg;
                    add_errorHolder.html(error_msg);
                    add_errorHolder.show();
                } else {
                    // prevent a event
                    jQuery('body').trigger('after_add_new_user', data.data);
                    $('#basic').modal('hide');
                }
                loading.hide();
            },
            error: function() {
                loading.hide();
            }
        });
        return false;
    }
    function reset_add_new_user_form_fields() {
        console.log("Reset Add New User form");
        var self = jQuery("#add_new_person_form");
        self.find('input:not(.keep-data)').val('');
        self.find('select:not(.keep-data)').each(function() {
            var sdefault = $(this).find('option:first').val();
            $(this).val(sdefault);
            $(this).selectpicker('refresh');
        });
    }
</script>
