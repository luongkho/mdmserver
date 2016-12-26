<?php
use_stylesheet('bootstrap-switch.min.css') ?>
<?php use_stylesheet('daterangepicker-bs3.css') ?>
<?php use_stylesheet('tasks.css') ?>
<?php use_stylesheet('plugins.css') ?>
<?php use_stylesheet('layout.css') ?>
<?php use_stylesheet('custom.css') ?>
<?php // use_stylesheet('jquery.dataTables_themeroller.css') ?>
<?php use_stylesheet('main_page.css') ?>
<?php use_stylesheet('table-row-hover') ?>

<?php use_javascript('jquery.flot.min.js') ?>
<?php use_javascript('jquery.flot.resize.min.js') ?>
<?php use_javascript('jquery.flot.categories.min.js') ?>
<?php use_javascript('jquery.pulsate.min.js') ?>
<?php use_javascript('metronic.js') ?>
<?php use_javascript('layout.js') ?>
<?php use_javascript('quick-sidebar.js') ?>
<?php use_javascript('jquery.easypiechart.min.js') ?>
<?php use_javascript('tasks.js') ?>
<?php use_javascript('bootstrap-select.min.js') ?>
<?php use_javascript('lib/datatables/js/jquery.dataTables.min.js') ?>
<?php use_javascript('lib/datatables/js/dataTables.bootstrap.js') ?>
<div style="clear:both"></div>
<div class="row">
<div class="wrapper-panel" id="">
</tbody>

<h3 class="page-title">Users</h3>
<div id="left-component" class="dv-container-left minimize-margin">
<div class="form-group select-column">
    <label class="control-label select-column"></label>
    <div class="select-wrapper">
        <select id="select-column" multiple title='Select columns' data-selected-text-format="count>0" data-icon="fa fa-columns" data-style="blue">
            <option value="first_name" selected="">First name</option>
            <option value="last_name" selected="">Last name</option>
            <option value="email" selected="">Email</option>
            <option value="role" selected="">Role</option>
            <option value="phone_number" selected="">Phone number</option>
        </select>
        &nbsp;&nbsp;
        <button type="button" onClick="UserDataTable.ajax.reload();" class="btn btn-default blue">Refresh <span class="glyphicon glyphicon-refresh"></span></button>
        &nbsp;&nbsp;
        <a data-toggle="modal" href="#basic">
            <button onclick="reset_add_new_user_form();" type="button" class="btn green">Add New User</button>
        </a>
    </div>
    <div style="clear:left"></div>
</div>
<div>

<table id="devices-table" class="table table-striped table-bordered display" cellspacing="0" width="100%">
<thead>
<tr>
    <th class="text-center no-sort">ID</th>
    <th class="text-center">Status</th>
    <th>Username</th>
    <th>First name</th>
    <th>Last name</th>
    <th class="">Email</th>
    <th class="text-center">Role</th>
    <th class="">Phone number</th>
    <th class="">DOB</th>
    <th class="text-center no-sort">Action</th>
</tr>
</thead>
<tbody>
</tbody>
</table>

</div>
<div style="clear:both"></div>
</div>

<div style="clear:both"></div>

</div>

</div>
<?php include_partial("global/add_new_user_modal", array("roles" => $roles, "userTooltip" => $userTooltip));?>
<?php include_partial("global/edit_user_modal", array("roles" => $roles, "userTooltip" => $userTooltip, "error" => $error));?>
<!-- END PAGE LEVEL SCRIPTS -->
<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core componets
        Layout.init(); // init layout
        QuickSidebar.init(); // init quick sidebar
        Tasks.initDashboardWidget();
    });
