<?php

/**
 * Description: class for Windows Phone service
 * Init command and push command to Windows Notification Service.
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */


namespace Gcs\Services;

use Gcs\Services\SendcmdAbstract;
use Gcs\Repository\ConfigRepository;
use Gcs\Repository\TemplateRepository;
use Gcs\Repository\DeviceEventRepository;
use Gcs\Repository\DeviceRepository;
use Gcs\Repository\EnrollWPRepository;

class SendCmdWindowsPhone extends SendcmdAbstract
{

    const PUSH_FAIL             = "E4002";
    const EMAIL_FAIL_MSG        = "E3002";
    const NOT_EXIST_PASSCODE_MSG = "E4003";
    const EMAIL_FAIL            = "2010";
    const NOT_EXIST_PASSCODE    = "2011";
    const SUCCESS               = "N1001";
    const PUSH_FAIL_CODE        = "2001";
    const COMMAND_ERROR         = 3;
    const COMMAND_READY         = 0;
    const COMMAND_COMPLETED     = 2;
    const WINDOWSPHONE_PLATFORM = "wp";
    const WINDOWSPHONE_APP_PLATFORM = "wpapp";
    const UNLOCK_WINDOWS_PHONE  = "unlock_wp";
    const DEVICE_UNLOCK         = "DeviceUnlock";
    const UNENROLL              = "Unenroll";
    const UNENROLL_APP          = "UnenrollApp";
    const PUSH_HTTP_CODE_SUCCESS = 200;

    public function sendCmd($controller, $request, $log)
    {

        $command = $request->getParameter('command');
        $deviceData = $request->getParameter('deviceId');
        $response = array();

// change deviceId from string to array.
        $deviceIds = explode(",", $deviceData);
        $deviceEventModel = new DeviceEventRepository();

// query to get devices based on device_ids 
        $deviceRep = new DeviceRepository();
        $deviceArray = $deviceRep->getDeviceList($deviceIds);

        foreach ($deviceArray as $device) {
            $deviceId = $device->getId();

            // device is unenrolled.
            if ($device->getEnrollStatus() != 0) {
                $response = $this->buildResponse($response, self::UN_ENROLL);
                break;
            }

            // Send mail unlock
            if ($command == self::DEVICE_UNLOCK) {
                $response = $this->_unlockCommand($controller, $deviceId, $request);
                break;
            }

            // Other command: Save event and push
            // Unenroll: additional work: 
            //          Set device status to 1, all ready and processing event become error
            //          Push for MDM and for App
            //          Clear device data after push
            $config = new ConfigRepository();
            $request_type = $config->getRequestTypeByPlatform(self::WINDOWSPHONE_PLATFORM);
            $request_type_app = $config->getRequestTypeByPlatform(self::WINDOWSPHONE_APP_PLATFORM);
            $enrollRepository = new EnrollWPRepository();
            if ($command == self::UNENROLL) {
                $deviceEventModel->unEnroll($deviceId);
                $enrollRepository->unEnrollWP($device->getUdid());
                $response = $this->_saveCmdAndPush($device, $request, self::UNENROLL, $device->getRegistrationId(), TRUE, $request_type);
                $response = $this->_saveCmdAndPush($device, $request, self::UNENROLL_APP, $device->getDeviceToken(), FALSE, $request_type_app);
                $deviceEventModel->clearRegId($deviceId);
            } else {
                $response = $this->_saveCmdAndPush($device, $request, $command, $device->getRegistrationId(), TRUE, $request_type);
            }
        }

// response message success
        if (empty($response)) {
            $response = array("error" => array("status" => 0, "msg" => ""), "data" => array(), "msg" => self::SUCCESS);
        }
        $log->debug('', __LINE__ . "::::" . json_encode($response), array());
        return $response;
    }

