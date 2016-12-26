<?php use_stylesheet('bootstrap-switch.min.css') ?>
<?php use_stylesheet('daterangepicker-bs3.css') ?>
<?php use_stylesheet('tasks.css') ?>
<?php use_stylesheet('plugins.css') ?>
<?php use_stylesheet('layout.css') ?>
<?php use_stylesheet('custom.css') ?>
<?php use_stylesheet('jquery.dataTables_themeroller.css') ?>
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

        <h3 class="page-title">Email Templates</h3>
        <div id="left-component" class="dv-container-left minimize-margin">
            <div class="form-group select-column">
                <label class="control-label select-column"></label>
                <div class="select-wrapper">
                    <select id="select-column" multiple title='Select columns' data-selected-text-format="count>0" data-icon="fa fa-columns" data-style="blue">
                        <!--<option value="name" selected="">Template Name</option>-->
                        <option value="usage_system" selected="">Usage System</option>
                        <option value="updated_at" selected="">Last Edited</option>
                    </select>
                </div>
                <div style="clear:left"></div>
            </div>
            <div>

                <table id="devices-table" class="table table-striped table-bordered display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="text-center ">ID</th>
                            <th>Template Name</th>
                            <th>Usage System</th>
                            <th>Last Edited</th>
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
<?php include_partial("global/edit_template_modal", array("usageSystem" => $usageSystem, "error" => $error));?>
<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core componets
        Layout.init(); // init layout
        QuickSidebar.init(); // init quick sidebar
        Tasks.initDashboardWidget();
    });
</script>
<?php $ajaxUrl = url_for('ajax_template_list', array('sf_format' => 'json')) ?>
<script>
    var TemplateDataTable;
    $(document).ready(function() {
        jQuery('body').on('after_update_template', function(evt, data){
            // Reload data after add user
            TemplateDataTable.ajax.reload();
        });
        TemplateDataTable = $('#devices-table').DataTable({
            "processing": true,
            "serverSide": true,
            clearSearch: true,
            "ajax": '<?php echo $ajaxUrl; ?>',
            "order": [[2, "asc"], [1, "asc"]],
            "createdRow": function ( row, data, index ) {
                $(row).attr('data-id', data[0]);
            },
            "columnDefs": [
                {
                    targets: -1,
                    class: "text-center text-nowrap",
                    "data": null,
                    "orderable": false,
                    "defaultContent": '<a href="javascript:;" onclick="edit_template_data(this); return false;" class="btn btn-icon-only btn-default" data-toggle="tooltip" data-placement="bottom" title="Edit"><i style="color:#428bca" class="fa fa-edit"></i></a>'
                },
                {
                    "targets": 0,
                    "class": "text-center text-nowrap",
                    "visible": false
                },
                {
                    targets: 3,
                    data: 3,
                    render: function(data, type){
                        if ( type === 'display' || type === 'filter' ) {
                            return get_local_time(data,true);
                        }
                        return data;
                    }
                },
            ]
        });
        $('#select-column').selectpicker({
            countSelectedText: 'Select columns',
            title: 'Select columns'
        });
        $('#select-column').on('change', function() {
            var showColumns = $('#select-column').selectpicker('val');
            if (!showColumns)   {
                showColumns = ['name']
            } else {
                showColumns.push('name');
            }
            var dataColumns = {1: 'name', 2: 'usage_system', 3: 'updated_at'};
            for (var index in dataColumns) {
                if (showColumns.indexOf(dataColumns[index]) != -1)
                {
                    TemplateDataTable.column(index).visible(true);
                }
                else
                {
                    TemplateDataTable.column(index).visible(false);
                }

            }
        });
        $('.select-picker').selectpicker();
        /* Status Sort plugins */
        jQuery.extend(jQuery.fn.dataTableExt.oSort, {
            "status-sort-pre": function(a) {
                var x = jQuery(a).attr('rel');
                return parseFloat(x);
            },
            "status-sort-asc": function(a, b) {
                return ((a < b) ? -1 : ((a > b) ? 1 : 0));
            },
            "status-sort-desc": function(a, b) {
                return ((a < b) ? 1 : ((a > b) ? -1 : 0));
            }
        });
    });
    function edit_template_data(elm) {
        var self = jQuery(elm).parents("tr:first");
        var id = self.attr('data-id');
        jQuery('body').trigger('edit_template_form', id);
    }
</script>


<!-- END JAVASCRIPTS -->
