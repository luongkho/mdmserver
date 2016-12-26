<?php
use_stylesheet("jquery.jqplot.min.css");
use_javascript("jquery.jqplot.min.js");
use_javascript("lib/jqplot/jqplot.donutRenderer.min.js");
use_javascript("https://maps.googleapis.com/maps/api/js?key=AIzaSyDpUr12p-zrHwD9ZWFPECCDkhaf8x8pVvY&sensor=false&v=3.20");
?>

<style>
    table   thead   tr  th  {
        padding: 10px 15px;
    }
</style>

<div class="row">
    <?php
        $device_platform = $device->getPlatformSlug();
    ?>
    <div class="wrapper-panel" id="">
        <div id="left-component" class="dv-container-left minimize-margin">
            <?php include_partial("right_sidebar", array("deviceId" => $device->id, "status" => $device->getEnrollStatus(), "platform" => $device->getPlatform(), "confirm" => $confirm)); ?>
            <div class="upper-container">
                <div class="device-img">
                    <img src="/img/<?php echo $device_platform;?>-device.png" alt="">
                </div>
                <div class="device-info">
                    <table class="device-spec">
                        <tbody>
                            <tr>
                                <td colspan="4">
                                    <h3 class="page-title" style="margin-top:0"><?php echo $device->getDeviceName();?></h3>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <p class="dv-title">User</p>
                                    <p class="dv-feature"><?php echo $device->getOwnerName() ?></p>
                                </td>
                                <td>
                                    <p class="dv-title">Last reported</p>
                                    <p class="dv-feature">
                                        <!-- <span><i class="fa fa-circle"></i></span> -->
                                        <?php include_partial("global/display_local_time", array('currentDate' => $device->updated_at,'fullTime' => true));?>
                                    </p>
                                </td>
                                <td>
                                    <p class="dv-title">Device</p>
                                    <p class="dv-feature"><?php echo $device->getDeviceName() ?></p>
                                </td>
                                <td>
                                    <p class="dv-title">Software</p>
                                    <p class="dv-feature"><?php echo $device->getVersion() ;?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="clear:left"></div>
            </div>
            <div class="lower-container">
                <div class="tabbable tabbable-tabdrop">
                    <ul class="nav nav-tabs devices-ul">
                        <li class="active">
                            <a href="#tab1" data-toggle="tab" aria-expanded="true">General</a>
                        </li>
                        <li class="">
                            <a href="#tab2" data-toggle="tab" aria-expanded="false">Tags</a>
                        </li>
                        <li class="">
                            <a href="#tab3" data-toggle="tab" aria-expanded="false">Applications</a>
                        </li>
                        <li class="">
                            <a href="#tab4" data-toggle="tab" aria-expanded="false">Activity Log</a>
                        </li>
                        <li class="">
                            <a href="#tab5" data-toggle="tab" aria-expanded="false">Device Information</a>
                        </li>
                        <li class="">
                            <a href="#tab6" id="locationDevice" data-toggle="tab" aria-expanded="false">Device Location</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">
                            <div class="tab-wrapper">
                                <div class="main-left">
                                    <table class="table-main">
                                        <caption> <i style="margin-right:5px" class="fa fa-cog"></i><span>Configuration profiles</span>
                                        </caption>
                                        <thead>
                                            <tr>
                                                <th >Last Updated</th>
                                                <th >Profile Name</th>
                                                <th >Type</th>
                                                <th class="text-center"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($deviceProfiles as $deviceProfile): ?>
                                                <tr>
                                                    <td >
                                                        <?php include_partial("global/display_local_time", array('currentDate' => $deviceProfile->updated_at,'fullTime' => true));?>
                                                    </td>
                                                    <td ><?php echo $deviceProfile->getProfileName(); ?></td>
                                                    <td ><?php echo $deviceProfile->getConfigTypeName(); ?></td>
                                                    <td>
                                                        <a href="javascript:;" onclick="delete_profile(<?php echo $deviceProfile->getProfileId() ?>, <?php echo $deviceProfile->getDeviceId() ?>,
                                                                    <?php echo $deviceProfile->getPlatform() ?>, <?php echo $deviceProfile->getConfigurationType() ?>);
                                                                    return false;" class="btn btn-icon-only btn-default" data-toggle="tooltip" data-placement="bottom" title="Delete Profile">
                                                            <i style="color:#cb5a5e" class="fa fa-trash-o"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <table class="table-main">
                                        <caption><i style="margin-right:5px" class="fa fa-signal"></i><span>Operator network</span>
                                        </caption>
                                        <tbody>
                                            <tr>
                                                <td width="50%">Current</td>
                                                <td width="50%">
                                                    <?php echo $deviceRepository->getValueByAttributeName('current_network'); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%">Home</td>
                                                <td width="50%">
                                                    <?php echo $deviceRepository->getValueByAttributeName('home_network'); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%">Data roaming</td>
                                                <td width="50%">
                                                    <?php echo $deviceRepository->getValueByAttributeName('data_roaming'); ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table class="table-main">
                                        <caption><i style="margin-right:5px" class="fa fa-lock"></i><span>Security</span>
                                        </caption>
                                        <tbody>
                                            <tr>
                                                <td width="50%">Encryption status</td>
                                                <td width="50%">
                                                    <?php echo $deviceRepository->getValueByAttributeName('encryption_status'); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%">
                                                    Passcode set
                                                </td>
                                                <td width="50%">
                                                    <?php echo $deviceRepository->getValueByAttributeName('passcode_set'); ?>
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                                <div class="main-right">
                                    <div class="main-left-wrapper">
                                        <?php
                                        $store = $deviceRepository->getValueByAttributeName('storage_free_total');
                                        $storeArr = explode('/', $store);
                                        $free = $total = 0;
                                        $freeChart = (empty($storeArr[0]) || !is_numeric($storeArr[0])) ? 0 : $storeArr[0];
                                        $totalChart = (empty($storeArr[1]) || !is_numeric($storeArr[1])) ? 0 : $storeArr[1];
                                        $mbToByte = pow(1024, 2);
                                        if(count($storeArr) > 1) {
                                            $freeDraw = $freeChart * $mbToByte;//Convert from MB to Byte
                                            $totalDraw = $totalChart * $mbToByte;//Convert from MB to Byte
                                            if($freeDraw > 0)
                                            {
                                                $free = $deviceRepository->getByteFormatValue($freeDraw,"GB",2,true);
                                            }

                                            if($totalDraw > 0)
                                            {
                                                $total = $deviceRepository->getByteFormatValue($totalDraw,"GB",2,true);
                                            }
                                        }

                                        ?>
                                        <div class="img-storage">
                                            <div class="donut-container" id="storage-used" style="height: 128px; width: 128px;" data="<?php echo ($totalChart > 0) ? ($freeChart * 100)/$totalChart : 0; ?>"></div>
                                        </div>
                                        <table class="main-spec">
                                            <tbody>
                                                <tr>
                                                    <td width="50%">
                                                        <p class="dv-title" style="padding-right: 15px !important;">Free storage</p>
                                                        <p class="dv-feature"><?php echo $free; ?></p>
                                                    </td>
                                                    <td width="50%">
                                                        <p class="dv-title">Storage name</p>
                                                        <p class="dv-feature"><?php echo $deviceRepository->getValueByAttributeName('storage_name'); ?></p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%">
                                                        <p class="dv-title">Total</p>
                                                        <p class="dv-feature"><?php echo $total; ?></p>
                                                    </td>
                                                    <td width="50%">
                                                        <p class="dv-title">Encrypted</p>
                                                        <p class="dv-feature"><?php echo $deviceRepository->getValueByAttributeName('encrypted'); ?></p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div style="clear:left"></div>
                                    </div>
                                    <div class="main-left-divider">
                                        <div></div>
                                    </div>
                                    <div class="main-left-wrapper">
                                        <div class="img-storage">
                                            <?php
                                            $PurchaseDate = $device->getPurchaseDate();
                                            $WarrantyEnd = $device->getWarrantyEnd();
                                            $daysLeft = $deviceRepository->getDayLeft($WarrantyEnd);
                                            $percentWarranty = $deviceRepository->getPercentWarranty($PurchaseDate, $WarrantyEnd);
                                            ?>
                                            <div class="donut-container" id="storage-warranty" style="height: 128px; width: 128px;" data="<?php echo $percentWarranty; ?>"></div>
                                        </div>
                                        <table class="main-spec">
                                            <tbody>
                                                <tr>
                                                    <td width="50%">
                                                        <p class="dv-title">Purchase date</p>
                                                        <p class="dv-feature warranty-starts">
                                                            <?php 
                                                                if(isset($PurchaseDate)){
                                                                    echo date("Y-m-d",  strtotime($PurchaseDate));
                                                                }else{
                                                                    echo "-";
                                                                }
                                                            ?>
                                                        </p>
                                                    </td>
                                                    <td width="50%">
                                                        <p class="dv-title">Days left</p>
                                                        <p class="dv-feature days-left"><?php echo $daysLeft; ?></p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%">
                                                        <p class="dv-title">Warranty ends</p>
                                                        <p class="dv-feature warranty-ends">
                                                            <?php 
                                                                if(isset($WarrantyEnd)){
                                                                    echo date("Y-m-d",  strtotime($WarrantyEnd));
                                                                }else{
                                                                    echo "-";
                                                                }
                                                            ?>
                                                        </p>
                                                    </td>
                                                    <td width="50%">
                                                        <p class="dv-title">Warranty status</p>
                                                        <p class="dv-feature warranty-status">
                                                            <?php echo $deviceRepository->getWarrantyStatus($daysLeft); ?>
                                                        </p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div style="clear:left"></div>
                                    </div>


                                </div>

                            </div>
                        </div>
                        <div class="tab-pane" id="tab2">
                            <div class="tab-wrapper">
                                <table class="table-setting">
                                    <caption><i style="margin-right:5px" class="glyphicon glyphicon-wrench"></i><span>Settings</span>
                                    </caption>
                                    <tbody>
                                        <tr>
                                            <td width="50%">
                                                User
                                            </td>
                                            <td width="50%">
                                                <?php echo $device->getOwnerEmail() ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="50%">
                                                Organization
                                            </td>
                                            <td id="device_organization" width="50%">
                                                <?php echo $device->getOrganization(); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="50%">
                                                Location
                                            </td>
                                            <td id="device_location" width="50%">
                                                <?php echo $device->getLocation(); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="50%">
                                                Purchase date
                                            </td>
                                            <td id="device_purchase" width="50%">
                                                <?php 
                                                    $curPurchaseDate = $device->getPurchaseDate();
                                                    if(!empty($curPurchaseDate)){
                                                        echo date("Y-m-d",  strtotime($curPurchaseDate));
                                                    }else{
                                                        echo "-";
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="50%">
                                                Warranty ends
                                            </td>
                                            <td id="device_warranty" width="50%">
                                                <?php 
                                                    $curWarrantyEnd = $device->getWarrantyEnd();
                                                    if(!empty($curWarrantyEnd)){
                                                        echo date("Y-m-d",  strtotime($curWarrantyEnd));
                                                    }else{
                                                        echo "-";
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div>
                                    <button onclick="call_edit_tag();" type="button" class="btn green">Edit</button>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab3">
                            <div class="tab-wrapper">
                                <table class="table-app">
                                    <caption>
                                        <i style="margin-right:5px" class="fa fa-archive"></i>
                                        <span>Applications</span>
                                        <span class="pull-right">
                                            Updated: <?php if ($lastAppUpdated !== FALSE) include_partial("global/display_local_time", array('currentDate' => $lastAppUpdated,'fullTime' => false)); else echo "N/A";?>
                                        </span>
                                    </caption>
                                    <thead>
                                        <th>Name</th>
                                        <th>Version</th>
                                        <th>Identifier</th>
                                        <th>Size</th>
                                    </thead>
                                    <tbody>
                                            <?php foreach ($device->getDeviceApplicationInventories() as $key => $app): ?>
                                                    <tr>
                                                    <td><?php echo $app->getName(); ?></td>
                                                <td><?php echo $app->getVersion(); ?></td>
                                                <td><?php echo $app->getIdentifier(); ?></td>
                                                <td><?php echo $app->getSize()." MB"; ?></td>
                                                </tr>
                                            <?php endforeach; ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab4">
                            <div class="tab-wrapper">
                                <table class="table-event">
                                    <caption><i style="margin-right:5px" class="fa fa-calendar"></i><span>Events</span>
                                    </caption>
                                    <tbody>
                                        <?php
                                        $event_statuses = \sfConfig::get("app_event_status_data");
                                        foreach ($deviceEvent as $value): ?>
                                            <tr>
                                                <td>
                                                    <script type="text/javascript">
                                                        display_local_time("<?php echo $value->getUpdatedAt(); ?>", true);
                                                    </script>
                                                </td>
                                                <td><?php echo $value->getEventType(); ?></td>
                                                <td><?php echo $value->getEventNameView(); ?></td>
                                                <td class="event-status-<?php echo(array_search($value->getEventStatusName(), $event_statuses)); ?>">
                                                <?php echo $value->getEventStatusName(); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab5">
                            <div class="tab-wrapper">
                                <div class="checkbox text-right">
                                    <label>
                                        <span>Show:</span>
                                    </label>
                                    <?php foreach ($groups as $key => $group):?>
                                        <label>
                                            <input checked="" onchange="toggleGroup(this)" type="checkbox" value="<?php echo $key; ?>"> <?php echo $key; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <?php
                                // @todo find the best way
                                $icons = array(
                                    "Device" => 'mobile',
                                    "Operator Network" => 'wifi',
                                    "Profiles" => 'cog',
                                    "Security" => 'lock',
                                    "Sim" => 'signal'
                                );
                                $attrSlugIgnore = array(
                                    'encrypted',
                                    'storage_name',
                                    'device_capacity',
                                    'available_device_capacity',
                                    'product_name'
                                );
                                foreach ($groups as $key => $group):
                                $icon = !empty($icons[$key]) ? $icons[$key] : 'phone';
                                ?>
                                <table id="group_<?php echo $key ?>" class="table-event">
                                    <caption>
                                        <i style="margin-right:5px" class="fa fa-<?php echo $icon;?>"></i>
                                        <span><?php echo $key; ?></span>
                                        <span class="pull-right">Updated: 
                                            
                                            <?php 
                                                $updatedTime = $deviceRepository->getUpdateTimeOfGroup($key);
                                                if($updatedTime != "N/A"){
                                                    include_partial("global/display_local_time", array('currentDate' => $updatedTime,'fullTime' => false));
                                                }else{
                                                    echo $updatedTime;
                                                }
                                            ?>
                                        </span>
                                    </caption>
                                    <tbody>
                                        <?php foreach ($group as $gid => $attr):?>
                                            <?php if(!in_array($attr['slug'], $attrSlugIgnore)): ?>
                                            <tr>
                                                <td width="50%">
                                                    <?php echo $attr['name']; ?>
                                                </td>
                                                <td width="50%">
                                                    <?php if($attr['slug'] == 'storage_free_total'): ?>
                                                        <?php echo $free."/".$total;//line 164 ?>
                                                    <?php else :?>
                                                        <?php echo $deviceRepository->getValueByAttributeName($attr['slug']); ?>
                                                    <?php endif;?>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php endforeach; ?>
                            </div>

                        </div>
                        <div class="tab-pane" id="tab6">
                            <div class="tab-wrapper">
                                <table class="table-setting">
                                    <caption><i style="margin-right:5px" class="fa fa-history"></i><span>Last Known Location</span>
                                    </caption>
                                    <tbody>
                                        <tr>
                                            <td width="50%">Longitude</td>
                                            <td id="longitude">
                                                <?php echo $location->getValueByAttributeName('longitude'); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="50%">Latitude</td>
                                            <td id="latitude">
                                                <?php echo $location->getValueByAttributeName('latitude'); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-wrapper">
                                <table class="table-setting">
                                    <caption class="caption-location">
                                        <i style="margin-right:5px" class="fa fa-map-marker"></i>
                                        <span>Device Location</span>
                                        <button id="locate_device" type="button" onclick="locateDevice(<?php echo $device->id; ?>, this);" class="btn green btn-locate">
                                            <span class="loading "><i class="fa-spin fa fa-refresh"></i>&nbsp;</span>
                                            Locate My Device
                                        </button>
                                    </caption>
                                    <tbody>
                                        <tr>
                                            <td style="padding-top: 15px;">
                                                <div id="map-canvas" style="height:500px;"></div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="clear:left"></div>
            </div>
        </div>

    </div>

<?php include_partial("global/edit_tag_modal", array('device' => $device, 'error' => $error));?>

</div>

<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core componets
        Layout.init(); // init layout
        QuickSidebar.init(); // init quick sidebar

        Tasks.initDashboardWidget();
        $('.select-picker').selectpicker();
        $(".event-status-3").css('color', 'red');   // Change color error event
    });