    /**
     * Save device event and push to device
     * @param  [object] $device
     * @param  [object] $request
     * @param  [string] $command
     * @param  [string] $regId
     * @param  [boolean] $typeBool
     * @param  [integer] $request_type
     * @return [array] $response
     */
    private function _saveCmdAndPush($device, $request, $command, $regId, $typeBool, $request_type)
    {
        $deviceEventModel = new DeviceEventRepository();
        $regIds = array();
        $response = NULL;

        $userId = $request->getParameter('userId');
        $profileId = $request->getParameter('profileId');
        $deviceId = $device->getId();

        $commandUUID = $deviceEventModel->saveCommand($deviceId, $command, self::COMMAND_READY, $userId, $profileId, $typeBool, $request_type);

        if ($regId != "") {
            array_push($regIds, $regId);
        }

        $pushResult = $deviceEventModel->pushNotification($regIds, self::WINDOWSPHONE_PLATFORM, NULL);

        if ($pushResult->httpCode != self::PUSH_HTTP_CODE_SUCCESS) {
            $response = $this->buildResponse($response, self::PUSH_FAIL);
            if ($commandUUID != null) {
                $event = \DeviceEventTable::getInstance()->findOneByCommandUuid($commandUUID);
                $deviceEventModel->updateNoteAndStatus($event, self::PUSH_FAIL_CODE, self::COMMAND_ERROR);
            }
        } else {
            $response = array("error" => array("status" => 0, "msg" => ""), "data" => array(), "msg" => self::SUCCESS);
        }
        return $response;
    }

    /**
     * Call send mail and handle response
     * @param  [object] $controller
     * @param integer $deviceId 
     * @return [string] $result          
     */
    private function _unlockCommand($controller, $deviceId, $request)
    {
        $response = array();
        $event = new DeviceEventRepository();
        $config = new ConfigRepository();
        $command = $request->getParameter('command');
        $userId = $request->getParameter('userId');
        $request_type = $config->getRequestTypeByPlatform(self::WINDOWSPHONE_PLATFORM);

        $result = $this->_DeviceUnlock($controller, $deviceId);

        // error happen , error code is return
        if ($result) {
            if ($result == self::EMAIL_FAIL) {
                $message = self::EMAIL_FAIL_MSG;
            }
            if ($result == self::NOT_EXIST_PASSCODE) {
                $message = self::NOT_EXIST_PASSCODE_MSG;
            }
            $event->saveDeviceEvent($deviceId, $command, 1, $result, self::COMMAND_ERROR, $userId);
            $response = $this->buildResponse($response, $message);
        } else {
            $event->saveCommand($deviceId, $command, self::COMMAND_COMPLETED, $userId, null, true, $request_type);
            $response = array("error" => array("status" => 0, "msg" => ""), "data" => array(), "msg" => self::SUCCESS);
        }
        return $response;
    }

    /**
     * Send passcode of device to user
     * @param  [int] $device_id
     * @return [string] $result          
     */
    private function _DeviceUnlock($controller, $device_id)
    {
        $result = null;
        $device = \DeviceInventoryTable::getInstance()->findOneById($device_id);
        if (!$device) {
            return self::NOT_EXIST_PASSCODE;
        }

        $passcode = $device->getPasscode();
        if (!isset($passcode) || trim($passcode) === '') {
            return self::NOT_EXIST_PASSCODE;
        }

        $passcode = base64_decode($passcode);
        $ownerFullName = $device->getOwnerName();
        $userId = $device->getUserId();
        $userInfo = \UserInfoTable::getInstance()->findOneById($userId);
        if ($userInfo) {
            $ownerEmail = $userInfo->getEmail();
            $ownerFullName = $userInfo->getFullName();
        }
        $deviceName = $device->getDeviceName();
        //send unlock email to client
        $email_result = $this->_sendUnlockEmail($controller, $ownerFullName, $deviceName, $passcode, $ownerEmail);

        if (!$email_result) {
            return self::EMAIL_FAIL;
        }
        return $result;
    }

    /**
     * Sending email function
     * @param  [UserInfo] $user,[string] $deviceName,[string] $passcode,[string] $email
     * @return [boolean]          
     */
    private function _sendUnlockEmail($controller, $user, $deviceName, $passcode, $email)
    {
        $from = \sfConfig::get("app_mail_from");
        /* Send the email */
        \sfProjectConfiguration::getActive()->loadHelpers('Partial');
// Get template from database
        $templateRep = new TemplateRepository();
        $template = $templateRep->getMail(self::UNLOCK_WINDOWS_PHONE);
        $html = $template[0]->getContent();
        $subject = $template[0]->getSubject();

        $html = str_replace('${user}', $user, $html);
        $html = str_replace('${deviceName}', $deviceName, $html);
        $html = str_replace('${passcode}', $passcode, $html);

        try {
            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom(array($from['email'] => $from['name']))
                ->setTo($email)
                ->setBody($html, 'text/html');
            $controller->getMailer()->send($message);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

}
