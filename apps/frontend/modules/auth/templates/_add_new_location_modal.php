<?php use_javascript('jquery.validate.min.js') ?>
<?php use_javascript('jquery.validate.custom.methods.js') ?>
<?php use_javascript('components-pickers.js') ?>

<div class="modal fade" id="add_new_location_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f5f5f5">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Add new location</h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body form">
                    <!-- BEGIN FORM-->
                    <form action="" id="add_new_location_form" class="form-horizontal">
                        <div class="form-body">
                            <div class="form-group">
                                <div>
                                    <label class="control-label col-xs-3" style="padding-right:0">Organization<span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-9">
                                        <input type="text" name="organization" tabindex="1" class="required form-control" id="organization" data-modalfocus maxlength="250">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div>
                                    <label class="control-label col-xs-3"style="padding-right:0">Location<span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-9">
                                        <input name="location" type="text" tabindex="2" class="required form-control" id="location" maxlength="250">
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
                                        <button type="submit" class="btn green" tabindex="3"><span class="loading "><i class="fa-spin fa fa-refresh"></i>&nbsp;</span>Submit</button>
                                        <button type="button" class="btn default" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!--Modal body-->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core componets
        Layout.init(); // init layout
        QuickSidebar.init(); // init quick sidebar
        Tasks.initDashboardWidget();
        ComponentsPickers.init();
    });
</script>

<script type="text/javascript">
    var add_validator;
    var add_errorHolder = $("#add_new_location_form").find('.error-holder');
    
    function ajax_add_location(form) {
        var self = jQuery(form);
        var loading = self.find('.loading').first();
        if (loading.is(':visible'))
            return false;
        loading.show();
        jQuery.ajax({
            url: '<?php echo url_for('ajax_add_new_location', array('sf_format' => 'json')) ?>',
            type: 'POST',
            dataType: 'json',
            data: self.serialize(),
            success: function(data) {
                if (data.error.status) {
                    self.find('.error-holder').html(data.error.msg);
                    self.find('.error-holder').show();
                } else {
                    if ($('#add_new_location_modal').hasClass('in'))   {
                        $('#add_new_location_modal').modal('hide');
                    }
                    if ($('#edit_location_modal').hasClass('in'))   {
                        $('#edit_location_modal').modal('hide');
                    }
                }
                loading.hide();
                console.log(data);
            },
            error: function() {
                loading.hide();
            }
        });
        return false;
    }
    
    $('#add_new_location_modal').on('hide.bs.modal', function() {
        add_validator.resetForm();//remove error class on name elements and clear history
        add_validator.reset();//remove all error and success data
        add_errorHolder.html('');
    });

    $('#add_new_location_modal').on('show.bs.modal', function() {
        var self = jQuery("#add_new_location_form");
        self.find('input:not(.keep-data)').val('');
//        setTimeout(function() {
//            $("#add_new_location_form").find('input[type=text]').first().focus();
//        }, 700);
    });

    $(document).ready(function() {
        add_validator = $("#add_new_location_form").validate({
            rules: {
                organization: {
                    required: true
                },
                location: {
                    required: true
                }
            },
            onfocusout: function(element) {
                this.element(element);  // <- "eager validation"
            },
            submitHandler: function(form) {
                ajax_add_location(form);
            }
        });
    });

</script>