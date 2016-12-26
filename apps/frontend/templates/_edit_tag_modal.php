<?php use_stylesheet('bootstrap-datepicker3.min.css') ?>
<?php use_stylesheet('daterangepicker-bs3.css') ?>

<?php use_javascript('bootstrap-datepicker.min.js') ?>
<?php use_javascript('components-pickers.js') ?>
<?php use_javascript('jquery.validate.min.js') ?>
<?php use_javascript('jquery.validate.custom.methods.js') ?>

<div class="modal fade" id="edit_tag_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width: 500px">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Edit tags</h4>
            </div>

            <!--Modal body-->

            <div class="modal-body">
                <form class="form-horizontal" id="edit_tag_modal_form">
                    <div class="form-group">
                        <label for="edit_tag_modal_user" class="col-sm-3 control-label">User</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control keep-data" id="edit_tag_modal_user" disabled=""
                                   value="<?php echo $device->getOwnerEmail(); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_tag_modal_organization" class="col-sm-3 control-label">Organization</label>
                        <div class="col-sm-9">
                            <select class="form-control select-picker" id="edit_tag_modal_organization" name="organization">
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_tag_modal_location" class="col-sm-3 control-label">Location</label>
                        <div class="col-sm-9">
                            <select class="form-control select-picker" id="edit_tag_modal_location" name="location">
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_tag_modal_purchase" class="col-sm-3 control-label">Purchase date</label>
                        <div class="col-sm-9">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control date" readonly="" id="edit_tag_modal_purchase"
                                       name="purchase_date">
                                <span class="input-group-btn">
                                    <button id="datepickerStart" class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_tag_modal_warranty" class="col-sm-3 control-label">Warranty end</label>
                        <div class="col-sm-9"> 
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control date" readonly="" id="edit_tag_modal_warranty"
                                       name="warranty_end">
                                <span class="input-group-btn">
                                    <button id="datepickerEnd" class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="error-holder alert-danger">
                        <span></span>
                    </div>

                    <div class="form-actions">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="text-center">
                                    <button type="submit" class="btn green"><span class="loading "><i class="fa-spin fa fa-refresh"></i>&nbsp;</span>Submit</button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    <input id="edit_tag_id" name="id" type="hidden" />
                                </div>
                            </div>
                        </div>
                </form>
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
    });
</script>

