<?php use_stylesheet('bootstrap-switch.min.css') ?>
<?php use_stylesheet('daterangepicker-bs3.css') ?>
<?php use_stylesheet('tasks.css') ?>
<?php use_stylesheet('plugins.css') ?>
<?php use_stylesheet('layout.css') ?>
<?php use_stylesheet('custom.css') ?>
<?php // use_stylesheet('jquery.dataTables_themeroller.css') ?>
<?php use_stylesheet('main_page.css') ?>
<?php use_stylesheet('table-row-hover.css') ?>

<?php use_javascript('jquery.flot.min.js') ?>
<?php use_javascript('jquery.flot.resize.min.js') ?>
<?php use_javascript('jquery.flot.categories.min.js') ?>
<?php use_javascript('jquery.uniform.min.js') ?>
<?php use_javascript('jquery.pulsate.min.js') ?>
<?php use_javascript('metronic.js') ?>
<?php use_javascript('layout.js') ?>
<?php use_javascript('quick-sidebar.js') ?>
<?php use_javascript('jquery.easypiechart.min.js') ?>
<?php use_javascript('tasks.js') ?>
<?php use_javascript('bootstrap-select.min.js') ?>
<?php use_javascript('lib/datatables/js/jquery.dataTables.min.js') ?>
<?php use_javascript('lib/datatables/js/dataTables.bootstrap.js') ?>

<div class="row">
    <div id="" class="col-xs-12 wrapper-panel">
        <h3 class="page-title">
            Provision Device Linkup 
        </h3>
        <div id="left-component" class="dv-container-left minimize-margin">
            <div class="form-group select-column">
                <div style="padding:0" class="btnSend-wrapper">
                    <a href="#basic" data-toggle="modal">
                        <button onclick="reset_add_new_user_form();" class="btn green" type="button">Add New User</button>
                    </a>
                </div>
            </div>

            <table id="devices-table" class="table table-striped table-bordered display" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th class="text-center no-sort">
                            <div id="uniform-cbmaster" class="checker">
                                <span><input type="checkbox" id="cbmaster"></span>
                            </div>
                        </th>
                        <th class="text-center">Status</th>
                        <th>Username</th>
                        <th>First name</th>
                        <th>Last name</th>
                        <th class="">Email</th>
                        <th class="">Phone number</th>
                        <th class="">DOB</th>
                        <th class="text-center no-sort" width="15%">Device OS</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>

        <div class="padder btnSend-wrapper">
            <button id="send" name="submit" class="btn blue disabled" type="submit">
                <span class="loading "><i class="fa-spin fa fa-refresh"></i></span>Send
            </button>
        </div>
    </div>
</div>

<?php $ajaxUrl = url_for('ajax_user_list', array('sf_format' => 'json')) ?>
<?php include_partial("global/add_new_user_modal", array('roles' => $roles, "userTooltip" => $userTooltip)); ?>

