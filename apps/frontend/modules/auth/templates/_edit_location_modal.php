<?php use_javascript('jquery.validate.min.js') ?>
<?php use_javascript('jquery.validate.custom.methods.js') ?>
<?php use_javascript('components-pickers.js') ?>

<div class="modal fade" id="edit_location_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f5f5f5">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Edit location</h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body form">
                    <!-- BEGIN FORM-->
                    <form action="" id="edit_location_form" class="form-horizontal">
                        <div class="form-body">
                            <div class="form-group">
                                <div>
                                    <label for="edit_organization" class="control-label col-xs-3" style="padding-right:0">Organization<span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-9">
                                        <input type="text" name="organization" tabindex="1" class="required form-control" id="edit_organization" maxlength="250">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div>
                                    <label for="edit_location" class="control-label col-xs-3"style="padding-right:0">Location<span class="required" aria-required="true">
                                            * </span>
                                    </label>
                                    <div class="col-xs-9">
                                        <input name="location" type="text" tabindex="2" class="required form-control" id="edit_location" maxlength="250">
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
                                        <input name="id" type="hidden"/>
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
    var edit_validator;
    var edit_errorHolder = $("#edit_location_form").find(".error-holder");
    
    $('body').on('show_edit_location_modal', function(evt, id) {
        jQuery.ajax({
            url: '<?php echo url_for('ajax_get_location_info', array('sf_format' => 'json')); ?>',
            type: 'POST',
            dataType: 'json',
            data: {id: id},
            success: function(data) {
                if (data.error.status) {
                    my_alert(data.error.msg, 'error');
                } else {
                    $("#edit_location_form").find("input").each(function()  {
                        var name = $(this).attr("name");
                        $(this).val(data.data[name]);
                    });
                    $("#edit_location_modal").modal("show");
                }
            },
            error: function() {
                my_alert("<?php echo $error['E7001'] ?>");
            }
        });
    });
    
    $(document).ready(function()    {
         edit_validator = $("#edit_location_form").validate({
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
    
    $('#edit_location_modal').on('hide.bs.modal', function() {
        edit_validator.resetForm();//remove error class on name elements and clear history
        edit_validator.reset();//remove all error and success data
        edit_errorHolder.html('');
    });

</script>