<script type="text/javascript">
    function submit_edit_tag(form) {
        console.log("Submit");
        var self = $(form);
        var data = self.serialize();
        console.log(data);
        var loading = self.find('.loading').first();
        if (loading.is(':visible'))       return false;
        loading.show();
        $.ajax({
            url: '<?php echo url_for('ajax_edit_tag', array('sf_format' => 'json')); ?>',
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(data, textStatus, jqXHR) {
                if (data.error.status)  {
                    $('#edit_tag_modal').modal('hide');
                    my_alert(data.error.msg);
                } else {
                    $('#device_organization').html(data.data.organization);
                    $('#device_location').html(data.data.location);
                    if (data.data.purchase_date)    {
                        var dataPurchaseDate = data.data.purchase_date.split(" ",1);
                        $('#device_purchase').html(dataPurchaseDate);
                    } else {
                        $('#device_purchase').html('-');
                    }
                    if (data.data.warranty_end) {
                        var dataWarrantyEnd = data.data.warranty_end.split(" ",1);
                        $('#device_warranty').html(dataWarrantyEnd);
                    } else {
                        $('#device_warranty').html('-');
                    }
                    drawWarrantyData();
                    loading.hide();
                    $('#edit_tag_modal').modal('hide');
                }
            },
            error: function() {
                loading.hide();
            }
        });
    }
    
    function drawWarrantyData(){
        var deviceId = <?php echo $device->getId(); ?>;
        $.ajax({
            url: '<?php echo url_for('ajax_get_warranty', array('sf_format' => 'json')); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                id:deviceId
            },
            success: function(jdata) {
                var purchaseDate = jdata.data['PurchaseDate'].split(" ",1);
                var warrantyEnd = jdata.data['WarrantyEnd'].split(" ",1);
                
                $(".warranty-starts").text(purchaseDate);
                $(".days-left").text(jdata.data['daysLeft']);
                
                $(".warranty-ends").text(warrantyEnd);
                $(".warranty-status").text(jdata.data['WarrantyStatus']);
                $("#storage-warranty").attr("data",jdata.data['percentWarranty']);
                flagPlot = true;
            }
        });
    }

    $(document).ready(function() {
        // Retrieve event in showSuccess
        $('body').on('load_data_to_modal', function(evt) {
            load_data_to_modal();
        });
        
        // Set begin and end date when change
        $('#datepickerStart, #datepickerEnd').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            clearBtn: true
        });
        $("#datepickerStart").on("changeDate", function(event) {
            $("#edit_tag_modal_purchase").val(
                    $("#datepickerStart").datepicker('getFormattedDate')
                    );
            var purchased = new Date($("#edit_tag_modal_purchase").val());
            var max = Math.max($.now(), purchased.getTime());
            var end = get_date_format(new Date(max));
            $("#datepickerEnd").datepicker('setStartDate', end);
        });
        $("#datepickerEnd").on("changeDate", function(event) {
            $("#edit_tag_modal_warranty").val(
                    $("#datepickerEnd").datepicker('getFormattedDate')
                    );
            $("#datepickerStart").datepicker('setEndDate', $("#datepickerEnd").datepicker('getFormattedDate'));
        });
        
        $("#edit_tag_modal_form").validate({
            rules: {
            },
            submitHandler: function(form) {
                submit_edit_tag(form);
            }
        });
    });
    
    function load_data_to_modal()   {
        var form = $("#edit_tag_modal_form");
        $.ajax({
            url: '<?php echo url_for("ajax_get_tag_info", array('sf_format' => 'json')); ?>',
            data: {"id": "<?php echo $device->getId(); ?>"},
            dataType: "json",
            success: function(data) {
                console.log(data);
                // Set input value by returned date, include id field
                form.find('input:not(.date):not(.keep-data)').each(function() {
                    var my_self = $(this);
                    var name = my_self.attr("name");
                    my_self.val(data.data[name]);
                });
                // Set select box by returned data
                form.find('select').each(function() {
                   var my_self = $(this);
                   var name = my_self.attr("name");
                   remove_option(my_self);
                   add_option(my_self, data.orgLocal[name]);
                   my_self.val(data.data[name]);
                   my_self.selectpicker("refresh");
                });
                // Set date field by returned data
                form.find('input.date').each(function() {
                    var my_self = $(this);
                    var name = my_self.attr("name");
                    if (data.data[name])    {
                        var dataDate = data.data[name].split(" ",1);
                        my_self.val(dataDate);
                    } else {
                        my_self.val("");
                    }
                });
                // Set StartDate and EndDate if exist
                $("#datepickerStart").datepicker('setEndDate', $("#edit_tag_modal_warranty").val());
                var purchased = new Date($("#edit_tag_modal_purchase").val());
                var max = Math.max($.now(), purchased.getTime());
                var end = get_date_format(new Date(max));
                $("#datepickerEnd").datepicker('setStartDate', end);
                
                $("#edit_tag_modal").modal("show");
            },
            error: function()   {
                my_alert("<?php echo $error['E7001'] ?>");
            }
        });
    }

    $("#edit_tag_modal_organization").change(function() {
        console.log($(this).val());
        var org = $(this).val();
        $.ajax({
            url: '<?php echo url_for('ajax_get_location_by_organization', array('sf_format' => 'json')); ?>',
            data: {'organization': org},
            dataType: 'json',
            success: function(data) {
                remove_option($("#edit_tag_modal_location"));
                add_option($('#edit_tag_modal_location'), data);
                $("#edit_tag_modal_location").selectpicker('refresh');
            },
            error: function() {
                my_alert("<?php echo $error['E7001'] ?>");
            }
        });
    });

    function remove_option(select) {
        select.find("option").each(function() {
            $(this).remove();
        });
    }
    
    function add_option(select, data) {
        $.each(data, function(key, vl) {
            select.append($('<option>', {
                value: vl,
                text: vl
            }));
        });
    }
    
    function get_date_format(time)  {
        var dd = time.getDate();
        var mm = time.getMonth() + 1; //January is 0!
        var yyyy = time.getFullYear();
        if (dd < 10)    {
            dd = '0' + dd;
        } 
        if (mm < 10)    {
            mm = '0' + mm;
        }
        return yyyy + "-" + mm + "-" + dd;
    }
</script>