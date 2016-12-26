<?php use_stylesheet('bootstrap-switch.min.css') ?>
<?php use_stylesheet('daterangepicker-bs3.css') ?>
<?php use_stylesheet('tasks.css') ?>
<?php use_stylesheet('plugins.css') ?>
<?php use_stylesheet('layout.css') ?>
<?php use_stylesheet('custom.css') ?>
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
<?php  use_javascript('lib/datatables/js/jquery.dataTables.min.js') ?>
<?php use_javascript('lib/datatables/js/dataTables.bootstrap.js') ?>

<div class="row">
    <div class="wrapper-panel" id="">
        <h3 class="page-title">Configuration Profiles</h3>
        <div id="left-component" class="dv-container-left minimize-margin">
            <div class="form-group select-column">
                <label class="control-label select-column"></label>
                <div class="select-wrapper">
                    &nbsp;&nbsp;
                    <button type="button" onClick="UserDataTable.ajax.reload();" class="btn btn-default blue">Refresh <span class="glyphicon glyphicon-refresh"></span></button>
                    &nbsp;&nbsp;
                    <a data-toggle="modal" href="#add_profile_modal">
                        <button onclick="clear_data_add_new()" type="button" class="btn green">Add New Profile</button>
                    </a>
                </div>
                <div style="clear:left"></div>
            </div>
            <div>
                <table id="profile_table" class="table table-striped table-bordered display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Platform ID</th>
                            <th>Name</th>
                            <th>Type Number</th>
                            <th>Configuration type</th>
                            <th>Platform</th>
                            <th class="no-sort">Description</th>
                            <th>Updated at</th>
                            <th class="no-sort text-center" style="width: 150px">Action</th>
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

<?php $ajaxUrl = url_for('ajax_profile_list', array('sf_format' => 'json')) ?>

<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core componets
        Layout.init(); // init layout
        QuickSidebar.init(); // init quick sidebar
        Tasks.initDashboardWidget();
        $('.select-picker').selectpicker();
    });
    
    // Initial object, use in all modal
    // p = {'1': {'3': 'Location'}, '2': {'2': 'iOS Passcode', '3': 'Location'}}
    var platform_config = {
        <?php foreach ($platform_config as $index => $value) {
            echo "'" . $index . "': {";
            foreach ($value as $k => $v) {
                echo "'" . $k . "': '" . $v . "', ";
            }
            echo "}, ";
        } ?>
    };
</script>

<script>
    $(document).ready(function() {
        UserDataTable = $('#profile_table').DataTable({
            retrieve: true,
            clearSearch: false,
            "processing": true,
            "serverSide": true,
            "ajax": '<?php echo $ajaxUrl; ?>',
            "order": [[2, "asc"]],
            // Write data to each row
            "createdRow": function ( row, data, index ) {
                $(row).attr('data-profile_id', data[0]);
                $(row).attr('data-platform', data[1]);
                $(row).attr('data-configuration_type', data[3]);
            },
            "columnDefs": [
                {
                    "targets": -1, "data": null,
                    "orderable": false,
                    class: "text-center",
                    "defaultContent": '<a href="javascript:;" onclick="edit_profile(this);return false;" class="btn btn-icon-only btn-default" data-toggle="tooltip" data-placement="bottom" title="Edit"><i style="color:#428bca" class="fa fa-edit"></i></a>&nbsp;<a href="javascript:;" onclick="delete_profile(this);return false;" class="btn btn-icon-only btn-default" data-toggle="tooltip" data-placement="bottom" title="Delete"><i style="color:#cb5a5e" class="fa fa-trash-o"></i></a>&nbsp;<button onclick="deploy_profile(this);" class="btn btn-icon-only btn-default deploy_profile" data-toggle="tooltip" data-placement="bottom" title="Deploy"><i style="color:#c49f47" class="fa fa-upload"></i></button>'
                },
                {
                    "targets": [0, 1, 3, -2],
                    "visible": false
                },
                {
                    "targets": -3,
                    "orderable": false
                }
            ]
        });
    });

    function deploy_profile(tr) {
        var data = $(tr).parents("tr");
        var object = {"id": data.attr("data-profile_id"),
            "platform": data.attr("data-platform"),
            "type": data.attr("data-configuration_type")};
        $('body').trigger('show_deploy_profile_modal', object);
    }

    function edit_profile(row) {
        var data = $(row).parents("tr");
        $('body').trigger('show_edit_profile_modal', data.attr("data-profile_id"));
    }

    function delete_profile(row) {
        var data = $(row).parents("tr");
        var id = data.attr("data-profile_id");
        if (id) {
            my_confirm("<?php echo $confirm['C1001'] ?>", function() {
                jQuery.ajax({
                    url: '<?php echo url_for('ajax_delete_profile', array('sf_format' => 'json')); ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {id: id},
                    success: function(data) {
                        if (data.error.status) {
                            my_alert(data.error.msg, 'error');
                        } else {
                            UserDataTable.ajax.reload();
                        }
                    },
                    error: function() {
                    }
                });
            });
        }
    }

    function show_tooltip_passcode(element){
        var header = $(element).attr('data-header');
        // Change break line symbol in tooltip to html
        var content = $(element).attr('data-content').replace("\n", "<br>");
        // Set header and content for promt
        $("#help_modal").find("h3").html(header);
        $("#help_modal").find(".modal-body").html(content);
        $("#help_modal").modal("show");
    }

    // Clear data and when open modal add profile
    function clear_data_add_new() {
        jQuery('body').trigger('show_add_profile_modal');
    }
</script>

<?php include_partial('add_profile_modal', array('configTypes' => $configTypes,'platformNames' => $platformNames, 'profilePasscodeTooltip' => $profilePasscodeTooltip, 'locationWarning' => $locationWarning, 'iOSpasscodeSetting' => $iOSpasscodeSetting)) ?>
<?php include_partial('deploy_profile_modal') ?>
<?php include_partial('edit_profile_modal',array('configTypes' => $configTypes,'platformNames' => $platformNames, 'profilePasscodeTooltip' => $profilePasscodeTooltip, 'locationWarning' => $locationWarning, 'iOSpasscodeSetting' => $iOSpasscodeSetting)) ?>
<?php include_partial('global/help_prompt_modal') ?>