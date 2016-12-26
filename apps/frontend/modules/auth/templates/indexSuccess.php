<?php use_helper('I18N') ?>
<?php
use_stylesheet('login3.css');
use_stylesheet('select2.css');
use_stylesheet('components.css');
use_stylesheet('plugins.css');
use_stylesheet('layout.css');
use_stylesheet('darkblue.css');
use_stylesheet('custom.css');
 ?>

<div class="logo">
    <a href="#">
        <img style="max-width:360px" src="/img/logo-login.png" alt="">
    </a>
    <h2 style="text-align: center;color: #fff;">Mobile Device and Application Management</h2>
</div>
<div class="menu-toggler sidebar-toggler">
</div>
<?php if ($loggedIn): ?>
	<?php include_partial('logoutPrompt') ?>
	<?php else: ?>
	<?php include_partial('loginPrompt') ?>
<?php endif ?>
<?php
use_javascript('jquery.validate.min.js');
use_javascript('select2.min.js');
use_javascript('metronic.js');
use_javascript('layout.js');
use_javascript('login.js');
use_javascript('page-login.js');

?>
