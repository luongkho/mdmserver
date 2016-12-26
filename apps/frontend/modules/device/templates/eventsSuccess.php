<?php use_stylesheet('table-row-hover') ?>

<style type="text/css">
    #devices-table tbody tr td  {
        padding: 12px;
    }
</style>

<div class="row">
    <div class="wrapper-panel" id="">
        <h3 class="page-title">Activity Log</h3>
        <div id="left-component" class="dv-container-left minimize-margin">
            <div class="form-group select-column column-filter">
                <label class="control-label select-column"></label>
                <div class="select-wrapper">
                    <select id="select-column" multiple title='Select columns' data-selected-text-format="count>0" data-style="blue">
                        <option value="event_type">Event type</option>
                        <option value="event_name" selected="">Event name</option>
                        <option value="user" selected="">User</option>
                        <option value="sent_by" selected="">Sent by</option>
                        <option value="created" selected="">Created</option>
                    </select>
                    &nbsp;&nbsp;
                    <button type="button" onClick="UserDataTable.ajax.reload();" class="btn btn-default blue">Refresh <span class="glyphicon glyphicon-refresh"></span></button>
                </div>
                <div style="clear:left"></div>
            </div>
            <div>
                <table id="devices-table" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="" width="8%">Status</th>
                            <th class="">Device name</th>
                            <th class="">Event type</th>
                            <th class="">Event name</th>
                            <th class="">User</th>
                            <th class="">Sent by</th>
                            <th class="">Created</th>
                            <th class="">Device ID</th>
                            <th class="text-center no-sort" >Detail</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div style="clear:both"></div>
        </div>

        <div style="clear:both"></div>

    </div>

</div>
<?php $ajaxUrl = url_for('ajax_event_list', array('sf_format' => 'json')) ?>

<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core componets
        Layout.init(); // init layout
        QuickSidebar.init(); // init quick sidebar
        Tasks.initDashboardWidget();
    });
</script>

<script type="text/javascript">
    var UserDataTable = null;
    $(document).ready(function() {
        UserDataTable = $('#devices-table').DataTable({
            clearSearch: false,
            "processing": true,
            "serverSide": true,
            "ajax": '<?php echo $ajaxUrl; ?>',
            "order": [[6, "desc"]],
            // Add class to show Error events
            "createdRow": function ( row, data, index ) {
                var status = data[0];
                switch (status) {
                <?php
                foreach ($eventStatus as $key => $val) {
                    echo "case '{$val}': status ='{$key}'; break;";
                }
                ?>
                                }
                $(row).attr('data-status', status);
            },
            "columnDefs": [
                {
                    targets: [1],
                    orderable: false
                },
                {
                    targets: 6,
                    data: 6,
                    render: function(data, type){
                        if ( type === 'display' || type === 'filter' ) {
                            return get_local_time(data,true);
                        }
                        return data;
                    }
                },
                {
                    "targets": [-2,2],
                    "visible": false
                },
                {
                    "targets": -1,
                    "data": null,
                    "orderable": false,
                    class: "text-center",
                    "defaultContent": "<a> <button type='button' class='btn btn-default btn-xs detail'><span class='fa fa-share' aria-hidden='true'></span></button></a>"
                }
            ],
            "fnDrawCallback": function() {
                // Change error text color
                var trs = $("#devices-table tbody").find("tr");
                $.each(trs, function() {
                    var status = $(this).attr('data-status');
                    if (status == "3")  {
                        $(this).children("td:first-child").css("color", "red");
                    }
                });
            }
        });
        
        $('#devices-table tbody').on('click', 'button.detail', function() {
            var data = UserDataTable.row($(this).parents('tr')).data();
            window.location.href = data[8];
        });

        $('#devices-table tbody').on('dblclick', 'td', function() {
            var data = UserDataTable.row($(this).parents('tr')).data();
            window.location.href = data[8];
        });

        $('#select-column').selectpicker({
            countSelectedText: 'Select columns', // set text when count column
            title: 'Select columns'              // title
        });

        $('#select-column').on('change', function() {
            var showColumns = $('#select-column').selectpicker('val');
            if (!showColumns)   {
                showColumns = ['device_name', 'status']
            } else {
                showColumns.push('device_name', 'status');
            }
            var dataColumns = {0: "status", 1: 'device_name', 2: 'event_type',
                3: 'event_name', 4: 'user', 5: 'sent_by', 6: 'created'};
            for (var index in dataColumns) {
                if (showColumns.indexOf(dataColumns[index]) != -1)
                {
                    UserDataTable.column(index).visible(true);
                }
                else
                {
                    UserDataTable.column(index).visible(false);
                }
            }
        });
    });
</script>
