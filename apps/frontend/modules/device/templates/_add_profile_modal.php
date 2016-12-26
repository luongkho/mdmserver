<?php use_stylesheet('table-row-hover') ?>
<?php use_javascript('jquery.validate.min.js') ?>
<?php use_javascript('jquery.validate.custom.methods.js') ?>
<?php use_stylesheet('bootstrap-datepicker3.min.css') ?>
<?php use_javascript('moment.min.js') ?>
<?php use_javascript('bootstrap-datepicker.min.js') ?>
<?php use_javascript('components-pickers.js') ?>

<div class="modal fade bs-modal-lg" id="add_profile_modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f5f5f5">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" default-title="Add New User">Add New Profile</h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body form">
                    <!-- BEGIN FORM-->
                    <form id="add_new_profile" class="form-horizontal" novalidate="novalidate">
                        <div class="form-body">
                            
                            <!--Platform and Type-->
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-5" style="padding-right:0">Platform</label>
                                    <div class="col-xs-6">
                                        <select id="platform_new" name="platform" class="form-control select-picker">
                                            <?php
                                            if (!empty($platformNames)) {
                                                foreach ($platformNames as $index => $value) {
                                                    if($index > 3){
                                                        continue;
                                                    }
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
                                        <select class="form-control select-picker" name="configuration_type" id="type_new">
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!--Name and Description-->
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <label class="control-label col-xs-5" style="padding-right:0" for="name_modal">Name</label>
                                    <div class="col-xs-6">
                                        <input name="profile_name" id="name_modal" type="text" class="form-control" maxlength="250">
                                    </div>
                                </div>
                                <div class="col-xs-6 offset-6">
                                    <label class="control-label col-xs-5" style="padding-right:0" for="desc_modal">Description</label>
                                    <div class="col-xs-6">
                                        <input name="description" id="desc_modal" type="text" class="form-control" maxlength="250">
                                    </div>
                                </div>
                            </div>
                            
                             <!--Location-->
                             <div id="Location_content_new" class="content">
                                <div class="form-group" >
                                    <div class="col-xs-6">
                                        <label for="distance_android_modal" class="control-label col-xs-5" style="padding-right:0">Distance (m)<span class="required" aria-required="true">
                                                * </span>
                                        </label>
                                        <div class="col-xs-6">
                                            <input name="distance" id="distance_android_modal" type="text" class="form-control" maxlength="10">
                                        </div>
                                    </div>
                                    <div class="col-xs-6">
                                        <label for="interval_android_modal" class="control-label col-xs-5" style="padding-right:0">Interval (s)<span class="required" aria-required="true">
                                                * </span>
                                        </label>
                                        <div class="col-xs-6">
                                            <input id="interval_android_modal" name="interval" type="text" class="form-control" maxlength="10">
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
                            <div id="iOS Passcode_content_new" class="content">
                                <div class="form-group">
                                    <div class="col-xs-6">
                                        <label class="control-label col-xs-5" style="padding-right:0" for="pc_alphanum_new">Require alphanumeric
                                        </label>
                                        <div class="col-xs-6">
                                            <div class="checker-wrapper">
                                                <input class="checkbox" type="checkbox" value="1" name="pc_alphanum" id="pc_alphanum_new">
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
                                            <select class="form-control select-picker" name="pc_auto_lock">
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
                                            <select class="form-control select-picker" name="pc_min_length">
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
                                            <select class="form-control select-picker" name="pc_history">
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
                                            <select class="form-control select-picker" name="pc_min_complex_char">
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
                                        <label class="control-label col-xs-5" style="padding-right:0">Grace period
                                        </label>
                                        <div class="col-xs-6">
                                            <select class="form-control select-picker" name="pc_period_device_lock">
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
                                            <input type="text" class="form-control" maxlength="3" name="pc_age">
                                        </div>
                                        <a href="javascript:;" onclick="show_tooltip_passcode(this);
                                            return false;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" 
                                            title="<?php echo $profilePasscodeTooltip['pc_age']['title'] . "\n  " . $profilePasscodeTooltip['pc_age']['content']; ?>"
                                            data-header="<?php echo $profilePasscodeTooltip['pc_age']['title']; ?>"
                                            data-content="<?php echo $profilePasscodeTooltip['pc_age']['content']; ?>">
                                        </a>
                                    </div>
                                    <div class="col-xs-6">
                                        <label class="control-label col-xs-5" style="padding-right:0">Failed attempt times
                                        </label>
                                        <div class="col-xs-6">
                                            <select class="form-control select-picker" name="pc_num_failed">
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

                            <div class="content" id="iOS Wifi_content_new">
                                <div class="form-group">
                                    Hello
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
                                        <button id="submit_new" type="submit" class="btn green"><span class="loading "><i class="fa-spin fa fa-refresh"></i>&nbsp;</span>Submit</button>
                                        <button type="button" class="btn default" data-dismiss="modal">Cancel</button>
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
    ComponentsPickers.init();
</script>

<script>
    var add_validator;
    var add_errorHolder = $("#add_new_profile").find(".error-holder");
    
    $(document).ready(function() {
        jQuery('body').on('show_add_profile_modal', function(evt) {
            show_add_profile_modal();
        });
        
        add_validator = jQuery("#add_new_profile").validate({
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
    
    function show_add_profile_modal()   {
        var self = jQuery("#add_new_profile");
        self.find('input.checkbox').prop('checked', false);
        self.find('input.checkbox').parents('span').removeClass('checked')
        self.find('input:not(.keep-data)').val('');
        self.find('select:not(.keep-data)').each(function() {
            var sdefault = $(this).find('option:first').val();
            $(this).val(sdefault);
            $(this).selectpicker('refresh');
        });
        
        setTimeout(function() {
            $("#add_profile_modal").find('input[type=text]').first().focus();
        }, 700);
        
        change_platform(Object.keys(platform_config)[0]);
    }

    function ajax_add_profile(form) {
        var self = jQuery(form);
        var loading = self.find('.loading').first();
        if (loading.is(':visible'))         return false;
        loading.show();
        var data = self.serialize();
        if ($(form).find("input[name=pc_alphanum]").prop('checked'))  {
            data = data + "&pc_alphanum=true";
        }
        console.log(data);
        jQuery.ajax({
            url: '<?php echo url_for('ajax_add_new_profile', array('sf_format' => 'json')); ?>',
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(data) {
                if (data.error.status) {
                    my_alert(data.error.msg);
                } else {
//                    jQuery('body').trigger('add_new_profile', data.data);
                }
                if ($('#add_profile_modal').hasClass('in'))   {
                    $('#add_profile_modal').modal('hide');
                }
                if ($('#edit_profile_modal').hasClass('in'))   {
                    $('#edit_profile_modal').modal('hide');
                }
                UserDataTable.ajax.reload();
                loading.hide();
            },
            error: function() {
                loading.hide();
            }
        });
        return false;
    }
    
    $('#add_profile_modal').on('hide.bs.modal', function() {
        add_validator.resetForm();//remove error class on name elements and clear history
        add_validator.reset();//remove all error and success data
        add_errorHolder.html('');
    });
    
    // Change platform
    $("#platform_new").change(function() {
        var platform = $(this).val();
        change_platform(platform);
    });
    
    // Real function change platform
    function change_platform(platform)  {
        remove_type();
        add_type(platform_config[platform]);
        var type = Object.keys(platform_config[platform])[0];
        show_content(platform_config[platform][type], "new");
    }

    // Change type
    $('#type_new').change(function() {
        var platform = $("#platform_new").val();
        var type = $(this).val();
        show_content(platform_config[platform][type], "new");
    });
    
    // Remove type option
    function remove_type() {
        $("#type_new option").each(function() {
            $(this).remove();
        });
        $("#type_new").selectpicker('refresh');
    }

    // Add option to type select
    function add_type(data) {
        $.each(data, function(key, vl) {
            $('#type_new').append($('<option>', {
                value: key,
                text: vl
            }));
        });
        $("#type_new").selectpicker('refresh');
    }
    
    // Show content by profile type
    function show_content(type, func) {
        var temp = type + "_content_" + func;
        var contentId = "[id='" + temp.replace(/([ #;&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1') + "']";
        $(".content").slideUp();
        $(contentId).slideDown();
    }
</script>
