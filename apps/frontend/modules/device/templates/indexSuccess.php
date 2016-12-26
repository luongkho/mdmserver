<?php use_stylesheet('table-row-hover') ?>

<div class="row">
    <div class="wrapper-panel" id="">
        <h3 class="page-title">Devices</h3>
        <div id="left-component" class="dv-container-left minimize-margin">
            <div class="form-group select-column column-filter">
                <label class="control-label select-column"></label>
                <div class="select-wrapper">
                    <select id="select-column" multiple title='Select columns' data-selected-text-format="count>0" data-style="blue">
                        <option value="software_version" selected="">Software version</option>
                        <option value="user" selected="">User</option>
                        <option value="last_report" selected="">Last reported</option>
                        <option value="imei">IMEI</option>
                        <option value="mac_address">Wifi MAC Address</option>
                    </select>
                    &nbsp;&nbsp;
                    <button type="button" onClick="table.ajax.reload();" class="btn btn-default blue">Refresh <span class="glyphicon glyphicon-refresh"></span></button>
                </div>
                <div style="clear:left"></div>
            </div>
            <div>
                <table id="devices-table" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="no-sort"></th>
                            <th class="no-sort"></th>
                            <th class="text-center online_status" width="8%">Status</th>
                            <th width="20%" disabled="disabled">Device name</th>
                            <th width="16%">Software version</th>
                            <th width="8%">User</th>
                            <th width="12%">Last reported</th>
                            <th width="8%">IMEI</th>
                            <th width="16%">Wifi MAC Address</th>
                            <th class="no-sort">Platform</th>
                            <th class="text-center no-sort" width="5%">Detail</th>
                            <th class="text-center no-sort" width="5%">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div style="clear:both"></div>
        </div>

        <div style="clear:both"></div>

    </div>

</div>
<?php $ajaxUrl = url_for('ajax_device_list', array('sf_format' => 'json')) ?>

