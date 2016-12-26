<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="/favicon.ico" />
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
  </head>
  <body class="page-header-fixed page-quick-sidebar-over-content page-sidebar-closed-hide-logo page-container-bg-solid page-quick-sidebar-open">
  <div id="ajaxContent"> </div>

      <!-- BEGIN HEADER -->
      <div class="page-header navbar navbar-fixed-top">
          <!-- BEGIN HEADER INNER -->
          <div class="page-header-inner">

              <div class="page-header-inner">

                  <div class="top-menu">
                      <ul class="user-component pull-right">
                        <?php if (!$sf_user->isAuthenticated()): ?>
                                <li>
                                    <a href="<?php echo url_for('login') ?>">
                                        <span>Sign in</span>
                                    </a>
                                </li>
                        <?php else: ?>
                            <li>
                                <!--Not implemented this feature yet-->
<!--                                <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                                    <img alt="" class="img-circle" src="/img/avatar3_small.jpg">
                                    <span class="username username-hide-on-mobile"><?php // echo $sf_user->getDecorator()->fullName(); ?> </span>
                                </a>-->
                                <?php echo $sf_user->getDecorator()->fullName(); ?>
                            </li>
                            <li>
                                <span>|</span></li>
                            <li>
                                <a href="<?php echo url_for('logout'); ?>"><span>Log Out</span></a>
                            </li>
                        <?php endif;?>
                    </li>
                    <!-- END USER LOGIN DROPDOWN -->
                </ul>
            </div>
        </div>
        </div>
        <!-- END HEADER INNER -->
    </div>
    <!-- END HEADER -->
    <div class="clearfix">
    </div>
    <!-- BEGIN CONTAINER -->
    <div class="page-container">
        <!-- BEGIN SIDEBAR -->
        <?php
            // include_slot('side_bar', include_partial('left_sidebar') );
            include_partial('global/left_sidebar');
        ?>
        <!-- END SIDEBAR -->
        <!-- BEGIN CONTENT -->
        <div class="page-content-wrapper">
            <div class="page-content" style="min-height:914px">
                <!-- BEGIN PAGE HEADER-->
                <div class="page-bar">
                    <?php
                        include_partial('global/breadcrumb');
                    ?>
                </div>
                <!-- END PAGE HEADER-->
                <?php if ($sf_user->hasFlash('notice')): ?>
                    <div class="flash_notice bg-success wrapper-panel">
                        <?php echo $sf_user->getFlash('notice') ?>
                    </div>
                <?php endif ?>
                <?php if ($sf_user->hasFlash('error')): ?>
                    <div class="flash_error bg-danger wrapper-panel">
                        <?php echo $sf_user->getFlash('error') ?>
                    </div>
                <?php endif ?>
            <?php 
                echo $sf_content;
            ?>
        </div>
        <!-- END CONTENT -->
    </div>
    </div>
  <script type="text/javascript">
        /* Hide the flash */
        jQuery(document).ready(function(){
            setTimeout(function() {
                jQuery('[class^="flash_"]').slideUp();
                jQuery('[class*=" flash_"]').slideUp();
            }, 10000);
        });
        $(document).ready(function() {
              $.ytLoad();
        });
  </script>

  </body>
</html>
