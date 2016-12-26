<?php use_helper('I18N') ?>

<div class="content">
     <?php if ($sf_user->hasFlash('notice')): ?>
      <div class="flash_notice">
        <?php echo $sf_user->getFlash('notice') ?>
      </div>
    <?php endif ?>

    <?php if ($sf_user->hasFlash('error')): ?>
    <div class="flash-error alert alert-danger" style="padding: 10px !important">
        <?php
        if (is_array($sf_user->getFlash('error')) || is_object($sf_user->getFlash('error'))) {
            foreach ($sf_user->getFlash('error') as $message) {
                echo "<p>" . $message . "</p>";
            }
        } else {
            echo $sf_user->getFlash('error');
        }
        ?>
    </div>
    <?php endif ?>
    <!-- BEGIN LOGIN FORM -->
    <form class="login-form" action="<?php echo url_for('@signin') ?>" name="sf_signin" id="sf_signin" method="post" novalidate="novalidate">
        <h3 class="form-title">Login to MDM system</h3>
        <div class="alert alert-danger display-hide">
            <button class="close" data-close="alert"></button>
            <span>
  Please enter username and password. </span>
        </div>
        <div class="form-group">
            <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
            <label class="control-label visible-ie8 visible-ie9">Username</label>
            <div class="input-icon">
                <i class="fa fa-user"></i>
                <input class="form-control placeholder-no-fix" id="signin_username" type="text" autocomplete="off" placeholder="Username" name="username">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9">Password</label>
            <div class="input-icon">
                <i class="fa fa-lock"></i>
                <input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="Password" id="signin_password" name="password">
            </div>
        </div>
        <div class="form-actions">
            <div class="checkbox">
                <!-- <label>
                    <input type="checkbox" name="signin[remember]" id="signin_remember"> Remember me
                </label> -->
            </div>
            <button type="submit" class="btn green-haze pull-right">
                Login <i class="m-icon-swapright m-icon-white"></i>
            </button>
        </div>
    </form>
    <!-- END LOGIN FORM -->
</div>
