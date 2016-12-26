<?php use_javascript('jquery.validate.min.js') ?>
<?php use_javascript('jquery.validate.custom.methods.js') ?>
<?php use_stylesheet('bootstrap-datepicker3.min.css') ?>
<?php use_javascript('moment.min.js') ?>
<?php use_javascript('bootstrap-datepicker.min.js') ?>
<?php use_javascript('components-pickers.js'); ?>

<style>
    .dataTables_scroll
    {
        max-height: 200px !important;
        overflow-y: auto !important;
        width: 100%;
    }
</style>

<div class="modal fade" id="deploy_profile_modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f5f5f5">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" default-title="Add New User">Select devices to deploy</h4>
            </div>
            <div class="modal-body" style="margin-top: 10px;">
                <div class="portlet-body form">
                    <!-- BEGIN FORM-->
                    <form id="deploy_profile_form" action="" id="add_new_person_form" class="form-horizontal" novalidate="novalidate">
                        <div id="left-component" class="dv-container-left minimize-margin">
                            <button style="margin-bottom: 5px" type="button" onClick="table.ajax.reload();" class="btn btn-default blue col-xs-offset-10">Refresh 
                                <span class="glyphicon glyphicon-refresh"></span>
                            </button>
                            <table id="devices-table" class="table table-striped table-bordered table-responsive" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th class="text-center no-sort" width="5%">
                                            <div id="uniform-cbmaster" class="checker">
                                                <span><input type="checkbox" id="cbmaster"></span>
                                            </div>
                                        </th>
                                        <th class="no-sort"></th>
                                        <th class="text-center online_status">Status</th>
                                        <th width="15%" disabled="disabled">Device name</th>
                                        <th width="15%">Software version</th>
                                        <th width="15%">User</th>
                                        <th>Last reported</th>
                                        <th>IMEI</th>
                                        <th>Wifi Mac Address</th>
                                        <th>Platform</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            
                            <div style="margin: 20px auto">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="text-center">
                                            <button type="submit" class="btn green disabled" id="send"><span class="loading "><i class="fa-spin fa fa-refresh"></i>&nbsp;</span>Submit</button>
                                            <button type="button" class="btn default" data-dismiss="modal">Cancel</button>
                                            <input id="new_user_modal_user_id" type="hidden" name="id" />
                                        </div>
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

<?php $ajaxUrl = url_for('ajax_device_list', array('sf_format' => 'json')) ?>

