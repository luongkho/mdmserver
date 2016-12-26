<?php
/**
 * Created by PhpStorm.
 * User: longtm
 * Date: 5/22/2015
 * Time: 3:33 PM.
 */
?>

<!--<div class="dv-container-right">-->
    <?php
        // empty(0) == true
        $non_enroll_class = !empty($status) ? 'disabled' : '';
        $allow_enroll_class = !empty($status) && $status == 2 ? 'disabled' : '';
    ?>

    <div class="dropdown action-btn device-actions">
        <button class="btn btn-default <?php echo $non_enroll_class; ?>" type="button" id="get_log_information"
                rel="get_log_information">
            Get Log Information
        </button>
        
        <button class="btn btn-default dropdown-toggle" type="button" id="btn-ac" data-toggle="dropdown"
                aria-expanded="true">
            <i class="fa fa-cogs"></i>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right device-action-list" role="menu" aria-labelledby="btn-ac">
            <li role="presentation" class="media <?php echo $non_enroll_class; ?>"><a role="menuitem" tabindex="-1" rel="unenroll_device"><i
                        class="fa fa-chain-broken" style="margin-right:5px;"></i>Unenroll device</a>
            </li>
            <li role="presentation" class="media <?php echo $allow_enroll_class; ?>"><a role="menuitem" tabindex="-1" rel="allow_reenroll_device"><i
                        class="fa fa-chain" style="margin-right:5px;"></i>Allow re-enroll device</a>
            </li>
            <li role="presentation" class="media <?php echo $non_enroll_class; ?>"><a role="menuitem" tabindex="-1" rel="lock_device"><i
                        class="fa fa-lock" style="margin-right:5px;"></i>Lock device</a>
            </li>
            <li role="presentation" class="media <?php echo $non_enroll_class; ?>"><a role="menuitem" tabindex="-1" rel="wipe_device"><i
                        class="fa fa-fire" style="margin-right:5px;"></i>Device Linkup Data Removal</a>
            </li>
            <li role="presentation" class="media <?php echo $non_enroll_class; ?>"><a role="menuitem" tabindex="-1" rel="reset_passcode"><i
                        class="fa fa-retweet" style="margin-right:5px;"></i>Reset passcode</a>
            </li>

        </ul>
    </div>
    <div style="clear:both"></div>
<!--</div>-->
<script>
    <?php $ajaxUrl = url_for('ajax_action_device_task', array('sf_format' => 'json', 'id' => isset($deviceId) ? $deviceId : '')); ?>

    $(document).ready(function () {
        $("#get_log_information").click(function()  {
           var rel = $(this).attr('rel');
           ajax_action(this, rel);
        });
        
        $('.device-action-list .media').click(function () {
            var rel = $(this).find('a').attr('rel');
            ajax_action(this, rel);
        });
    });
    
    function ajax_action(element, type_action)  {
        var self = $(element);
        if(self.hasClass('disabled')) return; // Non-accept this action
        /* Confirm dialog */
        var confirm_msg = "<?php echo $confirm['C2005'] ?>";
        switch (type_action) {
            case 'get_log_information':
                confirm_msg = "<?php echo $confirm['C2002'] ?>";
                break;
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
//                confirm_msg = "Are you sure to " + type_action + " this device?";
                break;
        }
        console.log(type_action);
        my_confirm(confirm_msg, function () {
            console.log("Enter ajax");
            /* Call ajax if user confirm */
            $.ajax({
                url: '<?php echo $ajaxUrl;?>',
                data: {'action_type': type_action,
                        'platform': <?php echo $platform; ?>,
                        'user_id': <?php echo $sf_user->getDecorator()->getUserId() ?>},
                dataType: 'json',
                success: function (data) {
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
                error: function (data) {
                    console.log("error function");
                }
            });
        });
    }
</script>
