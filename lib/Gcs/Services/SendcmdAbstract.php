<?php

/**
 * Description: Abstract class for push notification service
 *  Base class for 3 platform: Android, IOS and Windows phone
 *  Abstract function send command for 3 platform
 *  Getting command error for 3 platform
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Services;

abstract class SendcmdAbstract {
    
    const UN_ENROLL = "E4004";

    //put your code here
    abstract protected function sendCmd($controller, $request, $log);

    /**
     * build command error for response to GUI.
     * @param  [array] $response, [string] $error
     * @return [array] $response
     */
    public function buildResponse($response, $error) {
        if ($error == self::UN_ENROLL) {
            \MDMLogger::getInstance()->info('', $error, array());
        } else {
            \MDMLogger::getInstance()->error('', $error, array());
        }
        return $response = array("error" => array("status" => 1, "msg" => $error), "data" => array());
    }

}
