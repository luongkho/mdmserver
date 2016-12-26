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
<?php use_javascript('lib/datatables/js/jquery.dataTables.min.js') ?>
<?php use_javascript('lib/datatables/js/dataTables.bootstrap.js') ?>

<div class="row">
    <div class="wrapper-panel" id="">
        <h3 class="page-title">Locations</h3>
        <div id="left-component" class="dv-container-left minimize-margin">
            <div class="form-group select-column column-filter">
                <label class="control-label select-column"></label>
                <div class="select-wrapper">
                    <a data-toggle="modal" href="#add_new_location_modal">
                        <button onclick="" type="button" class="btn green">Add New Location</button>
                    </a>
                </div>
                <div style="clear:left"></div>
            </div>
            <div>
                <table id="locations-table" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="">Id</th>
                            <th class="">Organization</th>
                            <th class="">Location</th>
                            <th class="text-center no-sort" width="9%">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div style="clear:both"></div>
        </div>

        <div style="clear:both"></div>
    </div>
</div>
<?php $ajaxUrl = url_for('ajax_location_list', array('sf_format' => 'json')) ?>
<?php include_partial('add_new_location_modal') ?>
<?php include_partial('edit_location_modal', array("error" => $error)) ?>

<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core componets
        Layout.init(); // init layout
        QuickSidebar.init(); // init quick sidebar
        Tasks.initDashboardWidget();
    });
</script>

<script>
    var edit_location_id, table;
    
    $(document).ready(function() {
        table = $("#locations-table").DataTable({
            "processing": true,
            "serverSide": true,
            clearSearch: true,
            "ajax": '<?php echo $ajaxUrl; ?>',
            "order": [[1, "asc"], [2, "asc"]],
            "createdRow": function ( row, data, index ) {
                $(row).attr('data-id', data[0]);
            },
            "columnDefs": [{
                    targets: 0,
                    visible: false
                },
                {
                    targets: -1,
                    orderable: false,
                    data: null,
                    defaultContent: '<a href="javascript:;" onclick="edit_location(this); return false;" class="btn btn-icon-only btn-default" data-toggle="tooltip" data-placement="bottom" title="Edit"><i style="color:#428bca" class="fa fa-edit"></i></a>&nbsp;<a href="javascript:;" onclick="delete_location(this); return false;" class="btn btn-icon-only btn-default" data-toggle="tooltip" data-placement="bottom" title="Delete"><i style="color:#cb5a5e" class="fa fa-trash-o"></i></a>'
                }
            ]
        });
    });

    // Edit a location
    function edit_location(btn) {
        var data = $(btn).parents("tr");
        $('body').trigger('show_edit_location_modal', data.attr("data-id"));
    }

    // Delete a location
    function delete_location(btn) {
        var data = $(btn).parents("tr");
        var id = data.attr("data-id");
        console.log(id);
        my_confirm("<?php echo $confirm['C1001'] ?>", function() {
            jQuery.ajax({
                url: '<?php echo url_for('ajax_delete_location', array('sf_format' => 'json')); ?>',
                type: 'POST',
                dataType: 'json',
                data: {id: id},
                success: function(data) {
                    if (data.error.status) {
                        my_alert(data.error.msg, 'error');
                    } else {
                        table.ajax.reload();
                    }
                },
                error: function() {
                }
            });
        });
    }

    // Update table when adding completed
    $('#add_new_location_modal').on('hide.bs.modal', function() {
        table.ajax.reload();
    });
    $('#edit_location_modal').on('hide.bs.modal', function() {
        table.ajax.reload();
    });
</script>