<?php

/**
 * Description: class router general notification by platform
 * Getting notification by platform
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Notification;

class NotificationCreator {

    /**
     * Create notification (Apple, Google) base on $type paramter.
     *
     * @param string $type
     *                     Notificaiton type, can be "ios, android"
     *
     * @return notification instance of ApplePushNotification or GooglePushNotification.
     */
    public function createNotification($type) {
        $notification = null;
        switch ($type) {
            case 'ios':
                $notification = new ApplePushNotification();
                break;
            case 'android':
                $notification = new GooglePushNotification();
                break;
            case 'iosapp':
                $notification = new AppleAppPushNotification();
                break;
            case 'wp':
            case 'wpapp':
                $notification = new WindowsPhonePushNotification();
                break;
            default:
                break;
        }
//        if ($type === 'ios') {
//            $notification = new ApplePushNotification();
//        } elseif ($type === 'android') {
//            $notification = new GooglePushNotification();
//        }
        return $notification;
    }

}
