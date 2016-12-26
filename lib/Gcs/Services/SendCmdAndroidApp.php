<?php

/**
 * Description: class for Android service
 * Init command and push command to Google service
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Services;

/**
 * Description of SendCmdAndroidApp
 *
 * @author cuongnd.xt
 */
use Gcs\Services\SendcmdAbstract;
use Gcs\Repository\DeviceEventRepository;
use Gcs\Repository\ConfigRepository;
use Gcs\Repository\TemplateRepository;
use Gcs\Repository\DeviceRepository;

class SendCmdAndroidApp extends SendcmdAbstract {

    const UN_ENROLL              = "E4004";
    const PUSH_FAIL              = "E4002";
    const EMAIL_FAIL_MSG         = "E3002";
    const NOT_EXIST_PASSCODE_MSG = "E4003";
    const EMAIL_FAIL             = "2010";
    const NOT_EXIST_PASSCODE     = "2011";
    const PUSH_FAIL_CODE         = "2001";
    const COMMAND_ERROR          = 3;
    const COMMAND_READY          = 0;
    const COMMAND_COMPLETED      = 2;
    const ANDROID_PLATFORM       = "android";
    const UNLOCK_ANDROID         = "unlock_android";

    public function sendCmd($controller, $request, $log) {
        // get parameters from GUI
        $command    = $request->getParameter('command');
        $deviceData = $request->getParameter('deviceId');
        $userId     = $request->getParameter('userId');
        $profileId  = $request->getParameter('profileId');
        $platform   = self::ANDROID_PLATFORM;

        $configRepository = new ConfigRepository();
        $request_type     = $configRepository->getRequestTypeByPlatform($platform);

        // change deviceId from string to array.
        $deviceIds        = explode(",", $deviceData);
        $deviceEventModel = new DeviceEventRepository();
        // save command to database with status 0
        $regIds           = array();
        $response         = array();
        $commandUUID      = null;
        // query to get devices based on device_ids 
        $deviceRep        = new DeviceRepository();
        $deviceArray      = $deviceRep->getDeviceList($deviceIds);
        
        foreach ($deviceArray as $device) {
            if ($device->getEnrollStatus() == 0) {
                $deviceId  = $device->getId();
                $pushMagic = $device->getPushMagic();
                // change platform when command is install profile and wipedata.
                if ($command == 'DeviceUnlock') {
                    $result = $this->_DeviceUnlock($controller, $deviceId);
                    \MDMLogger::getInstance()->debug('', "RRRRR".print_r($result,true), array());
                    // error happen , error code is return
                    if ($result) {
                        if ($result == self::EMAIL_FAIL) {
                            $message = self::EMAIL_FAIL_MSG;
                        } else if ($result == self::NOT_EXIST_PASSCODE) {
                            $message = self::NOT_EXIST_PASSCODE_MSG;
                        }
                        $response = $this->buildResponse($response, $message);
                        $deviceEventModel->saveDeviceEvent($deviceId, $command, 1, $result, self::COMMAND_ERROR, $userId);
                    } else {
                        $deviceEventModel->saveCommand($deviceId, $command, self::COMMAND_COMPLETED, $userId, null, true, $request_type);
                    }
                } else { // call function unenroll device.
                    if ($command == 'Unenroll') {
                        $deviceEventModel->unEnroll($deviceId);
                    }
                    //$deviceEventModel = new DeviceEventRepository();
                    $commandUUID = $deviceEventModel->saveCommand($deviceId, $command, self::COMMAND_READY, $userId, $profileId, true, $request_type);
                    $regId       = $device->getRegistrationId();
                    if ($regId != "") {
                        array_push($regIds, $regId);
                    }

                    \MDMLogger::getInstance()->debug('', "Flag True::" . __LINE__ . "::::" . $command . "::::" . print_r($regIds, true) . "::::" . $platform . "::::" . $pushMagic, array());
                    $pushResult = $deviceEventModel->pushNotification($regIds, $platform, $pushMagic);
                    if ($pushResult != "") {
                        $response = $this->buildResponse($response, self::PUSH_FAIL);
                        if ($commandUUID != null) {
                            $event = \DeviceEventTable::getInstance()->findOneByCommandUuid($commandUUID);
                            $deviceEventModel->updateNoteAndStatus($event, self::PUSH_FAIL_CODE, self::COMMAND_ERROR);
                        }
                    }
                    if ($command == 'Unenroll') {
                        foreach ($deviceArray as $device) {
                            $deviceId = $device->getId();
                            $deviceEventModel->clearRegId($deviceId);
                        }
                    }
                }
            } else {
                // device is unenrolled.
                $response = $this->buildResponse($response, self::UN_ENROLL);
            }
            $regIds = array();
        }
        return $response;
    }

    /**
     * Send passcode of device to user
     * @param  [int] $device_id
     * @return [string] $result          
     */
    private function _DeviceUnlock($controller, $device_id) {
        $result = null;
        $device = \DeviceInventoryTable::getInstance()->findOneById($device_id);
        if (!$device) {
            return self::NOT_EXIST_PASSCODE;
        }

        $passcode = $device->getPasscode();
        if (!isset($passcode) || trim($passcode) === '') {
            return self::NOT_EXIST_PASSCODE;
        }

        $passcode      = base64_decode($passcode);
        $ownerFullName = $device->getOwnerName();
        $userId        = $device->getUserId();
        $userInfo      = \UserInfoTable::getInstance()->findOneById($userId);
        if ($userInfo) {
            $ownerEmail    = $userInfo->getEmail();
            $ownerFullName = $userInfo->getFullName();
        }
        $deviceName   = $device->getDeviceName();
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
    private function _sendUnlockEmail($controller, $user, $deviceName, $passcode, $email) {
        $from = \sfConfig::get("app_mail_from");
        /* Send the email */
        \sfProjectConfiguration::getActive()->loadHelpers('Partial');

        //Get template from database
        $templateRep = new TemplateRepository();
        $template    = $templateRep->getMail(self::UNLOCK_ANDROID);
        $html        = $template[0]->getContent();
        $subject     = $template[0]->getSubject();

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
