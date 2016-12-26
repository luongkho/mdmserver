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
        <h3 class="page-title"><?php echo $page_title; ?></h3>
        <div id="left-component" class="dv-container-left minimize-margin">
            
            <div class="hidden">
                <form method="post" id="upload_file" action="<?php echo url_for('upload_device_linkup', array('sf_format' => 'json')); ?>" class="form-horizontal" novalidate="novalidate" enctype="multipart/form-data">
                    <input type="file" onchange="handleUploadFile(this);" class="form-control" name="file_upload" id="file_upload">
                </form>
            </div>
            <div class="form-group select-column">
                <label class="control-label select-column"></label>
                <div class="select-wrapper">
                    &nbsp;&nbsp;
                    <button type="button" onClick="LinkupDataTable.ajax.reload();" class="btn btn-default blue">Refresh <span class="glyphicon glyphicon-refresh"></span></button>
                </div>
                <div style="clear:left"></div>
            </div>
            <div>
                <table id="linkup_version_table" class="table table-striped table-bordered display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Device Platform</th>
                            <th>Software Version</th>                                            
                            <th width="14%" class="text-center no-sort">Action</th>
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

<?php $ajaxUrl = url_for('ajax_device_linkup_list', array('sf_format' => 'json')) ?>

<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core componets
        Layout.init(); // init layout
        QuickSidebar.init(); // init quick sidebar
        Tasks.initDashboardWidget(); 
    });
</script>

<script>
    var keyword, software_extension, software_extension_error_msg, LinkupDataTable;
    LinkupDataTable = $('#linkup_version_table').DataTable({
        retrieve: true,
        clearSearch: false,
        "processing": true,
        "serverSide": true,
        "ajax": '<?php echo $ajaxUrl; ?>',
        "order": [[1, "asc"]],
        "createdRow": function ( row, data, index ) {
            $(row).attr('data-id', data[0]);
            $(row).attr('data-extension', data[3]);
            $(row).attr('data-extension-error-msg', data[4]);
        },
        "columnDefs": [
            {
                "targets": -1, 
                "data": null,
                "orderable": false,
                class: "text-center",
                "defaultContent": '<button onclick="update_device_version(this);" onmouseover="show_tooltip(this);" data-toggle="tooltip" data-placement="left" class="btn btn-icon-only btn-default update-version" ><i style="color:#c49f47" class="fa fa-upload"></i></button>'
            },
            {
                "targets": 0,
                "visible": false
            },
            {
                "targets": -1,
                "orderable": false
            },
        ],
    });
    
    function update_device_version(tag){
        keyword = $(tag).parent().parent('tr').attr('data-id');
        software_extension = $(tag).parent().parent('tr').attr('data-extension');
        $("#file_upload").click();
    }
    
    function show_tooltip(element){
        var soft_extension = $(element).parent().parent('tr').attr('data-extension');
        var msg = "<?php echo $notification['N9002'] ?>";
        $(element).attr("title", msg.replace("${extension}", soft_extension));
    }
    
    function invalidStructure(file_name){
        var nameRegex = /^[A-Za-z0-9_\-\.]+_v[0-9\.]+$/i;
        if(nameRegex.exec(file_name)){
            return false;
        }
        return true;
    }
    
    function readyUpload(element){
        $(element).val('').clone(true);
        $("#linkup_version_table_processing").hide();
        $(".update-version").removeAttr("disabled");
    }
    
    function handleUploadFile(element){
        var file = element.files[0];
        var file_name = file.name;
        var file_name = file_name.substring(0,file_name.lastIndexOf('.'));
        var file_extension = file.name.split('.').pop();
        $("#linkup_version_table_processing").show();
        $(".update-version").attr("disabled",true);
        
        if(file_extension != software_extension){
            var msg = "<?php echo $error['E6012'] ?>";
            my_alert(msg.replace("${extension}", "<b>" + software_extension + "</b>"), 'type-warning');
            readyUpload(element);
            return;
        }
        
        if(invalidStructure(file_name)){
            var msg = "<?php echo $error['E6014'] ?>";
            my_alert(msg.replace("filename_v1.0.0.xxxx", "<b>filename_v1.0.0.xxxx</b>"), 'type-warning');
            readyUpload(element);
            return;
        }
        
        var dataForm = new FormData();
        //Append files infos
        $.each($(element)[0].files, function(i, file) {
            dataForm.append('software_upload', file);
        });
        dataForm.append('keyword',keyword);
        $.ajax({
            url: $("#upload_file").attr("action"), // Url to which the request is send
            type: "POST",             // Type of request to be send, called as method
            data: dataForm,           // Data sent to server, a set of key/value pairs (i.e. form fields and values)
            contentType: false,       // The content type used when sending data to the server.
            cache: false,             // To unable request pages to be cached
            processData:false,        // To send DOMDocument or non processed data file it is set to false
            dataType:"json",
            success: function(jdata)   // A function to be called if request succeeds
            {
                readyUpload(element);
                if(jdata.error){
                    my_alert(jdata.msg,'type-warning');
                }else{
                    my_alert("<?php echo $notification['N2001'] ?>");
                    LinkupDataTable.ajax.reload();
                }
            },
            error:function(){
                readyUpload(element);
                my_alert("<?php echo $error['E6017'] ?>", 'type-warning');
            }
        });
    }
</script>