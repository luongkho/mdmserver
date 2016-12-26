<?php
use_stylesheet('jquery.jqplot.css'); 

use_javascript('jquery.flot.min.js') ;
use_javascript('jquery.flot.resize.min.js') ;
use_javascript('jquery.flot.categories.min.js') ;
use_javascript('jquery.pulsate.min.js') ;

use_javascript('metronic.js') ;
use_javascript('layout.js') ;
use_javascript('quick-sidebar.js') ;
use_javascript('jquery.easypiechart.min.js') ;

use_javascript('lib/datatables/js/jquery.dataTables.min.js');
use_javascript('lib/datatables/js/dataTables.bootstrap.js');

use_javascript('jquery.jqplot.js') ;
use_javascript('lib/jqplot/jqplot.barRenderer.min.js'); 
use_javascript('lib/jqplot/jqplot.categoryAxisRenderer.min.js'); 
use_javascript('lib/jqplot/jqplot.pointLabels.min.js'); 
use_javascript('lib/jqplot/jqplot.pieRenderer.min.js'); 
use_javascript('lib/jqplot/jqplot.donutRenderer.min.js'); 

use_javascript('tasks.js') ;
use_javascript('bootstrap-select.min.js') ;    
?>
<div class="row">
    <div class="col-xs-12">
        <div class="actionarea">
            <div class="RadAjaxPanel" id="" style="display: block;">
                <div class="wrapper-panel" id="">
                    <h3 class="page-title">
                        Dashboard
                    </h3>                    
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i style="font-size: 18px" class="fa fa-tasks"></i>Count devices by platform
                            </div>
                        </div>
                        <div class="portlet-body" id="blockui_sample_1_portlet_body">
                            <div class="row">
                                <div class="col-xs-4">
                                    <div class="dashboard-stat">
                                        <div class="visual-x">
                                            <i class="fa fa-android"></i>
                                        </div>
                                        <div class="details-x">
                                            <div class="number">
                                                 <?php echo $percentTotalAndroid?>%
                                            </div>
                                            <div class="desc">
                                                <?php echo $totalAndroid['count']?> devices
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="dashboard-stat">
                                        <div class="visual-x">
                                            <i class="fa fa-apple"></i>
                                        </div>
                                        <div class="details-x">
                                            <div class="number">
                                                <?php echo $percentTotalIOS?>%
                                            </div>
                                            <div class="desc">
                                                <?php echo $totalIOS['count']?> devices
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="dashboard-stat">
                                        <div class="visual-x">
                                            <i class="fa fa-windows"></i>
                                        </div>
                                        <div class="details-x">
                                            <div class="number">
                                                <?php echo $percentTotalWP?>%
                                            </div>
                                            <div class="desc">
                                                <?php echo $totalWP['count']?> devices
                                            </div>
                                        </div>

                                    </div>
                                </div>                                                
                            </div>
                            <div id="chart1" style="position: relative;" class="jqplot-target"></div>
                            <div style="clear:both"></div>
                        </div>
                    </div>
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i style="font-size: 18px" class="fa fa-bar-chart"></i>Enrollments by status (Android)
                            </div>
                        </div>
                        <div class="portlet-body" id="blockui_sample_1_portlet_body">
                            <div class="row">
                                <div class="col-xs-6">
                                    <div id="chart2" style="position: relative;" class="jqplot-target"></div>
                                </div>
                                <div class="col-xs-6">
                                    <div id="chart3" style="position: relative;" class="jqplot-target"></div>
                                </div>
                            </div>
                            <div style="clear:both"></div>
                        </div>
                    </div>
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i style="font-size: 18px" class="fa fa-bar-chart"></i>Enrollments by status (iOS)
                            </div>
                        </div>
                        <div class="portlet-body" id="blockui_sample_1_portlet_body">
                            <div class="row">
                                <div class="col-xs-6">
                                    <div id="chart4" style="position: relative;" class="jqplot-target"></div>
                                </div>
                                <div class="col-xs-6">
                                    <div id="chart5" style="position: relative;" class="jqplot-target"></div>
                                </div>
                            </div>
                            <div style="clear:both"></div>
                        </div>
                    </div>
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i style="font-size: 18px" class="fa fa-bar-chart"></i>Enrollments by status (Windows Phone)
                            </div>
                        </div>
                        <div class="portlet-body" id="blockui_sample_1_portlet_body">
                            <div class="row">
                                <div class="col-xs-6">
                                    <div id="chart6" style="position: relative;" class="jqplot-target"></div>
                                </div>
                                <div class="col-xs-6">
                                    <div id="chart7" style="position: relative;" class="jqplot-target"></div>
                                </div>
                            </div>
                            <div style="clear:both"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE LEVEL SCRIPTS -->
