<?php
/**
 * Created by PhpStorm.
 * User: longtm
 * Date: 5/21/2015
 * Time: 4:50 PM
 */
?>
<ul class="page-breadcrumb">

    <li>
        <i class="fa fa-home"></i>
        <a href="<?php echo url_for('/')?>">Home</a>
    </li>
<?php
$module_name = array(
    "login" => "Login",
    "signin" => "Sign in",
    "logout" => "Logout",
    "device" => "Devices",
    "dashboard" => "Dashboard",
    "enroll" => "Provision Device Linkup",
    "profile" => "Configuration Profiles",
    "events" => "Activity Log",
    "user_management" => "Users",
    "manage_templates" => "Manage Templates",
    "location_management" => "Locations",
    "device_linkup" => "Device Linkup Version"
);
$module = $sf_request->getParameterHolder()->get('module');     // Get current module
$action = $sf_request->getParameterHolder()->get('action');
$route = sfContext::getInstance()->getRouting()->getCurrentRouteName(); // Get current route's name
$breadcrumb = array();

if (!empty($module_name[$module])) {
    $breadcrumb[$module_name[$module]] = url_for($module);
}
if (!empty($module_name[$route])) {
    $breadcrumb[$module_name[$route]] = url_for($route);
}
if (!empty($addition_breadcrumb)) {
    $breadcrumb[$addition_breadcrumb] = '#';
}

if (count($breadcrumb) > 1) {
    array_shift($breadcrumb);
}
foreach ($breadcrumb as $name => $url) {
    echo "<li><i class='fa fa-angle-right'></i><a href='{$url}'>{$name}</a></li>";
}
?>
    </ul>