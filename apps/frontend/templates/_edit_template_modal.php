<?php /**
 * User: cuongnd
 * Date: 7/6/2015
 * Time: 5:21 PM
 */ ?>
<?php use_javascript('jquery.validate.min.js') ?>
<?php use_javascript('jquery.validate.custom.methods.js') ?>
<?php use_stylesheet('bootstrap-datepicker3.min.css') ?>
<?php use_javascript('moment.min.js') ?>
<?php use_javascript('bootstrap-datepicker.min.js') ?>
<?php use_javascript('components-pickers.js') ?>
<!-- BEGIN MODAL EDIT_TEMPLATE -->
<div class="modal fade bs-modal-lg" id="basic-edit-template" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f5f5f5">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" default-title="Add New User">Edit Template</h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body form">
                    <!-- BEGIN FORM-->
                    <form action="<?php echo url_for('ajax_update_template', array('sf_format' => 'json')); ?>" id="edit_template_form" class="form-horizontal" novalidate="novalidate">
                        <div class="form-body">
                            <div class="form-group">
                                <div class="col-xs-12">
                                    <label class="control-label col-xs-2 control-label-template">Template Name
                                        <span class="required" aria-required="true"> * </span>
                                    </label>
                                    <div class="col-xs-10 control-textbox-template">
                                        <input type="text" name="name" tabindex="1" data-required="1" class="required form-control" maxlength="250">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-12">
                                    <label class="control-label col-xs-2 control-label-template">Usage System</label>
                                    <div class="col-xs-10 control-textbox-template" style="cursor: not-allowed !important;">
                                        <select disabled tabindex="2" class="form-control select-picker" name="usage_system">
                                            <?php
                                            if (!empty($usageSystem))
                                                foreach ($usageSystem as $index => $value) {
                                                    echo "<option value='{$index}'>{$value}</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                           <div class="form-group">
                                <div class="col-xs-12">
                                    <label class="control-label col-xs-2 control-label-template">Subject 
                                        <span class="required" aria-required="true"> * </span>
                                    </label>
                                    <div class="col-xs-10 control-textbox-template">
                                        <input name="subject" id="edit_template_modal_subject" type="text" tabindex="3" class="required form-control" maxlength="250">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-12">
                                    <label class="control-label col-xs-2 control-label-template">Content</label>
                                    <div class="col-xs-10 control-textbox-template">
                                        <textarea name="content" class="form-control" tabindex="4" style="resize: none;" rows="8"></textarea>
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
                                        <button type="submit" class="btn green" tabindex="5"><span class="loading "><i class="fa-spin fa fa-refresh"></i>&nbsp;</span>Submit</button>
                                        <button type="button" class="btn default" tabindex="6`" data-dismiss="modal">Cancel</button>
                                        <input id="template_modal_template_id" type="hidden" name="id" />
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
    var validator;
    var errorHolder = $("#edit_template_form").find(".error-holder");
    
    $('#basic-edit-template').on('hide.bs.modal', function() {
        validator.resetForm();//remove error class on name elements and clear history
        validator.reset();//remove all error and success data
        errorHolder.html('');
    });
    
    jQuery(document).ready(function() {
        ComponentsPickers.init();
        jQuery('body').on('edit_template_form', function(evt, id) {
            fill_data_edit_template_form(id);
        });
        
        validator = jQuery("#edit_template_form").validate({
            rules: {
                subject: {
                    minlength: 1,
                    maxlength: 250
                },
                content: {
                    minlength: 1,
//                    maxlength: 2000
                },
                name:   {
                    minlength: 1,
                    maxlength: 250
                }
            },
            onfocusout: function(element) {
                this.element(element);  // <- "eager validation"
            },
            errorPlacement: function(error, element) {
                error.insertAfter(element);
            },
            submitHandler: function(form) {
                ajax_edit_template(form);
            }
        });
    });

    function ajax_edit_template(form) {
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
            success: function(data){
                if (data.error.status) {
                    var error_msg = data.error.msg;
                    errorHolder.html(error_msg);
                    errorHolder.show();
                } else {
                    // prevent a event
                    jQuery('body').trigger('after_update_template', data.data);
                    $('#basic-edit-template').modal('hide');
                }
                loading.hide();
            },
            error: function() {
                loading.hide();
            }
        });
        return false;
    }
    
    function fill_data_edit_template_form(id) {
        var self = jQuery("#edit_template_form");
        jQuery.ajax({
            url: '<?php echo url_for('ajax_get_template', array('sf_format' => 'json')); ?>',
            type: 'GET',
            dataType: 'json',
            data: {id: id},
            success: function(data) {
                self.find('.error-holder').html('');
                if (data.error.status) {
                    my_alert(data.error.msg, 'error');
                } else {
                    self.find('input:not(.keep-data),textarea:not(.keep-data)').each(function() {
                        var my_self = jQuery(this);
                        var name = my_self.attr('name');
                        var value = data.data[name];
                        my_self.val(value);
                    }); 
                    self.find('select:not(.keep-data)').each(function() {
                        var my_self = jQuery(this);
                        var name = my_self.attr('name');
                        var value = data.data[name];
                        my_self.val(value ? value : '');
                        my_self.selectpicker('refresh');
                    });
                    
                    $('#basic-edit-template').modal('show');
                }
            },
            error: function() {
            }
        });
    }
</script>