<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core componets
        Layout.init(); // init layout
        QuickSidebar.init(); // init quick sidebar        

        Tasks.initDashboardWidget();
    });
    </script>

    <!-- BARCHART TOTAL DEVICES of EVERY PLATFORM -->
    <script>
   /* $(document).ready(function() {
        var s1 = [222];
        var s2 = [460];
        var s3 = [369];
        // Can specify a custom tick Array.
        // Ticks should match up one for each y value (category) in the series.
        var ticks = [''];

        var plot1 = $.jqplot('chart1', [s1, s2, s3], {
            // The "seriesDefaults" option is an options object that will
            // be applied to all series in the chart.

            captureRightClick: true,
            seriesDefaults: {
                renderer: $.jqplot.BarRenderer,
                rendererOptions: {
                    fillToZero: true
                },
                markerOptions: {
                    size: 20,
                },
                pointLabels: {
                    show: true
                },
            },
            // Custom labels for the series are specified with the "label"
            // option on the series option.  Here a series option object
            // is specified for each series.
            series: [{
                label: 'iOS devices',
                color: '#ff89b5'
            }, {
                label: 'Android devices',
                color: '#71e096'
            }, {
                label: 'Windows Phone devices',
                color: '#bb96ff'
            }],
            // Show the legend and put it outside the grid, but inside the
            // plot container, shrinking the grid to accomodate the legend.
            // A value of "outside" would not shrink the grid and allow
            // the legend to overflow the container.
            legend: {
                show: true,
                placement: 'outsideGrid',
                location: 'e',
            },
            axes: {
                // Use a category axis on the x axis and use our custom ticks.
                xaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                    ticks: ticks
                },
                // Pad the y axis just a little so bars can get close to, but
                // not touch, the grid boundaries.  1.2 is the default padding.
                yaxis: {
                    pad: 1.05,
                    tickOptions: {
                        formatString: '%d'
                    }
                }
            }
        });
    });*/
    </script>

    <!-- ANDROID CHART -->
    <script>
    $(document).ready(function() {
        // For horizontal bar charts, x an y values must will be "flipped"
        // from their vertical bar counterpart.
        var plot2 = $.jqplot('chart2', [
            [
                [<?php echo $androidDeviceEnrolled['count']?>, ''],

            ],
            [
                [<?php echo $androidDeviceUnEnroll['count']?>, ''],

            ],
            [
                [<?php echo $androidDeviceReEnroll['count']?>, ''],
            ],
        ], {
            seriesDefaults: {
                renderer: $.jqplot.BarRenderer,
                // Show point labels to the right ('e'ast) of each bar.
                // edgeTolerance of -15 allows labels flow outside the grid
                // up to 15 pixels.  If they flow out more than that, they 
                // will be hidden.
                pointLabels: {
                    show: true,
                    location: 'e',
                    edgeTolerance: -15
                },
//                // Rotate the bar shadow as if bar is lit from top right.
                shadowAngle: 135,
                // Here's where we tell the chart it is oriented horizontally.
                rendererOptions: {
                    barDirection: 'horizontal'
                }
            },
            series: [{
                label: 'Enroll devices',
            }, {
                label: 'Unenroll devices',
            }, {
                label: 'Wait for re-enroll devices',
            }],
            /*legend: {
                show: true,
                location: 'e',
                placement: 'outsideGrid',
            },*/
            axes: {
                yaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                },
                xaxis: {
                   min:0,
                   <?php if ($totalIOS['count'] < 15)   echo "tickInterval: 1,"; ?>
                   tickOptions: { 
                        formatString: '%d' 
                   },
                }
            }
        });
    });
    </script>
    <script>
    $(document).ready(function() {
        var data = [
            ['Enroll devices', <?php echo $androidDeviceEnrolled['count']?>],
            ['Unenroll devices', <?php echo $androidDeviceUnEnroll['count']?>],
            ['Wait for re-enroll devices', <?php echo $androidDeviceReEnroll['count']?>],
        ];
        var plot1 = jQuery.jqplot('chart3', [data], {
            seriesDefaults: {
                // Make this a pie chart.
                renderer: jQuery.jqplot.PieRenderer,

                rendererOptions: {
                    // Put data labels on the pie slices.
                    // By default, labels show the percentage of the slice.
                    showDataLabels: true,
                    dataLabels: ['<?php echo $percentandroidDeviceEnrolled?>%', '<?php echo $percentandroidDeviceUnEnroll?>%','<?php echo $percentandroidDeviceReEnroll?>%'],
                }
            },
            legend: {
                show: true,
                location: 'e',
                placement: 'outsideGrid',
            }
        });
    });
    </script>

    <!-- iOS CHART -->
    <script>
    $(document).ready(function() {
        // For horizontal bar charts, x an y values must will be "flipped"
        // from their vertical bar counterpart.
        var plot2 = $.jqplot('chart4', [
            [
                [<?php echo $iosDeviceEnrolled['count']?>, ''],

            ],
            [
                [<?php echo $iosDeviceUnEnroll['count']?>, ''],

            ],
            [
                [<?php echo $iosDeviceReEnroll['count']?>, ''],
            ],
        ], {
            seriesDefaults: {
                renderer: $.jqplot.BarRenderer,
                // Show point labels to the right ('e'ast) of each bar.
                // edgeTolerance of -15 allows labels flow outside the grid
                // up to 15 pixels.  If they flow out more than that, they 
                // will be hidden.
                pointLabels: {
                    show: true,
                    location: 'e',
                    edgeTolerance: -15
                },
                // Rotate the bar shadow as if bar is lit from top right.
                shadowAngle: 135,
                // Here's where we tell the chart it is oriented horizontally.
                rendererOptions: {
                    barDirection: 'horizontal',                                   
                }
            },            
            series: [{
                label: 'Enroll devices',
            }, {
                label: 'Unenroll devices',
            }, {
                label: 'Wait for re-enroll devices',
            }],
            /*legend: {
                show: true,
                location: 'e',
                placement: 'outsideGrid',
            },*/
            axes: {
                yaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                },
                xaxis: {
                   min:0,
                   <?php if ($totalIOS['count'] < 15)   echo "tickInterval: 1,"; ?>
                   tickOptions: { 
                        formatString: '%d' 
                   }
                }
            }
        });
    });
    </script>
    <script>
    $(document).ready(function() {
        var data = [
            ['Enroll devices', <?php echo $iosDeviceEnrolled['count']?>],
            ['Unenroll devices', <?php echo $iosDeviceUnEnroll['count']?>],
            ['Wait for re-enroll devices', <?php echo $iosDeviceReEnroll['count']?>],
        ];
        var plot1 = jQuery.jqplot('chart5', [data], {
            seriesDefaults: {
                // Make this a pie chart.
                renderer: jQuery.jqplot.PieRenderer,

                rendererOptions: {
                    // Put data labels on the pie slices.
                    // By default, labels show the percentage of the slice.
                    showDataLabels: true,
                    dataLabels: ['<?php echo $percentiosDeviceEnrolled?>%', '<?php echo $percentiosDeviceUnEnroll?>%','<?php echo $percentiosDeviceReEnroll?>%'],
                }
            },
            legend: {
                show: true,
                location: 'e',
                placement: 'outsideGrid',
            }
        });
    });
    </script>

    <!-- WindowsPhone CHART -->
    <script>
    $(document).ready(function() {
        // For horizontal bar charts, x an y values must will be "flipped"
        // from their vertical bar counterpart.
        var plot2 = $.jqplot('chart6', [
            [
                [<?php echo $wpDeviceEnrolled['count']?>, ''],

            ],
            [
                [<?php echo $wpDeviceUnEnroll['count']?>, ''],

            ],
            [
                [<?php echo $wpDeviceReEnroll['count']?>, ''],
            ],
        ], {
            seriesDefaults: {
                padding: 0,
                renderer: $.jqplot.BarRenderer,
                // Show point labels to the right ('e'ast) of each bar.
                // edgeTolerance of -15 allows labels flow outside the grid
                // up to 15 pixels.  If they flow out more than that, they 
                // will be hidden.
                pointLabels: {
                    show: true,
                    location: 'e',
                    edgeTolerance: -15
                },
                // Rotate the bar shadow as if bar is lit from top right.
                shadowAngle: 135,
                // Here's where we tell the chart it is oriented horizontally.
                rendererOptions: {
                    barDirection: 'horizontal',                  
                }
            },
            series: [{
                label: 'Enroll devices',
            }, {
                label: 'Unenroll devices',
            }, {
                label: 'Wait for re-enroll devices',
            }],            
            axes: {
                yaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                },  
                xaxis: {
                   min:0,
                   <?php if ($totalWP['count'] < 15)   echo 'tickInterval: 1,' ?>
                   tickOptions: { 
                        formatString: '%d' 
                   }
                }
            }
        });
    });
    </script>
    <script>
    $(document).ready(function() {
        var data = [
            ['Enroll devices', <?php echo $wpDeviceEnrolled['count']?>],
            ['Unenroll devices', <?php echo $wpDeviceUnEnroll['count']?>],
            ['Wait for re-enroll devices', <?php echo $wpDeviceReEnroll['count']?>],
        ];
        var plot1 = jQuery.jqplot('chart7', [data], {
            seriesDefaults: {
                // Make this a pie chart.
                renderer: jQuery.jqplot.PieRenderer,

                rendererOptions: {
                    // Put data labels on the pie slices.
                    // By default, labels show the percentage of the slice.
                    showDataLabels: true,
                    dataLabels: ['<?php echo $percentwpDeviceEnrolled?>%', '<?php echo $percentwpDeviceUnEnroll?>%','<?php echo $percentwpDeviceReEnroll?>%'],
                }
            },
            legend: {
                show: true,
                location: 'e',
                placement: 'outsideGrid',
            }
        });
    });
    </script>

    <!-- END JAVASCRIPTS -->