<script type="text/javascript">
    var table = null;
    $(document).ready(function() {
        table = $('#devices-table').DataTable({
            clearSearch: false,
            "processing": true,
            "serverSide": true,
            "ajax": {
                'type': 'POST',
                'url': '<?php echo $ajaxUrl; ?>',
            },
            "order": [[6, "desc"]],
            'columnDefs': [
                {
                    "targets": [0, 1, -3, -4, -5],
                    "visible": false
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
                    "targets": -2,
                    "data": null,
                    "orderable": false,
                    class: "text-center",
                    "defaultContent": "<a> <button type='button' class='btn btn-default btn-xs detail'><span class='fa fa-share' aria-hidden='true'></span></button></a>"
                },
                {
                    "targets": -1,
                    "data": null,
                    "orderable": false,
                    class: "text-center",
                    "render": function(data, type, full, meta) {
                        var status = data[2];
                        switch (status) {
                        <?php
                        $enroll_statuses = \sfConfig::get("app_enroll_statuses_data");
                        foreach ($enroll_statuses as $key => $val) {
                            echo "case '{$val}': status ='{$key}'; break;";
                        }
                        ?>
                        }
                        var output = "<div class='dropdown device-actions'><button class='btn btn-default dropdown-toggle' type='button' data-toggle='dropdown' aria-expanded='true' data-placement-range='#devices-table'><i class='fa fa-cogs'></i><span class='caret'></span></button><ul class='dropdown-menu dropdown-menu-right' role='menu' aria-labelledby='dropdownMenu1'>";
                        output += "<li role='presentation' class='" + (status != 0 ? 'disabled' : '') + "'><a role='menuitem' tabindex='-1' href='#' rel='unenroll_device'><i class='fa fa-chain-broken' ></i>&nbsp;Unenroll device</a></li>";
                        output += "<li role='presentation' class='" + (status == 2 ? 'disabled' : '') + "'><a role='menuitem' tabindex='-1' href='#' rel='allow_reenroll_device'><i class='fa fa-chain' ></i>&nbsp;Allow re-enroll device</a></li>";
                        output += "<li role='presentation' class='" + (status != 0 ? 'disabled' : '') + "'><a role='menuitem' tabindex='-1' href='#' rel='lock_device'><i class='fa fa-lock' ></i>&nbsp;Lock device</a></li>";
                        output += "<li role='presentation' class='" + (status != 0 ? 'disabled' : '') + "'><a role='menuitem' tabindex='-1' href='#' rel='wipe_device'><i class='fa fa-fire' ></i>&nbsp;Device Linkup Data Removal</a></li>";
                        output += "<li role='presentation' class='" + (status != 0 ? 'disabled' : '') + "'><a role='menuitem' tabindex='-1' href='#' rel='reset_passcode'><i class='fa fa-retweet' ></i>&nbsp;Reset passcode</a></li>";
                        output += "</ul></div>";
                        return output;
                    },
                    "defaultContent": "<div class='dropdown device-actions'><button class='btn btn-default dropdown-toggle' type='button' data-toggle='dropdown' aria-expanded='true' data-placement-range='#devices-table'><i class='fa fa-cogs'></i><span class='caret'></span></button><ul class='dropdown-menu dropdown-menu-right' role='menu' aria-labelledby='dropdownMenu1'><li role='presentation'><a role='menuitem' tabindex='-1' href='#' rel='unenroll_device'><i class='fa fa-chain-broken' ></i>&nbsp;Unenroll device</a></li><li role='presentation'><a role='menuitem' tabindex='-1' href='#' rel='allow_reenroll_device'><i class='fa fa-chain' ></i>&nbsp;Allow re-enroll device</a></li><li role='presentation'><a role='menuitem' tabindex='-1' href='#' rel='lock_device'><i class='fa fa-lock' ></i>&nbsp;Lock device</a></li><li role='presentation'><a role='menuitem' tabindex='-1' href='#' rel='wipe_device'><i class='fa fa-fire' ></i>&nbsp;Device Linkup Data Removal</a></li><li role='presentation'><a role='menuitem' tabindex='-1' href='#' rel='reset_passcode'><i class='fa fa-retweet' ></i>&nbsp;Reset passcode</a></li></ul></div>"
                }
            ],
            "fnInitComplete": function() {

                $('#check_all').click(function() {
                    $('input[class="chkbox"]', table.cells().nodes()).attr('checked', this.checked);
                });

            }
        });
        /**
         * Fix action menu when show at bottom
         */
        $('#devices-table tbody').on('shown.bs.dropdown', '.dropdown', function() {
            var placement, placementHeight, whereThisEnd;
            $parent = jQuery(this);
            $this = $parent.children('button.dropdown-toggle');
            if ((placement = $this.data("placement-range"))) {
                placementHeight = $(placement).innerHeight() - 10; //10 is for scrollbar. need to replace it with some logic.
                whereThisEnd = $parent.position().top + $parent.height() + $parent.find(".dropdown-menu").height();

                if (whereThisEnd > placementHeight)
                    $parent.addClass("dropup");
                else
                    $parent.removeClass("dropup");
            }
        });
        $('#devices-table tbody').on('click', 'button.detail', function() {
            var data = table.row($(this).parents('tr')).data();
            window.location.href = data[1];
        });
        $('#devices-table tbody').on('click', '.dropdown.device-actions li a', function() {
            var self = $(this);
            if (self.parents('li:first').hasClass('disabled'))
                return; // Non-accept this action
            var data = table.row($(this).parents('tr')).data();
            var id = data[0];
            var platform = data[9];
            var type_action = self.attr('rel');
            /* Confirm dialog */
            var confirm_msg = "<?php echo $confirm['C2005'] ?>";
            switch (type_action) {
                case 'unenroll_device':
                    confirm_msg = "<?php echo $confirm['C2003'] ?>";
                    break;
                case 'allow_reenroll_device':
                    confirm_msg = "<?php echo $confirm['C2004'] ?>";
                    break;
                case 'lock_device':
                    confirm_msg = "<?php echo $confirm['C2005'] ?>";
                    break;
                case 'wipe_device':
                    confirm_msg = "<?php echo $confirm['C2006'] ?>";
                    break;
                case 'reset_passcode':
                    confirm_msg = "<?php echo $confirm['C2007'] ?>";
                    break;
                default:
//                    confirm_msg = "Are you sure to " + type_action + " this device?";
                    break;
            }
            my_confirm(confirm_msg, function() {
                /* Call ajax if user confirm */
                $.ajax({
                    url: '<?php echo url_for('ajax_action_device_task', array('sf_format' => 'json')); ?>',
                    data: {id: id, 'action_type': type_action, 'platform': platform,
                            'user_id': <?php echo $sf_user->getDecorator()->getUserId() ?>},
                    dataType: 'json',
                    success: function(data) {
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
                            table.ajax.reload();
                        }
                    },
                    error: function() {

                    }
                });
            });
        });

        $('#devices-table tbody').on('dblclick', 'td', function() {
            var data = table.row($(this).parents('tr')).data();
            window.location.href = data[1];
        });

        $('#select-column').selectpicker({
            countSelectedText: 'Select columns',
            title: 'Select columns'
        });
        $('#select-column').on('change', function() {
            var showColumns = $('#select-column').selectpicker('val');
            if (!showColumns)   {
                showColumns = ['device_name', 'detail', 'action', 'online_status']
            } else {
                showColumns.push('device_name', 'detail', 'action', 'online_status');
            }
            var dataColumns = {0: 'id', 1: 'url', 2: 'online_status', 3: 'device_name', 4: 'software_version', 5: 'user', 6: 'last_report', 7: 'imei', 8: 'mac_address', 9: 'platform', 10: 'detail', 11: 'action'};
            for (var index in dataColumns) {
                if (index === 9)    {
                    continue;       // Alway hide platform
                }
                if (showColumns.indexOf(dataColumns[index]) != -1)
                {
                    table.column(index).visible(true);
                }
                else
                {
                    table.column(index).visible(false);
                }

            }
        });

        Metronic.init(); // init metronic core componets
        Layout.init(); // init layout
        QuickSidebar.init(); // init quick sidebar
        Tasks.initDashboardWidget();

    });

</script>