</script>

<script>

    function toggleGroup(el)
    {
        var divElement = $(el).val();
        var temp = "group_" + divElement;
        // If id have space, add backslash before
        var groupId = "[id='" + temp.replace(/([ #;&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1') + "']";
        if ($(el).is(':checked'))
        {
            $(groupId).show();
        }
        else
        {
            $(groupId).hide();
        }
    }
</script>

<script>
    var plot = null;
    var flagPlot = false;
    $(document).ready(function(){
        // Apply datatable
        var table = $('#tab3 .table-app').DataTable({columnDefs: [
                {type: 'sort-numbers-ignore-text', targets: 3}
            ]});
        // Apply donut
        donutContainer();
        jQuery.extend(jQuery.fn.dataTableExt.oSort, {
            "sort-numbers-ignore-text-asc": function(a, b) {
                return sortNumbersIgnoreText(a, b, Number.POSITIVE_INFINITY);
            },
            "sort-numbers-ignore-text-desc": function(a, b) {
                return sortNumbersIgnoreText(a, b, Number.NEGATIVE_INFINITY) * -1;
            }
        });
        $("a[href=#tab1]").click(function(){
            if(flagPlot == true){
                flagPlot = false;    
                setTimeout(donutContainer,200);
            }
        });
    });
    function sortNumbersIgnoreText(a, b, high) {
        var reg = /[+-]?((\d+(\.\d*)?)|\.\d+)([eE][+-]?[0-9]+)?/;
        a = a.match(reg);
        a = a !== null ? parseFloat(a[0]) : high;
        b = b.match(reg);
        b = b !== null ? parseFloat(b[0]) : high;
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    }
    function donutContainer(){
        if(plot){
            plot.destroy();
        }
        $('.donut-container').each(function() {
            var self = jQuery(this);
            // Read data from data attribute
            var value = Math.round(self.attr("data"));
            var s1 = [['a', value], ['b', 100 - value]];
            plot = $.jqplot(self.attr('id'), [s1], {
                grid: {
                    shadow: false,
                    borderWidth: false,
                    background: 'transparent'
                },
                seriesColors: ['#26a69a', '#cccccc'],
                seriesDefaults: {
                    padding: 0,
                    shadow: false,
                    showMarker: false,
                    // make this a donut chart.
                    renderer: $.jqplot.DonutRenderer,
                    rendererOptions: {
                        fill: true,
                        // Donut's can be cut into slices like pies.
                        sliceMargin: 3,
                        // Pies and donuts can start at any arbitrary angle.
                        startAngle: -90,
                        showDataLabels: false,
                        // By default, data labels show the percentage of the donut/pie.
                        // You can show the data 'value' or data 'label' instead.
                        dataLabels: 'value',
                        padding: 0
                    }
                }
            });
        });
    }
</script>
<script>
    var flagInit = true;
    var marker;
    var map;
    var longitude = <?php echo ($location->getValueByAttributeName('longitude') != '-') ? $location->getValueByAttributeName('longitude') : 0; ?>;
    var latitude = <?php echo ($location->getValueByAttributeName('latitude') != '-') ? $location->getValueByAttributeName('latitude') : 0; ?>;
    var myLatlng;
    function initialize() {
      if(flagInit){
            myLatlng = new google.maps.LatLng(latitude,longitude);
            var mapOptions = {
                  zoom: 16,
                  center: myLatlng,
                  mapTypeId: google.maps.MapTypeId.ROADMAP
            }
             map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

             marker = new google.maps.Marker({
                    position: myLatlng,
                    map: map,
                    title: 'Your Device here!'
            });
            flagInit = false;
      }
    }
    
    function locateDevice(device_id, btn){
        var loading = $(btn).find(".loading").first();
        if (loading.is(':visible'))
            return false;
        loading.show();
        jQuery.ajax({
            url: '<?php echo url_for('ajax_locate_device', array('sf_format' => 'json')); ?>',
            type: 'GET',
            dataType: 'json',
            data: {id: device_id},
            success: function(data) {
                var longitude = data.data['longitude'];
                var latitude = data.data['latitude'];
                $("#longitude").text(longitude);
                $("#latitude").text(latitude);
                if(longitude != '-' && latitude != '-'){
                    var myLatlng = new google.maps.LatLng(latitude,longitude);
                    map.setCenter(myLatlng);
                    map.setZoom(16);
                    marker.setPosition(myLatlng);
                }
                // Wake Windows phone app
                $.ajax({
                    url: '<?php echo url_for('ajax_action_device_task', array('sf_format' => 'json')); ?>',
                    data: {'id': <?php echo $device->getId(); ?>, 'action_type': 'get_latest_location',
                            'platform': <?php echo $device->getPlatform(); ?>,
                            'user_id': <?php echo $sf_user->getDecorator()->getUserId() ?>},
                    dataType: 'json',
                    success: function (data) {
                        loading.hide();
                    }
                });
            },
            error: function(){
                //loading.hide();
            }
        });
    }
    
    var divid = document.getElementById('locationDevice');
    google.maps.event.addDomListener(divid, 'click', initialize);
</script>

<script>
    function delete_profile(profileId, deviceId, platform, configType) {
        console.log(profileId + " " + deviceId + " " + platform + " " + configType);
        my_confirm("<?php echo $confirm['C1002'] ?>", function() {
            $.ajax({
                url: '<?php echo url_for('ajax_action_device_task', array('sf_format' => 'json')); ?>',
                type: 'POST',
                dataType: 'json',
                data: {'profileId': profileId, 'id': deviceId, 'user_id': <?php echo $sf_user->getDecorator()->getUserId() ?>,
                        "platform": platform, "profileType": configType, "action_type": "remove_profile"},
                success: function(data) {
                    console.log(data);
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
                        my_alert(data.msg, null, function() {
                            window.location.reload();
                        });
                    }
                },
                error: function() {
                    console.log("Error");
                }
            });
        });
    }
</script>

<script>
    // Call edit tag from modal
    function call_edit_tag()    {
        jQuery('body').trigger('load_data_to_modal');
    }
</script>