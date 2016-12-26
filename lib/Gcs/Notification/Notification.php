<?php

/**
 * Description: Base class Notification for 3 platform
 * Getting message and push to Notification service
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Notification;

class Notification {

    protected $type; //type can be "ios" or "android"

    //protected $tokens; //Contain device tokens used to send push notification.
    /**
     * Init function
     */
    public function __construct() {
        
    }

    /**
     * General and push message to Notification service
     * @param String $regIds
     * @param String $pushMagic
     * @return String
     */
    public function sendNotification($regIds, $pushMagic) {
//		$tokens = retriveTokens();
        $mes     = array(
            'Mdm' => 'Notification',
        );
        $message = json_encode($mes);
        $result  = $this->sendMessage($message, $regIds, $pushMagic);
        return $result;
    }

    /**
     * Get All tokens of device to send push notification message
     * Base on type "android, ios" to get devices and then get token from device.
     */
    protected function retriveTokens() {
        $devices = \DeviceInventoryTable::model()->findAll(array(
            'condition' => 'type=:type',
            'params'    => array(':type' => $this->type)
        ));

        $deviceTokens = array();

        foreach ($devices as $device) {
            array_push($deviceTokens, $device->token);
        }

        return $deviceTokens;
    }

    /**
     * Send push message to all devices have registed token
     * @param string $message
     * 	Message to send
     *
     * @param array $tokens
     * 	List tokens of devices
     *
     * This is empty function, will be orrided by inheritance classes
     */
    protected function sendMessage($message, array $tokens, $pushMagic) {
        
    }

}