<script>
    var user = {};
    var UserDataTable;
    //load Platform from config file.
    var platform = '<select disabled class="form-control select-picker" name="select">';
        <?php foreach ($platformNames as $key => $value){ 
            if($key > 3){
                break;
            }
            ?>
            
            platform += '<?php echo "<option value=".$key.">".$value."</option>" ?>';
        <?php } ?>
    platform += '</select>';
    
    jQuery(document).ready(function() {
        
        // Reload table after add new user
        jQuery('body').on('after_add_new_user', function(evt, data){
            UserDataTable.ajax.reload();
        });
        
        // Remove code for select box to show/hide column

        UserDataTable = $('#devices-table').DataTable({
            "processing": true,
            "serverSide": true,
            clearSearch: true,
            "ajax": '<?php echo $ajaxUrl; ?>',
            "order": [[2, "asc"]],
            "createdRow": function ( row, data, index ) {
                $(row).attr('data-userId', data[0]);
            },
            "columnDefs": [{
                    targets: [1, -2, 6],
                    visible: false
                },
                {
                    targets: 0,
                    class: "text-center text-nowrap none-click",
                    "data": null,
                    "orderable": false,
                    "defaultContent": '<div id="uniform-cbmaster" class="checker"><span><input class="" type="checkbox" value=""></span></div>'
                },
                {
                    targets: -1,
                    class: "text-center none-click",
                    "data": null,
                    "defaultContent": platform
                },
                {
                    targets: [ 8 ],
                    orderable: false
                }
            ],
            "fnDrawCallback": function() {
                fill_cb(UserDataTable);
                $("#devices-table tbody tr td input").click(function() {
                    row = $(this).parents('tr');
                    cbClick(UserDataTable, row);
                });
                $("#devices-table tbody tr td:not(.none-click)").click(function() {
                    row = $(this).parent();
                    // Simulate click checkbox
                    cb = (row).find("input");
                    c = cb.prop('checked');
                    cb.prop('checked', !c);
                    cbClick(UserDataTable, row);
                });
                $("#devices-table tbody tr td select").change(function() {
                    row = $(this).parent().parent();
                    userId = UserDataTable.row(row).data()[0];
                    vl = $(this).val();
                    user[userId] = vl;
                });
                $('.select-picker').selectpicker();
            }
        });
        
        jQuery("#user_id").change(function() {
            applyUserInfo();
        });
        jQuery('body').on('after_add_new_user', function(evt, data) {
            applyUserInfoAfterAdd(data);
        });
        
        $("#cbmaster").click(function() {
            var span = $(this).parent('span');
            if(span.hasClass('checked')){
                span.removeClass('checked');
                checkall(UserDataTable, true);
            }else{
                span.addClass('checked');
                checkall(UserDataTable, false);
            }
        });

        $("#send").click(function() {
            var self = jQuery(this);
            var loading = self.find('.loading').first();
            if (loading.is(':visible'))
                return false;
            loading.show();
            data = {"data": user};
            $.ajax({
                url: '<?php echo url_for('ajax_enroll_device', array('sf_format' => 'json')); ?>',
                data: data,
                dataType: 'json',
                success: function(data) {
                    if (data.error && data.error.status === 0) {
                        my_alert(data.error.msg);
                    } else {
                        my_alert(data.error.msg,'type-warning');
                    }
                    loading.hide();
                },
                error: function() {
                    loading.hide();
                }
            });
            return false;
        });
        applyUserInfo();
        $.ytLoad();
    });

    // Fill checkbox when table change
    function fill_cb(table) {
        var rows = $("#devices-table").children('tbody').children('tr');
        // If no record, one row show "No data"
        if (rows.find(".dataTables_empty").length > 0 ) {
            $("#cbmaster").prop('checked', false);
            $("#cbmaster").parent('span').removeClass('checked');
            return;
        }
        var total_active = 0;
        $(rows).each(function() {
            var id_temp = $(this).attr("data-userId");
            for (var index in user) {
                if (index == id_temp) {
                    $(this).find("input").prop('checked', true);
                    $(this).find("input").parent('span').addClass('checked');
                    $(this).find("select").prop('disabled', false);
                    $(this).find("select").val(user[index]);
                    total_active = total_active + 1;
                }
            }
        });
//        for (var i = 0; i < rows.length; i++) {
//            var id_temp = table.row(i).data()[0];
//            for (var index in user) {
//                if (index == id_temp) {
//                    $(rows[i]).find("input").prop('checked', true);
//                    $(rows[i]).find("input").parent('span').addClass('checked');
//                    $(rows[i]).find("select").prop('disabled', false);
//                    $(rows[i]).find("select").val(user[index]);
//                    total_active = total_active + 1;
//                }
//            }
//        }
        if (total_active == rows.length) {
            $("#cbmaster").prop('checked', true);
            $("#cbmaster").parent('span').addClass('checked');
//            checkall(table, false);
        } else {
            $("#cbmaster").prop('checked', false);
            $("#cbmaster").parent('span').removeClass('checked');
//            checkall(table, true);
        }
    }

    // Handle click checkbox
    function cbClick(table, row) {
        id = $(row).attr("data-userId");
        cb = $(row).find("input");
        sl = $(row).find("select");
        var index = -1;
        for (var key in user) {
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
    // Uncheck row
    function cb_uncheck(cb, sl, id) {
        $("#cbmaster").prop('checked', false);
        var span = $("#cbmaster").parent();
        span.removeClass("checked");
        $(cb).prop('checked', false);
        $(sl).prop('disabled', true);
//        $(sl).parent('td').children('div').children('button').addClass('disabled');
//        $(sl).parent('td').children('div').children('div').children('ul').children('li').addClass('disabled');
        $(cb).parent('span').removeClass('checked');
        $(sl).selectpicker("refresh");
        delete user[id];
        enable_button_send();
    }
    // Check row
    function cb_check(cb, sl, id) {
        cb.prop('checked', true);
        sl.prop('disabled', false);
//        $(sl).parent('td').children('div').children('button').removeClass('disabled');
//        $(sl).parent('td').children('div').children('div').children('ul').children('li').removeClass('disabled');
        $(cb).parent('span').addClass('checked');
        $(sl).selectpicker("refresh");
        user[id] = sl.val();
        enable_button_send();
    }
    
    function enable_button_send(){
        if($.isEmptyObject(user)){
            $("#send").addClass('disabled');
        }else{
            $("#send").removeClass('disabled');
        }
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

    // Check all box
    function checkall(table, checked) {
        var rows = $("#devices-table").children('tbody').children('tr');
        if (rows.find(".dataTables_empty").length > 0 ) {
            return;
        }
        $(rows).each(function() {
            var id = $(this).attr("data-userId");
            cb = $(this).find("input");
            sl = $(this).find("select");
            if (checked) {
                cb_uncheck(cb, sl, id);
            } else {
                cb_check(cb, sl, id);
            }
        });
    }

    function applyUserInfoAfterAdd(data) {
        var my_option = jQuery("<option selected='selected' value='' data=''></option>");
        my_option.attr('value', data.id);
        my_option.text(data.email);
        var obj = {u: data.username, n: data.fullname, p: data.phoneNumber, b: data.birthday};
        my_option.attr('data', JSON.stringify(obj));
        var mainSelect = jQuery("#user_id");
        mainSelect.append(my_option);
        mainSelect.val(data.id);
        mainSelect.selectpicker('refresh');
        applyUserInfo();
    }

    // Apply user's data selected
    function applyUserInfo() {
        var data = jQuery("#user_id").find('option:selected').first().attr("data");
        if (data = jQuery.parseJSON(data)) {
            jQuery("#user_name").val(data.u);
            jQuery("#fullname").val(data.n);
            jQuery("#birthday").val(data.b);
            jQuery("#phonenumber").val(data.p);
        }
    }
    
    // Load function from add_new_modal: clear data, auto focus
    function reset_add_new_user_form(){
        jQuery('body').trigger('reset_add_new_user_form');
    }
</script>