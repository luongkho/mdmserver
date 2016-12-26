<?php use_helper('I18N') ?>
<?php
use_stylesheet('login3.css');
use_stylesheet('select2.css');
use_stylesheet('components.css');
use_stylesheet('plugins.css');
use_stylesheet('layout.css');
use_stylesheet('darkblue.css');
use_stylesheet('custom.css');
use_javascript('metronic.js');
use_javascript('jquery.min.js');
 ?>

<style>
    h2  {
        text-align: center;
        color: #fff;
    }
    .expired    {
        color: lightcoral;
    }
</style>

<div class="logo" style="margin: 180px auto">
    <a href="#">
        <img style="max-width:360px" src="/img/logo-login.png" alt="">
    </a>
    <h2 class="<?php if ($status == 1) echo "expired"?>" ><?php echo $result; ?></h2>
</div>
