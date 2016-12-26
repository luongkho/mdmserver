<?php
/**
 * Created by PhpStorm.
 * User: longtm
 * Date: 5/21/2015
 * Time: 4:50 PM
 */
?>
<?php
$current_module = $sf_request->getPathInfo();
//$current_module = $sf_request->getParameterHolder()->get('module');
if (preg_match('/\/([^\/]+)/', $current_module, $matches)) {
    $current_module = $matches[1];
}
$menus = \sfConfig::get("menu_left_data");
?>
<div class="page-sidebar-wrapper">
    <div class="page-sidebar navbar-collapse collapse">
        <ul class="page-sidebar-menu " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">
            <?php foreach ($menus as $menu){ ?>
                <li class="heading">
                    <!-- MENU <?php echo $menu['header']; ?> -->
                    <h3 class="uppercase menu-title"><?php echo $menu['header']; ?></h3>
                    <?php if(!empty($menu['submenu'])){ ?>
                        <?php foreach ($menu['submenu'] as $submenu){ ?>
                            <li class="start <?php echo $submenu['name'] == $current_module ? 'active' : ''; ?>">
                                <a href="<?php echo url_for($submenu['name']); ?>">
                                    <span class="title"><?php echo $submenu['title'] ?></span>
                                    <span class="selected"></span>
                                </a>
                            </li>
                        <?php } ?>
                    <?php } ?>
                </li>
            <?php } ?>
        </ul>
        <!-- END SIDEBAR MENU -->
    </div>
</div>
<script>
   
</script>