</script>
<?php $ajaxUrl = url_for('ajax_user_list', array('sf_format' => 'json')) ?>
<script>
    var UserDataTable;
    $(document).ready(function() {
        jQuery('body').on('after_add_new_user', function(evt, data){
            // Reload data after add user
            UserDataTable.ajax.reload();
        });
        UserDataTable = $('#devices-table').DataTable({
            "processing": true,
            "serverSide": true,
            clearSearch: true,
            "ajax": '<?php echo $ajaxUrl; ?>',
            "order": [[2, "asc"]],
            "createdRow": function ( row, data, index ) {
                $(row).attr('data-id', data[0]);
                $(row).attr('data-status', data[1]);
                    if(data[1] == 1) {
                        $(row).find('.fa-circle').addClass('text-success').attr('rel', data[1]);
                    } else {
                        $(row).find('.fa-circle').addClass('text-warning').attr('rel', data[1]);
                    }
            },
            "columnDefs":[{
                targets: 0,
                visible: false
            },
            {
                "targets": 1,
                class: "text-center",
                "render": function ( data, type, full, meta ) {
                    return "<i rel='"+meta[1]+"' class='fa fa-circle'></i>";
                },
                visible: false
            },{
                    targets: 8,
                    visible: false
                },
                {
                targets: -1,
                class: "text-center text-nowrap",
                "data": null,
                "orderable": false,
                "defaultContent": '<a href="javascript:;" onclick="edit_user_data(this); return false;" class="btn btn-icon-only btn-default" data-toggle="tooltip" data-placement="bottom" title="Edit"><i style="color:#428bca" class="fa fa-edit"></i></a>&nbsp;<a href="javascript:;" onclick="delete_user_data(this); return false;" class="btn btn-icon-only btn-default" data-toggle="tooltip" data-placement="bottom" title="Delete"><i style="color:#cb5a5e" class="fa fa-trash-o"></i></a>'
            }
            ]
        });
        $('a.toggle-vis').on('click', function(e) {
            e.preventDefault();

            // Get the column API object
            var column = table.column($(this).attr('data-column'));

            // Toggle the visibility
            column.visible(!column.visible());
        });
        $('#select-column').selectpicker({
            countSelectedText: 'Select columns',
            title: 'Select columns'
        });
        $('#select-column').on('change',function(){
            var showColumns = $('#select-column').selectpicker('val');
            if (!showColumns)   {
                showColumns = ['user_name']
            } else {
                showColumns.push('user_name');
            }
            var dataColumns = { 2 :'user_name', 3 : 'first_name', 4 : 'last_name', 5:'email', 6:"role", 7:'phone_number',8:'birthday'};
            for(var index in dataColumns){
                if( showColumns.indexOf(dataColumns[index]) != -1 )
                {
                    UserDataTable.column(index).visible(true);
                }
                else
                {
                    UserDataTable.column(index).visible(false);
                }

            }
        });
        $('.select-picker').selectpicker();
        /* Status Sort plugins */
        jQuery.extend( jQuery.fn.dataTableExt.oSort, {
            "status-sort-pre": function ( a ) {
                var x = jQuery(a).attr('rel');
                return parseFloat( x );
            },
            "status-sort-asc": function ( a, b ) {
                return ((a < b) ? -1 : ((a > b) ? 1 : 0));
            },
            "status-sort-desc": function ( a, b ) {
                return ((a < b) ? 1 : ((a > b) ? -1 : 0));
            }
        } );
    });
    function reset_add_new_user_form(){
        jQuery('body').trigger('reset_add_new_user_form');
    }
    function edit_user_data(elm){
        var self = jQuery(elm).parents("tr:first");
        var id = self.attr('data-id');
        jQuery('body').trigger('edit_user_form', id);
    }
    function delete_user_data(elm){
        var self = jQuery(elm).parents("tr:first");
        var id = self.attr('data-id');
        var loading = $("#devices-table_processing");
        if (id) {
            my_confirm("<?php echo $confirm['C1001'] ?>", function() {
                loading.show();
                jQuery.ajax({
                    url: '<?php echo url_for('ajax_delete_user', array('sf_format' => 'json'));?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {id: id},
                    success: function (data) {
                        loading.hide();
                        if (data.error.status) {
                            my_alert(data.error.msg, 'error');
                        } else {
                            if (data.redirect)  {
                                my_alert(data.error.msg, 'type-warning', function() {
                                    window.location.href = data.redirect;
                                });
                            } else {
                                UserDataTable.ajax.reload();
                            }
                        }
                    },
                    error: function () {
                        loading.hide();
                    }
                });
            });
        }
    }
</script>


<!-- END JAVASCRIPTS -->
