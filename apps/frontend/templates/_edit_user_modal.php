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
<div class="modal fade bs-modal-lg" id="basic-edit-user" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f5f5f5">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" default-title="Add New User">Edit User</h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body form">
                    <!-- BEGIN FORM-->
                    <form action="<?php echo url_for('ajax_add_new_user', array('sf_format' => 'json')); ?>" id="edit_person_form" class="form-horizontal" novalidate="novalidate">
                        <div class="form-body">
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4 disabled" style="padding-right:0">Username
                                    </label>
                                    <div class="col-xs-7">
                                        <input type="text" disabled name="user_name" tabindex="10" data-required="1" class="disabled form-control">
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4"style="padding-right:0">First Name <span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-8">
                                        <input name="first_name" type="text" tabindex="15" class="form-control" maxlength="50">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4" style="padding-right:0">Email <span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-7">
                                        <input name="email" type="text" tabindex="11" class="email form-control" maxlength="500">
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4" style="padding-right:0">Last Name <span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-8">
                                        <input name="last_name" type="text" tabindex="16" class="form-control" maxlength="50">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4" style="padding-right:0">Old Password
                                    </label>
                                    <div class="col-xs-7">
                                        <input name="old_password" tabindex="12" id="edit_user_modal_old_password" type="password" class="form-control">
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4" style="padding-right:0">Provider
                                    </label>
                                    <div class="col-xs-8">
                                        <select tabindex="17" class="form-control select-picker keep-data" name="provider">
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
                                    <label class="control-label col-xs-4" style="padding-right:0">New Password
                                    </label>
                                    <div class="col-xs-7">
                                        <input name="password" tabindex="13" id="edit_user_modal_password" type="password" class="form-control">
                                    </div>
                                    <a href="javascript:;" onclick="tooltip_help(this);
                                            return false;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                            title="<?php echo $userTooltip['password']['content']; ?>"
                                            data-header="<?php echo $userTooltip["password"]['title'] ?>"
                                            data-content="<?php echo $userTooltip["password"]['content'] ?>">
                                    </a>
                                </div>
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4" style="padding-right:0">Phone Number
                                    </label>
                                    <div class="col-xs-8">
                                        <input name="phone_number" tabindex="18" type="text" class="form-control" maxlength="20">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4" style="padding-right:0; padding-top: 0px">Confirm New Password
                                    </label>
                                    <div class="col-xs-7">
                                        <input name="confirm_password" tabindex="14" type="password" class="form-control">
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-4" style="padding-right:0">Role <span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-8">
                                        <select tabindex="19" class="required form-control select-picker" name="role">
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
<script>
    var edit_validator, edit_errorHolder = $("#edit_person_form").find(".error-holder");
    jQuery(document).ready(function() {
        ComponentsPickers.init();
        jQuery('body').on('reset_edit_user_form', function(evt) {
            reset_edit_user_form_fields();
        });
        jQuery('body').on('edit_user_form', function(evt, id) {
            fill_data_edit_user_form(id);
        });
        // Reset title when modal shown
        $('#basic-edit-user').on('show.bs.modal', function() {
            set_edit_user_modal_title(this);
        })
        edit_validator = jQuery("#edit_person_form").validate({
            rules: {
                first_name: {
                    required: true
                },
                email:   {
                    required: true,
                    emailCustom: true
                },
                last_name:  {
                    required: true
                },
                old_password: {
                    required: function() {
                        return jQuery("#edit_user_modal_password").val().length > 0;
                    },
                    minlength: 8,
                    passwordCheck: true
                },
                password: {
                    minlength: 8,
                    passwordCheck: true
                },
                confirm_password: {
                    confirmPasswordCheck: "#edit_user_modal_password"
                },
                phone_number: {
                    positiveIntNoSpace: true
                },
            },
            // Validate when out focus
            onfocusout: function(element) {
                this.element(element);  // <- "eager validation"
            },
            submitHandler: function(form) {
                ajax_edit_user(form);
            }
        });
    });

    function ajax_edit_user(form) {
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
                    edit_errorHolder.html(error_msg);
                    edit_errorHolder.show();
                } else {
                    // prevent a event
                    jQuery('body').trigger('after_add_new_user', data.data);
                    jQuery('body').trigger('after_edit_user', data.data);
                    $('#basic-edit-user').modal('hide');
                }
                loading.hide();
            },
            error: function() {
                loading.hide();
            }
        });
        return false;
    }
    function reset_edit_user_form_fields() {
        console.log("Reset Add New User form");
        var self = jQuery("#edit_person_form");
        self.find('input:not(.keep-data)').val('');
        self.each(function() {
            var my_self = jQuery(this);
            my_self.find('select:not(.keep-data)').find('option:eq(0)').prop('selected', true);
            my_self.selectpicker('refresh');
        });
        setTimeout(function() {
            self.find('input[type=text]').first().focus();
        }, 700);
    }
    function fill_data_edit_user_form(id) {
        var self = jQuery("#edit_person_form");
        jQuery.ajax({
            url: '<?php echo url_for('ajax_get_user', array('sf_format' => 'json')); ?>',
            type: 'GET',
            dataType: 'json',
            data: {id: id},
            success: function(data) {
                if (data.error.status) {
                    my_alert(data.error.msg, 'error');
                } else {
                    self.find('input:not(.keep-data)').each(function() {
                        var my_self = jQuery(this);
                        var name = my_self.attr('name');
                        var value = data.data[name];
                        if (name == 'phone_number') {
                            var phone = /@/.exec(value);
                            // explaination:
                            /*
                             phone = /(\d+)@([\.|\w]+)/.exec('0905533205@txt.att.net');
                             --> ["0905533205@txt.att.net", "0905533205", "txt.att.net"]
                             phone = /(\d+)@([\.|\w]+)/.exec('0905533205');
                             --> null
                             */
                            console.log(value);
                            if (phone) {
                                // change value of provider
                                $('.select-picker[name="provider"]').selectpicker('val', '@' + value.substring(phone.index + 1));
                                value = value.substring(0, phone.index);
                            }
                            else {
                                value = null;
                                console.log(value);
                                $('.select-picker[name="provider"]').selectpicker('val', '@');
                            }
                            my_self.val(value);
                        } else {
                            my_self.val(value ? value : '');
                        }
                    });
                    self.find('select:not(.keep-data)').each(function() {
                        var my_self = jQuery(this);
                        var name = my_self.attr('name');
                        if (name == 'role')
                            name = 'role_id';
                        var value = data.data[name];
                        my_self.val(value ? value : '');
                        my_self.selectpicker('refresh');
                    });
                    $('#basic-edit-user').modal('show');
                }
            },
            error: function() {

            }
        });
    }
    function set_edit_user_modal_title(elm) {
        var title = jQuery(elm).find('h4.modal-title').first();
    }
    
    // Reset validate form when close
    $('#basic-edit-user').on('hide.bs.modal', function() {
        edit_validator.resetForm();//remove error class on name elements and clear history
        edit_validator.reset();//remove all error and success data
        edit_errorHolder.html('');  //Clear error message
    });
</script>