<script type="text/javascript">
    // Store devices chosen
    var devices = {};
    var object = {};
    var table;
    // Enrolled devices have status 0
    var device_enroll = 0;
    
    Metronic.init(); // init metronic core componets
    Layout.init(); // init layout
    QuickSidebar.init(); // init quick sidebar
    Tasks.initDashboardWidget();


    $('#deploy_profile_modal').on('hide.bs.modal', function() {
        devices = {};
        enable_button_send();
    });
    
    $(document).ready(function() {
        $('body').on('show_deploy_profile_modal', function(evt, data) {
            object = data;
            console.log(data);
            table = $('#devices-table').DataTable({
//                retrieve: true,
                destroy: true,
                "deferRender": true,
                "scrollCollapse": false,
                "clearSearch": false,
                "processing": true,
                "serverSide": true,
                "createdRow": function ( row, data, index ) {
                    $(row).attr('data-deviceId', data[0]);
                    $(row).attr('data-version', data[4]);
                },
                "ajax": {
                    'type': 'POST',
                    'url': '<?php echo $ajaxUrl; ?>',
                    'data': {'platform': data.platform, 'device_status': device_enroll}
                },
                "order": [3, "asc"],
                'columnDefs': [
                    {
                        "targets": [1, 2, 6, 7, 8, -1],
                        "visible": false
                    },
                    {
                        targets: 0,
                        class: "text-center text-nowrap none-click",
                        "data": null,
                        "orderable": false,
                        "defaultContent": '<div id="uniform-cbmaster" class="checker"><span><input type="checkbox" id="cbmaster"></span></div>'
                    }
                ],
                "fnDrawCallback": function() {
                    // Load checkbox selected
                    fill_cb(table);
                    // Click checkbox
                    $("#devices-table tbody tr td input").click(function() {
                        row = $(this).parents('tr');
                        cbClick(table, row);
                    });
                    // Click row
                    $("#devices-table tbody tr td:not(.none-click)").click(function() {
                        row = $(this).parent("tr");
                        // Simulate click checkbox
                        cb = (row).find("input");
                        c = cb.prop('checked');
                        cb.prop('checked', !c);
                        cbClick(table, row);
                    });
                    if($(".dataTables_scroll").length == 0){
                        $('#devices-table').wrap('<div class="dataTables_scroll" />');
                    }
                    
                    $("#deploy_profile_modal").modal('show');
                }
            });
        });
    });
    
    // Click check all
    $("#cbmaster").click(function(){
        var span = $(this).parent('span');
        if(span.hasClass('checked')){
            span.removeClass('checked');
            checkall(table, true);
        }else{
            span.addClass('checked');
            checkall(table, false);
        }
    });
    
    $("#deploy_profile_form").submit(function(e) {
        e.preventDefault();
        var self = jQuery(this);
        var loading = self.find('.loading').first();
        if (loading.is(':visible'))
            return false;
        loading.show();
        data = {"id": devices, "profileId": object.id, "action_type": "install_profile",
            "platform": object.platform, "profileType": object.type,
            'user_id': <?php echo $sf_user->getDecorator()->getUserId() ?>};
        $.ajax({
            url: '<?php echo url_for('ajax_action_device_task', array('sf_format' => 'json')); ?>',
            data: data,
            dataType: 'json',
            success: function(data) {
                loading.hide();
                $("#deploy_profile_modal").modal('hide');
                if (data.error && data.error.status === 1) {
                    if (data.redirect)  {
                        my_alert(data.error.msg, 'type-warning', function() {
                            window.location.href = data.redirect;
                        });
                    } else {
                        my_alert(data.error.msg);
                    }
                }
                else if (data.error && data.error.status === 0) {
                    my_alert(data.msg);
                }
            },
            error: function() {
                loading.hide();
            }
        });
        devices = {};
        return false;
    });

    function fill_cb(table) {
        var rows = $("#devices-table").children('tbody').children('tr');
        // If no record, clear checkbox and return
        if (rows.find(".dataTables_empty").length > 0 ) {
            $("#cbmaster").prop('checked', false);
            $("#cbmaster").parent('span').removeClass('checked');
            return;
        }
        var total_active = 0;
        $(rows).each(function() {
            var id_temp = $(this).attr("data-deviceId");
            for (var index in devices) {
                if (index == id_temp) {
                    $(this).find("input").prop('checked', true);
                    $(this).find("input").parent('span').addClass('checked');
                    total_active = total_active + 1;
                }
            }
        });
        if (total_active == rows.length) {
            $("#cbmaster").prop('checked', true);
            $("#cbmaster").parent('span').addClass('checked');
        } else {
            $("#cbmaster").prop('checked', false);
            $("#cbmaster").parent('span').removeClass('checked');
        }
    }

    // Handle click checkbox
    function cbClick(table, row) {
        id = $(row).attr("data-deviceId");
        cb = $(row).find("input");
        sl = $(row).attr("data-version");
        var index = -1;
        for (var key in devices) {
            if (key == id) {
                index = id;
            }
        }
        // Checkbox change 'checked' value right after click
        if (!cb.prop('checked') && (index > -1)) {
            cb_uncheck(cb, sl, id);
        }
        if (cb.prop('checked') && (index < 0)) {
            cb_check(cb, sl, id);
        }
        cb_child_check_all();
    }
    // Uncheck
    function cb_uncheck(cb, sl, id) {
        $("#cbmaster").prop('checked', false);
        var span = $("#cbmaster").parent();
        span.removeClass("checked");
        $(cb).prop('checked', false);
        $(cb).parent('span').removeClass('checked');
        delete devices[id];
        enable_button_send();
    }
    // Check
    function cb_check(cb, sl, id) {
        cb.prop('checked', true);
        $(cb).parent('span').addClass('checked');
        devices[id] = sl;
        enable_button_send();
    }

    // Check all box
    function checkall(table, checked) {
        var rows = $("#devices-table").children('tbody').children('tr');
        if (rows.find(".dataTables_empty").length > 0 ) {
            return;
        }
        $(rows).each(function() {
            var id = $(this).attr("data-deviceId");
            cb = $(this).find("input");
            sl = $(this).attr("data-version");
            if (checked) {
                cb_uncheck(cb, sl, id);
            } else {
                cb_check(cb, sl, id);
            }
        });
    }
    
    function enable_button_send(){
        if($.isEmptyObject(devices)){
            $("#send").addClass('disabled');
        }else{
            $("#send").removeClass('disabled');
        }
        console.log(devices);
    }
    
    function cb_child_check_all(){
        var checked_all = true;
        $("#devices-table tbody tr").each(function(index){
            var span = $(this).children('td').children('div').children('span');
            if(!span.hasClass('checked')){
                checked_all= false;
                return;
            }
        });
        if(checked_all){
            $("#cbmaster").parent().addClass('checked');
        }
    }
</script>
