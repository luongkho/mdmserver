<?php use_stylesheet('table-row-hover') ?>
<?php use_javascript('jquery.validate.min.js') ?>
<?php use_javascript('jquery.validate.custom.methods.js') ?>
<?php use_stylesheet('bootstrap-datepicker3.min.css') ?>
<?php use_javascript('moment.min.js') ?>
<?php use_javascript('bootstrap-datepicker.min.js') ?>
<?php use_javascript('components-pickers.js') ?>

<div class="modal fade bs-modal-lg" id="edit_profile_modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f5f5f5">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" default-title="Add New User">Edit Profile</h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body form">
                    <!-- BEGIN FORM-->
                    <form id="edit_profile_form" class="form-horizontal" novalidate="novalidate">
                        <div class="form-body">
                            
                            <!--Platform and Type-->
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-5" style="padding-right:0">Platform</label>
                                    <div class="col-xs-6">
                                        <select disabled="" id="platform_edit" name="platform" class="form-control select-picker disabled">
                                            <?php
                                            if (!empty($platformNames)) {
                                                foreach ($platformNames as $index => $value) {
                                                    echo "<option value='{$index}'>{$value}</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xs-6 offset-6">
                                    <label class="control-label col-xs-5" style="padding-right:0">Configuration Type</label>
                                    <div class="col-xs-6">
                                        <select disabled="" class="form-control select-picker" name="configuration_type" id="type_edit">
                                            <?php
                                            if (!empty($configTypes))   {
                                                foreach ($configTypes as $index => $value) {
                                                    echo "<option value='{$index}'>{$value}</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!--Name and description-->
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-5" style="padding-right:0" for="name_edit">Name</label>
                                    <div class="col-xs-6">
                                        <input name="profile_name" id="name_edit" type="text" class="form-control" maxlength="250">
                                    </div>
                                </div>
                                <div class="col-xs-6 offset-6">
                                    <label class="control-label col-xs-5" style="padding-right:0" for="desc_edit">Description</label>
                                    <div class="col-xs-6">
                                        <input name="description" id="desc_edit" type="text" class="form-control" maxlength="250">
                                    </div>
                                </div>
                            </div>

                            <!--Location-->
                            <div id="Location_content_edit" class="content">
                                <div class="form-group" >
                                    <div class="col-xs-6">
                                        <label for="distance_android_edit" class="control-label col-xs-5" style="padding-right:0">Distance (m)<span class="required" aria-required="true">
                                                * </span>
                                        </label>
                                        <div class="col-xs-6">
                                            <input name="distance" id="distance_android_edit" type="text" class="form-control" maxlength="10">
                                        </div>
                                    </div>
                                    <div class="col-xs-6">
                                        <label for="interval_android_edit" class="control-label col-xs-5" style="padding-right:0">Interval (s)<span class="required" aria-required="true">
                                                * </span>
                                        </label>
                                        <div class="col-xs-6">
                                            <input id="interval_android_edit" name="interval" type="text" class="form-control" maxlength="10">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-xs-6 help-message">
                                        <?php foreach ($locationWarning as $value) {
                                            echo $value . "<br>";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!--Pass code-->
                            <div id="iOS Passcode_content_edit" class="content">
                                <div class="form-group">
                                    <div class="col-xs-6">
                                        <label class="control-label col-xs-5" style="padding-right:0">Require alphanumeric
                                        </label>
                                        <div class="col-xs-6">
                                            <div class="checker-wrapper">
                                                <input type="checkbox" name="pc_alphanum" id="pc_alphanum_edit" class="checkbox">
                                            </div>
                                        </div>
                                        <a href="javascript:;" onclick="show_tooltip_passcode(this);
                                            return false;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" 
                                            title="<?php echo $profilePasscodeTooltip['pc_alphanum']['title'] . "\n  " . $profilePasscodeTooltip['pc_alphanum']['content']; ?>"
                                            data-header="<?php echo $profilePasscodeTooltip['pc_alphanum']['title']; ?>"
                                            data-content="<?php echo $profilePasscodeTooltip['pc_alphanum']['content']; ?>">
                                        </a>
                                    </div>
                                    <div class="col-xs-6">
                                        <label class="control-label col-xs-5" style="padding-right:0">Auto-Lock (mins)
                                        </label>
                                        <div class="col-xs-6">
                                            <select class="form-control select-picker" name="pc_auto_lock" id="pc_auto_lock_edit">
                                                <?php
                                                $maxInactivity = $iOSpasscodeSetting['maxInactivity'];
                                                foreach ($maxInactivity as $key => $value) {
                                                    echo "<option value='{$key}'>{$value}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <a href="javascript:;" onclick="show_tooltip_passcode(this);
                                            return false;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" 
                                            title="<?php echo $profilePasscodeTooltip['pc_auto_lock']['title'] . "\n  " . $profilePasscodeTooltip['pc_auto_lock']['content']; ?>"
                                            data-header="<?php echo $profilePasscodeTooltip['pc_auto_lock']['title']; ?>"
                                            data-content="<?php echo $profilePasscodeTooltip['pc_auto_lock']['content']; ?>">
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="col-xs-6">
                                        <label class="control-label col-xs-5" style="padding-right:0">Minimum length
                                        </label>
                                        <div class="col-xs-6">
                                            <select class="form-control select-picker" name="pc_min_length" id="pc_min_length_edit">
                                                <?php
                                                $minLength = $iOSpasscodeSetting['minLength'];
                                                echo "<option value='-1'>{$minLength[-1]}</option>";
                                                for ($i = $minLength["min"]; $i <= $minLength["max"]; $i++) {
                                                    echo "<option value='{$i}'>{$i}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <a href="javascript:;" onclick="show_tooltip_passcode(this);
                                            return false;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" 
                                            title="<?php echo $profilePasscodeTooltip['pc_min_length']['title'] . "\n  " . $profilePasscodeTooltip['pc_min_length']['content']; ?>"
                                            data-header="<?php echo $profilePasscodeTooltip['pc_min_length']['title']; ?>"
                                            data-content="<?php echo $profilePasscodeTooltip['pc_min_length']['content']; ?>">
                                        </a>
                                    </div>
                                    <div class="col-xs-6">
                                        <label class="control-label col-xs-5" style="padding-right:0">Passcode history
                                        </label>
                                        <div class="col-xs-6">
                                            <select class="form-control select-picker" name="pc_history" id="pc_history_edit">
                                                <?php
                                                $pinHistory = $iOSpasscodeSetting['pinHistory'];
                                                echo "<option value='-1'>{$minLength[-1]}</option>";
                                                for ($i = $pinHistory["min"]; $i <= $pinHistory["max"]; $i++) {
                                                    echo "<option value='{$i}'>{$i}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <a href="javascript:;" onclick="show_tooltip_passcode(this);
                                            return false;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" 
                                            title="<?php echo $profilePasscodeTooltip['pc_history']['title'] . "\n  " . $profilePasscodeTooltip['pc_history']['content']; ?>"
                                            data-header="<?php echo $profilePasscodeTooltip['pc_history']['title']; ?>"
                                            data-content="<?php echo $profilePasscodeTooltip['pc_history']['content']; ?>">
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="col-xs-6">
                                        <label class="control-label col-xs-5" style="padding-right:0">Complex characters
                                        </label>
                                        <div class="col-xs-6">
                                            <select class="form-control select-picker" name="pc_min_complex_char" id="pc_min_complex_char_edit">
                                                <?php
                                                $minComplexChars = $iOSpasscodeSetting['minComplexChars'];
                                                foreach ($minComplexChars as $key => $value) {
                                                    echo "<option value='{$key}'>{$value}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <a href="javascript:;" onclick="show_tooltip_passcode(this);
                                            return false;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" 
                                            title="<?php echo $profilePasscodeTooltip['pc_min_complex_char']['title'] . "\n  " . $profilePasscodeTooltip['pc_min_complex_char']['content']; ?>"
                                            data-header="<?php echo $profilePasscodeTooltip['pc_min_complex_char']['title']; ?>"
                                            data-content="<?php echo $profilePasscodeTooltip['pc_min_complex_char']['content']; ?>">
                                        </a>
                                    </div>
                                    <div class="col-xs-6">
                                        <label class="control-label col-xs-5" style="padding-right:0">Grace period</label>
                                        <div class="col-xs-6">
                                            <select class="form-control select-picker" name="pc_period_device_lock" id="pc_period_device_lock_edit">
                                                <?php
                                                $maxGracePeriod = $iOSpasscodeSetting['maxGracePeriod'];
                                                foreach ($maxGracePeriod as $key => $value) {
                                                    echo "<option value='{$key}'>{$value}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <a href="javascript:;" onclick="show_tooltip_passcode(this);
                                            return false;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" 
                                            title="<?php echo $profilePasscodeTooltip['pc_period_device_lock']['title'] . "\n  " . $profilePasscodeTooltip['pc_period_device_lock']['content']; ?>"
                                            data-header="<?php echo $profilePasscodeTooltip['pc_period_device_lock']['title']; ?>"
                                            data-content="<?php echo $profilePasscodeTooltip['pc_period_device_lock']['content']; ?>">
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="col-xs-6">
                                        <label class="control-label col-xs-5" style="padding-right:0">Passcode age (days)
                                        </label>
                                        <div class="col-xs-6">
                                            <input type="text" class="form-control" maxlength="3" name="pc_age" id="pc_age_edit">
                                        </div>
                                        <a href="javascript:;" onclick="show_tooltip_passcode(this);
                                            return false;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" 
                                            title="<?php echo $profilePasscodeTooltip['pc_age']['title'] . "\n  " . $profilePasscodeTooltip['pc_age']['content']; ?>"
                                            data-header="<?php echo $profilePasscodeTooltip['pc_age']['title']; ?>"
                                            data-content="<?php echo $profilePasscodeTooltip['pc_age']['content']; ?>">
                                        </a>
                                    </div>
                                    <div class="col-xs-6">
                                        <label class="control-label col-xs-5" style="padding-right:0">Failed attempt times</label>
                                        <div class="col-xs-6">
                                            <select class="form-control select-picker" name="pc_num_failed" id="pc_num_failed_edit">
                                                <?php
                                                $maxFailedAttempts = $iOSpasscodeSetting['maxFailedAttempts'];
                                                echo "<option value='-1'>{$maxFailedAttempts[-1]}</option>";
                                                for ($i = $maxFailedAttempts["min"]; $i <= $maxFailedAttempts["max"]; $i++) {
                                                    echo "<option value='{$i}'>{$i}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <a href="javascript:;" onclick="show_tooltip_passcode(this);
                                            return false;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" 
                                            title="<?php echo $profilePasscodeTooltip['pc_num_failed']['title'] . "\n  " . $profilePasscodeTooltip['pc_num_failed']['content']; ?>"
                                            data-header="<?php echo $profilePasscodeTooltip['pc_num_failed']['title']; ?>"
                                            data-content="<?php echo $profilePasscodeTooltip['pc_num_failed']['content']; ?>">
                                        </a>
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
                                        <input type="hidden" name="id" />
                                        <input type="hidden" name="platform" />
                                        <input type="hidden" name="configuration_type" />
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
    var edit_validator;
    var edit_errorHolder = $("#edit_profile_form").find(".error-holder");
    
    $(document).ready(function() {
        ComponentsPickers.init();
        
        $('body').on('show_edit_profile_modal', function(evt, id) {
            console.log(id);
            jQuery.ajax({
                url: '<?php echo url_for('ajax_get_profile', array('sf_format' => 'json')); ?>',
                type: 'GET',
                dataType: 'json',
                data: {id: id},
                success: function(data) {
                    console.log(data);
                    var form = jQuery("#edit_profile_form");
                    form.find('input:not(input[type=checkbox]), select').each(function() {
                        var my_self = $(this);
                        var name = my_self.attr("name");
                        my_self.val(data.data[name]);
                    });
                    form.find('input.checkbox').each(function() {
                        var my_self = $(this);
                        var name = my_self.attr("name");
                        if (data.data[name])    {
//                            my_self.parents("span").addClass("checked");
                            my_self.prop("checked", true);
                        } else {
//                            my_self.parents("span").removeClass("checked");
                            my_self.prop("checked", false);
                        }
                        $.uniform.update(my_self);
                    });
                    $('#edit_profile_modal .select-picker').selectpicker('refresh');
                    
                    // add_profile_modal
                    show_content(platform_config[data.data.platform][data.data.configuration_type], "edit");
                    $("#edit_profile_modal").modal('show');
                }
            });

            edit_validator = jQuery("#edit_profile_form").validate({
                rules: {
                    pc_age:   {
                        positiveIntNoSpace: true,
                        min: 1,
                        max: 730
                    },
                    distance: {
                        required: true,
                        positiveIntNoSpace: true,
                        min: 1
                    },
                    interval: {
                        required: true,
                        positiveIntNoSpace: true,
                        min: 1
                    }
                },
                onfocusout: function(element) {
                    this.element(element);  // <- "eager validation"
                },
                submitHandler: function(form) {
                    ajax_add_profile(form);
                }
            });
        });
    });

    $('#edit_profile_modal').on('hide.bs.modal', function() {
        edit_validator.resetForm();//remove error class on name elements and clear history
        edit_validator.reset();//remove all error and success data
        edit_errorHolder.html('');
    });
</script>
