<?php

/**
 * device actions.
 *
 * @package    mdm-server
 * @subpackage device
 * @author     Dung Huynh
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
use Gcs\Repository\DeviceRepository;
use Gcs\Repository\DeviceEventRepository;
use Gcs\Repository\DeviceOwnerRepository;
use Gcs\Repository\AttributeGroupRepository;
use Gcs\Repository\DeviceLocationRepository;
use Gcs\Repository\DeviceProfileRepository;
use Gcs\Repository\ConfigRepository;
use Gcs\Repository\DeviceApplicationRepository;
use Gcs\Repository\DeviceInventoryRepository;
use Gcs\Repository\DeviceLinkupRepository;
use Gcs\Repository\LocationRepository;
use Gcs\Repository\UserRepository;
use Gcs\Repository\TemplateRepository;

class deviceActions extends sfActions
{
    const IOS_NAME          = 'ios';
    const IOS_APP_NAME      = 'iosapp';
    const WP_APP_NAME       = 'wpapp';
    const UNLOCK_IOS        = "unlock_ios";
    const ACCOUNT_DELETED   = "E107";

    private $notificationMsg, $errorMsg, $confirmMsg;

    public function __construct($context, $moduleName, $actionName)   {
        parent::__construct($context, $moduleName, $actionName);
        $configRep = new ConfigRepository();
        $this->notificationMsg = $configRep->getNotificationMsg();
        $this->errorMsg        = $configRep->getErrorMsg();
        $this->confirmMsg      = $configRep->getConfirmMsg();
    }
    
  /**
   * check user has been logined or not
   * @return [type] [description]
   */
  private function _checkAuth()
  {
    // check login or not
    if (!$this->getUser()->isAuthenticated()) {
      $this->redirect('login');
    }
  }

  /**
   * Executes index action
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    $this->_checkAuth();
    $this->confirm = $this->confirmMsg;
  }

  /**
   * find list device by ajax
   * @param  sfWebRequest $request  A request object
   * @return [type]                [description]
   */
  public function executeAjaxList(sfWebRequest $request)
  {
    $this->getResponse()->setContentType('application/json');

    $device = new DeviceRepository();
    $platform =     $request->getParameter('platform');
    $deviceStatus = $request->getParameter('device_status');
    
    $deviceInventorys = $device->list_all_device($request, $platform, $deviceStatus);
    $result = array();

    if ($request->isXmlHttpRequest()) {
      foreach ($deviceInventorys['result'] as $key => $dv) {
        new DeviceInventory;
        $device->setDeviveInformation($dv->id);
        $result[] = array($dv->id, $this->generateUrl('device_detail', $dv, true), $dv->getEnrollStatusName(), $dv->getDeviceName(), $dv->getVersion(), $dv->getOwnerName(), $dv->getUpdatedAt(), $dv->getImei(), $dv->getWifiMacAddress(), $dv->getPlatform());
      }
    }

    $data_json = json_encode(array(
        "draw" => intval($request->getParameter('draw')),
        'data' => $result,
        "recordsTotal" => $deviceInventorys['count'],
        "recordsFiltered" => $deviceInventorys['count'],
            )
    );
    return $this->renderText($data_json);
  }

  /**
   * [executeActionDevice description]
   * @param  sfWebRequest $request [description]
   * @return [type]                [description]
   */
  public function executeAjaxDeviceTask(sfWebRequest $request)
  {
    $this->getResponse()->setContentType('application/json');
    $result = array('error' => array('status' => 1, 'msg' => ''), 'data' => array());
    $data_json = json_encode(json_decode('{}'));
    if ($request->isXmlHttpRequest()) {
      // Redirect if user has been deleted
      $userId =         $request->getParameter('user_id');
      $userRep = new UserRepository();
      $redirect = $userRep->checkUserAlive($userId);
      if ($redirect['redirect'] != '')  {
          $result['error']['msg']   = $this->errorMsg[$redirect['msg']];
          $result['redirect']       = $redirect['redirect'];
          return $this->renderText(json_encode($result));
      }
      
      // Get data from ajax
      $deviceId = '';
      $action =         $request->getParameter('action_type');
      $devices =        $request->getParameter('id');
      $profileId =      $request->getParameter('profileId');
      $profileType =    $request->getParameter('profileType');
      $platform =       $request->getParameter('platform');
      
//      MDMLogger::getInstance()->debug('', var_dump($devices), array());
      if (is_array($devices)) {
        foreach ($devices as $key => $device) {
          $deviceId = $deviceId . $key . ",";
        }
        $deviceId = substr($deviceId, 0, -1);
      } else {
        $deviceId = $devices;
      }

      // build url to call to service cmd/send for each action
      $configRep = new ConfigRepository();
      $commandUrl = $configRep->getCommandUrl();
      
      $deviceRep = new DeviceRepository();
      if ($profileType) {
          $platform = $deviceRep->getPlatformString($platform, $profileType);
      } else    {
          $platform = $deviceRep->getPlatformString($platform, $action);
      }
      
      $url = public_path($commandUrl['send'], true) . '?deviceId=' . $deviceId .
              '&userId=' . $userId . '&platform=' . $platform . '&profileId=' . $profileId;
      switch ($action) {
        case 'enroll_device':
          # code...
          break;
        case 'unenroll_device':
          $url .= '&command=Unenroll';
          $data_json = $this->_deviceAction($url);
          break;
        case 'allow_reenroll_device':
          $data_json = $this->_allowReEnroll($request);
          break;
        case 'lock_device':
          $url .= '&command=DeviceLock';
          $data_json = $this->_deviceAction($url);
          break;
        case 'wipe_device':
          if ($platform == self::IOS_APP_NAME)  {
            $url .= '&command=WipeData';
          } else    {
            $url .= '&command=EraseDevice';
          }
          $data_json = $this->_deviceAction($url);
          break;
        case 'reset_passcode':
          $url .= '&command=DeviceUnlock';
          if ($platform == self::IOS_NAME)  {
              $sendMail = $this->_sendUnlockiOS($deviceId, $userId);
              if ($sendMail === true)    {
                  $result['error']['status'] = 0;
                  $result['msg'] = $this->notificationMsg['N1001'];
              } else    {
                  $result['error']['msg'] = $sendMail;
              }
              return $this->renderText(json_encode($result));
          } else    {
              $data_json = $this->_deviceAction($url);
          }
          break;
        case 'get_log_information':
          $url .= '&command=GetLogInformation';
          $data_json = $this->_deviceAction($url);
          break;
        case "remove_profile":
          $url .= '&command=RemoveProfile';
          $data_json = $this->_deviceAction($url);
          break;
        case "install_profile":
          $url .= '&command=InstallProfile';
          $data_json = $this->_deviceAction($url);
          break;
        case "get_latest_location":
            if ($platform == self::WP_APP_NAME) {
                $deviceEvent = new DeviceEventRepository();
                if (!$deviceEvent->checkExistedCommand($deviceId)) {
                    $url .= '&command=GetLatestLocation';
                    $data_json = $this->_deviceAction($url);
                }
            }
          break;
        default:
          # code...
          break;
      }
    }
    return $this->renderText($data_json);
  }

  /**
   * Executes install profile
   * @param sfRequest $request A request object
   * Clump with device_action
   */

  /**
   * Executes uninstall profile
   * @param sfRequest $request A request object
   * Clump with device_action
   */

  /**
   * Executes show device detail
   *
   * @param sfRequest $request A request object
   */
  public function executeShow(sfWebRequest $request)
  {
    $this->_checkAuth();

    $attributeGroupRepository = new AttributeGroupRepository;
    $this->groups = $attributeGroupRepository->getAllAttributes();

    $this->device = $this->getRoute()->getObject();

    $deviceRepository = new DeviceRepository();
    $deviceRepository->setDeviveInformation($request->getParameter('id'));
    $this->deviceRepository = $deviceRepository;
    $this->deviceEvent = $deviceRepository->list_device_events($request, $this->device->id);

    $ownerRepository = new DeviceOwnerRepository;
    $ownerRepository->setOwner($this->device->getId());
    $this->owner = $ownerRepository;

    $locationRepository = new DeviceLocationRepository;
    $locationRepository->setLocation($this->device->getId());
    $this->location = $locationRepository;

    $deviceData = new DeviceProfileRepository();
    $this->deviceProfiles = $deviceData->getDeviceProfile($this->device->getId());

    $deviceApp = new DeviceApplicationRepository();
    $this->lastAppUpdated = $deviceApp->latestAppUpdated($request->getParameter('id'));
    $this->confirm = $this->confirmMsg;
    $this->error = $this->errorMsg;
  }

  /**
   * Executes get location belong to specific organization
   * @param sfRequest $request A request object
   */
  public function executeAjaxGetLocation($request)
  {
    $this->getResponse()->setContentType('application/json');
    $org = $request->getParameter("organization");
    $result = array();
    
    if ($request->isXmlHttpRequest()) {
        $locationRep = new LocationRepository();
        $locations = $locationRep->getLocationByOrg($org);

        if (is_object($locations))  {
            foreach ($locations as $location) {
                array_push($result, $location->getLocation());
            }
        }
    }

    $data_json = json_encode($result);
    return $this->renderText($data_json);
  }
  
  /**
   * Executes get tag info
   * @param sfRequest $request A request object
   * @return array $result contain all Organization, list Location,
   *               Device organization, Device location, Purchase, Warranty
   */
  public function executeAjaxGetTagInfo($request)
  {
    $result = array("error" => array("status" => 0, "msg" => ""), "data" => array(), "orgLocal" => array());
    $this->getResponse()->setContentType('application/json');
    $id = $request->getParameter('id');
    
    if ($request->isXmlHttpRequest()) {
        $deviceRep = new DeviceRepository();
        $device = $deviceRep->getDevice($id);

        $org = $device->getOrganization();
        $locationRep = new LocationRepository();
        $result['orgLocal'] = $locationRep->getAllOrgAndLocation($org);
        $result['data'] = $device->getData();
    }
    
    $data_json = json_encode($result);
    return $this->renderText($data_json);
  }
  

  /**
   * Executes show events
   * @param sfRequest $request A request object
   * 
   */
  public function executeEvents($request)
  {
    $this->_checkAuth();
    $configRep = new ConfigRepository();
    $this->eventStatus = $configRep->getEventStatus();
  }

  /**
   * find device events by ajax
   * @param  sfWebRequest $request  A request object
   * @return [json]                [for dataTable]
   */
  public function executeAjaxEventList($request)
  {
    $this->getResponse()->setContentType('application/json');

    $device = new DeviceRepository();
    $device_events = $device->list_device_events($request, null);
    $result = array();

    if ($request->isXmlHttpRequest()) {
      foreach ($device_events['result'] as $key => $de) {
        $result[] = array($de->getEventStatusName(),
            $de->getDeviceInventory()->getDeviceName(), $de->event_type,
            $de->getEventNameView(), $de->owner_name, $de->sender_email, $de->updated_at,
            $de->device_id,
            $this->generateUrl('device_detail', array("id" => $de->device_id), true));
      }
    }

    $data_json = json_encode(array(
        "draw" => intval($request->getParameter('draw')),
        'data' => $result,
        "recordsTotal" => $device_events['count'],
        "recordsFiltered" => $device_events['count'],
            )
    );
    return $this->renderText($data_json);
  }

  /**
   * update device tag
   * @param  sfWebRequest $request  A request object
   * @return [json]                [device Object if success]
   */
  public function executeAjaxEditTag($request)
  {
    $this->getResponse()->setContentType('application/json');
    $deviceInventoryRep = new Gcs\Repository\DeviceInventoryRepository;
    $result = array("error" => array("status" => 1, "msg" => ""), "data" => array());

    if ($request->isXmlHttpRequest()) {
        $id =               $request->getParameter('id');
        $organization =     $request->getParameter('organization');
        $location =         $request->getParameter('location');
        $purchase_date =    $request->getParameter('purchase_date');
        $warranty_end =     $request->getParameter('warranty_end');
        
        $data = compact('id', 'organization', 'location', 'purchase_date', 'warranty_end');
        $edit = $deviceInventoryRep->updateTag($data);
        if (is_object($edit)) {
          $result['error']['status'] = 0;
          $result["data"] = $edit->getData();
        } else {
          $result['error']['msg'] = $this->errorMsg[$edit];
        }
    }

    return $this->renderText(json_encode($result));
  }

  /**
   * Executes show Profile list
   * @param sfRequest $request A request object
   * 
   */
  public function executeProfile($request)
  {
    $this->_checkAuth();
    $profile = new Profile();
    $this->configTypes = $profile->configTypeDefault();
    $this->platformNames = $profile->platformNameDefault();
    
    $configRep = new ConfigRepository();
    $this->platform_config = $configRep->getProfileByPlatform();
    $this->iOSpasscodeSetting = $configRep->getiOSPasscodeSetting();
    $this->profilePasscodeTooltip = $this->notificationMsg['tooltip'];
    $this->locationWarning = $this->notificationMsg['N9001'];
    $this->confirm = $this->confirmMsg;
  }

  /**
   * List all profile
   * @param  sfWebRequest $request  A request object
   * @return [json]
   */
  public function executeAjaxProfileList($request)
  {
    $this->getResponse()->setContentType('application/json');

    $p = new \Gcs\Repository\ProfileRepository;
    $profiles = $p->get_profile($request);
    $result = array();

//        if ($request->isXmlHttpRequest()) {
    foreach ($profiles['result'] as $key => $profile) {
      new DeviceInventory;
      $result[] = array($profile->id, $profile->platform, $profile->profile_name, $profile->configuration_type, $profile->getConfigTypeName(), $profile->getPlatformName(), $profile->description, $profile->updated_at);
    }
//        }

    $data_json = json_encode(array(
        "draw" => intval($request->getParameter('draw')),
        'data' => $result,
        "recordsTotal" => $profiles['count'],
        "recordsFiltered" => $profiles['count'],
            )
    );
    return $this->renderText($data_json);
  }

  /**
   * reload locate device
   * @param wsWebRequest $request A request object
   * @return [json]
   */
  public function executeAjaxLocateDevice($request)
  {
    $this->getResponse()->setContentType('application/json');

    $device_id = $request->getParameter('id');
    $locationRepository = new DeviceLocationRepository;
    $locationRepository->setLocation($device_id);

    $result = array(
        'longitude' => $locationRepository->getValueByAttributeName('longitude'),
        'latitude' => $locationRepository->getValueByAttributeName('latitude'),
    );

    $data_json = json_encode(array(
        'data' => $result,
    ));
    return $this->renderText($data_json);
  }
  
  /**
   * get Warranty device
   * @param wsWebRequest $request A request object
   * @return [json]
   */
    public function executeAjaxGetWarranty($request) {
        $this->getResponse()->setContentType('application/json');
        $deviceId = $request->getParameter('id');
        $deviceInventoryRes = new DeviceInventoryRepository();
        $deviceInventory = $deviceInventoryRes ->getDeviceInventory($deviceId);
        
        $PurchaseDate = $deviceInventory->getPurchaseDate();
        $WarrantyEnd = $deviceInventory->getWarrantyEnd();
        
        $deviceRes = new DeviceRepository();
        
        $daysLeft = $deviceRes->getDayLeft($WarrantyEnd);
        $WarrantyStatus = $deviceRes->getWarrantyStatus($daysLeft);
        $percentWarranty = $deviceRes->getPercentWarranty($PurchaseDate, $WarrantyEnd);
        
        $result = array(
            'PurchaseDate'      => isset($PurchaseDate) ? $PurchaseDate : "-",
            'WarrantyEnd'       => isset($WarrantyEnd) ? $WarrantyEnd : "-",
            'daysLeft'          => $daysLeft,
            'percentWarranty'   => $percentWarranty,
            'WarrantyStatus'    => $WarrantyStatus
        );
        $data_json = json_encode(array(
            'data' => $result,
        ));
        return $this->renderText($data_json);
    }

    /**
     * Executes show Profile list
     * @param sfRequest $request A request object
     * 
     */
    public function executeLinkupManagement($request) {
        $this->_checkAuth();
        $menuRepository   = new Gcs\Repository\MenuRepository();
        $deviceLinkupRespository = new DeviceLinkupRepository();        
        $this->page_title = $menuRepository->getTitleMenu('device_linkup');
        $this->error = $this->errorMsg;
        $this->notification = $this->notificationMsg;
    }

    
    public function executeAjaxDeviceLinkupList($request){
        $this->getResponse()->setContentType('application/json');
        $deviceLinkupRespository = new DeviceLinkupRepository();
        
        $deviceLinkup = $deviceLinkupRespository ->listVersion($request);
        $result = array();
        if ($request->isXmlHttpRequest()) {
            foreach ($deviceLinkup['result'] as $key => $linkup) {
                $flatform = $deviceLinkupRespository ->getPlatformByKeyword($linkup -> getConfigKey());
                $result[] = array(
                    $linkup -> getConfigKey(),
                    $deviceLinkupRespository -> getPlatformNameById($flatform),
                    $linkup -> getConfigVal(),
                    $deviceLinkupRespository -> getPlatformExtensionById($flatform),
                );
            }
        }
        $data_json = json_encode(
            array(
                "draw"            => intval($request->getParameter('draw')),
                'data'            => $result,
                "recordsTotal"    => $deviceLinkup['count'],
                "recordsFiltered" => $deviceLinkup['count'],
            )
        );
        return $this->renderText($data_json);
    }

    public function executeUploadFileLinkup($request){
        $result = array(
            'error' => false,
            'msg'   => ''
        );
        $this->getResponse()->setContentType('application/json');
        $deviceLinkupRespository = new DeviceLinkupRepository();
        $dirApplication = $deviceLinkupRespository->getApplicationFolder();
        $defaultFileName = $deviceLinkupRespository->getApplicationFileName();
        $defaultFileName = $defaultFileName['default'];
        $keyword = $this->getRequestParameter("keyword");
        $file = null;
        if(isset($_FILES["software_upload"])){
            $file = $_FILES["software_upload"];
            $fileFullName = $file['name'];
            
            //Get file extension
            $extensions = explode(".",$fileFullName);
            $fileExtension = end($extensions); //Get the last extension
            
            //Get file name exclude ext
            $fileName = preg_replace("/.".$fileExtension."$/i", "", $fileFullName);
            
            $platform_id = $deviceLinkupRespository ->getPlatformByKeyword($keyword);
            $platformExtension = $deviceLinkupRespository ->getPlatformExtensionById($platform_id);

            if(!$deviceLinkupRespository ->invalidStructure($fileName)){
                if($fileExtension == $platformExtension){
                    $rootDir = $deviceLinkupRespository ->getRootDir();
                    $folderUpload = $rootDir . $dirApplication;
                    $fileUpload = $folderUpload . "/" . $defaultFileName . "." . $fileExtension;
                    $fileTemp = $file['tmp_name'];
                    if(is_dir($folderUpload)){
                        if (!move_uploaded_file($fileTemp, $fileUpload)){
//                            $msgError = $this->errorMsg['E6001'] . "\"" . $fileFullName. "\"";
                            if (!file_exists($folderUpload)) {
                                $msgError = $this->errorMsg['E6001'];
                            } elseif (!is_writable($folderUpload)) {
                                $msgError = $this->errorMsg['E6002'];
                            } elseif (!is_writable($fileUpload)) {
                                $msgError = $this->errorMsg['E6003'];
                            }
                            $msgError = str_replace('${filename}', $fileFullName, $msgError);
                            $result = array(
                                'error' => true,
                                'msg'   => $msgError
                            );
                        }else{
                            chmod($fileUpload, 0666);//Make it universally writable.
                            $version = $deviceLinkupRespository ->getSoftwareVersionByFileName($fileName);
                            $status =  $deviceLinkupRespository -> updateSoftwareVersion($keyword, $version);
                            $result = array(
                                'error' => false,
                                'msg'   => ""
                            );
                        }
                    }else{
                        $result = array(
                            'error' => true,
                            'msg'   => $this->errorMsg['E6011']
                        );
                    }
                }else{
                    $msg = $this->errorMsg['E6012'];
                    $msg = str_replace('${extension}', "<b>" . $platformExtension . "</b>", $msg);
                    $result = array(
                        'error' => true,
                        'msg'   => $msg
                    );
                }
            }else{
                $msg = $this->errorMsg['E6014'];
                $msg = str_replace('filename_v1.0.0.xxxx', "<b>filename_v1.0.0.xxxx</b>", $msg);
                $result = array(
                    'error' => true,
                    'msg'   => $msg
                );
            }
            
        }else{
            $result = array(
                'error' => true,
                'msg'   => $this->errorMsg['E6016']
            );
        }
        
        $data_json = json_encode($result);
        return $this->renderText($data_json);
    }


    /**
   * find list device by ajax
   * @param  sfWebRequest $request  A request object
   * @return [type]                [description]
   */
  private function _allowReEnroll($request)
  {
    if ($request->isXmlHttpRequest()) {
      $deviceRepository = new DeviceRepository();
      $result = $deviceRepository->re_enroll($request->getParameter('id'));
      if ($result['error']['status'] == 1)    {
          $result['error']['msg'] = $this->errorMsg[$result['error']['msg']];
      }
      if ($result['error']['status'] == 0)    {
          $result['msg'] = $this->notificationMsg[$result['msg']];
      }
    }
    return json_encode($result);
  }

  /**
   * [_lockDevice description]
   * @param  [type] $request [description]
   * @return [type]          [description]
   */
  private function _deviceAction($url)
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20); //timeout in seconds
    $commandUrl = \sfConfig::get("app_command_url_data");
    curl_setopt($ch, CURLOPT_PROXY, $commandUrl['proxy']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    $curlResult = curl_exec($ch);
    $result = json_decode($curlResult, TRUE);
    
    MDMLogger::getInstance()->debug('', __FILE__ . "::::" . __LINE__ . " curl error: " . curl_error($ch), array());
    if (curl_error($ch) != "") {
        $result['error']['status'] = 1;
        $result['error']['msg'] = $this->errorMsg['E4001'];
    }
    elseif (isset($result['error']) && isset($result['error']['status'])) {
        if ($result['error']['status'] == 0)   {
            $result['msg'] = $this->notificationMsg[$result['msg']];
        }
        elseif ($result['error']['status'] == 1)  {
            $result['error']['msg'] = $this->errorMsg[$result['error']['msg']];
        }
        else {
            $result['error']['status'] = 1;
            $result['error']['msg'] = $this->errorMsg['E7001'];
        }
    }
    else {
        $result['error']['status'] = 1;
        $result['error']['msg'] = $this->errorMsg['E7001'];
    }
    curl_close($ch);
    MDMLogger::getInstance()->debug('', $url, array());
    return json_encode($result);
  }
  
    /**
     * Send email to unlock iOS device
     * @param $deviceId
     * @param $link userId who send command
     * @return TRUE | error
     */
    private function _sendUnlockiOS($deviceId, $link) {
        $configRep          = new ConfigRepository();
        $deviceRep          = new DeviceInventoryRepository();
        $device             = $deviceRep->getDeviceInventory($deviceId);
        if (!$device)   {
            return $this->errorMsg['E3001'];
        }
        
        // Get information
        $passcode = $deviceRep->saveTempPassiOS($deviceId);
        $ownerFullName = $device->getOwnerName();
        $userId        = $device->getUserId();
        if(is_null($userId)){
            return $this->errorMsg['E3002'];
        }
        $userInfo      = UserInfoTable::getInstance()->findOneById($userId);
        if ($userInfo) {
            $ownerEmail    = $userInfo->getEmail();
            $ownerFullName = $userInfo->getFullName();
        }
        $deviceName   = $device->getDeviceName();
        $link = public_path('command', true) . '?deviceId=' . $deviceId . 
              '&userId=' . $link . "&otp=" . $passcode;
    // Get template from database
        $from               = $configRep->getMailFrom();
        sfProjectConfiguration::getActive()->loadHelpers('Partial');
        $templateRep        = new TemplateRepository();
        $template           = $templateRep->getMail(self::UNLOCK_IOS);
        $html               = $template[0]->getContent();
        $subject            = $template[0]->getSubject();

        $html = str_replace('${user}', $ownerFullName, $html);
        $html = str_replace('${deviceName}', $deviceName, $html);
        $html = str_replace('${link}', $link, $html);
        
        try {
            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom(array($from['email'] => $from['name']))
                ->setTo($ownerEmail)
                ->setBody($html, 'text/html');
            $this->getMailer()->send($message);
        } catch (Exception $e) {
            return $this->errorMsg['E3002'];
        }
        return true;
    }
  
    /**
     * Execute send unlock iOS device
     * @param sfWebRequest $request
     * @return error status & message
     */
    public function executeCommand(sfWebRequest $request)   {
        $this->setLayout('master');
        $this->status = 1;
        $this->result = $this->notificationMsg['N1002'];
        $deviceId = $request->getParameter('deviceId');
        $userId   = $request->getParameter('userId');
        $otp      = $request->getParameter('otp');
        
        // Wrong command
        if (!$deviceId || !$userId || !$otp)   {
           return;
        }
        
        // otp not match
        $deviceRep = new DeviceInventoryRepository();
        $device = $deviceRep->getDeviceInventory($deviceId);
        if (!$device || ($device && $device->getPasscode() != $otp)) {
            return;
        }
        
        // Clear otp and send command
        $configRep = new ConfigRepository();
        $commandUrl = $configRep->getCommandUrl();
        $deviceRep->clearTempPassiOS($deviceId);
        $url = public_path($commandUrl['send'], true) . '?deviceId=' . $deviceId .
                       '&userId=' . $userId . '&platform=ios' . '&command=DeviceUnlock';
        $data_json = json_decode($this->_deviceAction($url), true);
        $this->status = $data_json['error']['status'];
        if ($this->status)  {
            $this->result = $this->errorMsg['E4005'];
        } else  {
            $this->result = $data_json['msg'];
        }
    }
}
